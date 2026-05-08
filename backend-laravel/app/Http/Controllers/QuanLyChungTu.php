<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class QuanLyChungTu extends Controller 
{
    public function index()
    {
        try {
            // 1. Lấy danh sách chứng từ (Join qua bảng lo_hang và booking)
            $chungTu = DB::table('chung_tu_so_hoa')
                ->leftJoin('lo_hang', 'chung_tu_so_hoa.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking') // Kéo bảng booking vào
                ->select('chung_tu_so_hoa.*', 'booking.so_booking', 'lo_hang.ten_lo_hang')
                ->orderBy('chung_tu_so_hoa.ma_chung_tu', 'desc')
                ->get();
            
            // 2. Lấy danh sách Lô hàng (Cũng phải Join bảng booking để lấy số booking hiển thị ở Combobox)
            $loHang = DB::table('lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->where('lo_hang.thoi_gian_xoa', '<=', '2000-01-01 00:00:00')
                ->select('lo_hang.ma_lo_hang', 'lo_hang.ten_lo_hang', 'booking.so_booking')
                ->get();

            return response()->json(["success" => true, "data" => $chungTu, "lo_hang" => $loHang]);
        } catch (\Exception $e) {
            // Trả về lỗi để Frontend biết (nếu có)
            return response()->json(["success" => false, "message" => "Lỗi DB: " . $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'hinh_anh' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:10240', // Tối đa 10MB
                'loai_chung_tu' => 'required',
                'ma_lo_hang' => 'required'
            ]);

            $ma_chung_tu = $request->input('ma_chung_tu');
            $data = [
                'loai_chung_tu' => $request->input('loai_chung_tu'),
                'ma_lo_hang' => $request->input('ma_lo_hang'),
            ];

            if ($request->hasFile('hinh_anh')) {
                if ($ma_chung_tu) {
                    $oldDoc = DB::table('chung_tu_so_hoa')->where('ma_chung_tu', $ma_chung_tu)->first();
                    if ($oldDoc && $oldDoc->hinh_anh && file_exists(public_path($oldDoc->hinh_anh))) {
                        unlink(public_path($oldDoc->hinh_anh));
                    }
                }

                $file = $request->file('hinh_anh');
                $filename = time() . '_' . $file->getClientOriginalName();
                
                $path = public_path('uploads/chungtu');
                if(!File::isDirectory($path)){
                    File::makeDirectory($path, 0777, true, true);
                }

                $file->move($path, $filename);
                $data['hinh_anh'] = 'uploads/chungtu/' . $filename; 
            }

            if ($ma_chung_tu) {
                DB::table('chung_tu_so_hoa')->where('ma_chung_tu', $ma_chung_tu)->update($data);
                $msg = "Đã cập nhật chứng từ!";
            } else {
                DB::table('chung_tu_so_hoa')->insert($data);
                $msg = "Đã tải lên chứng từ mới!";
            }

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $ma_chung_tu = $request->input('ma_chung_tu');
            $oldDoc = DB::table('chung_tu_so_hoa')->where('ma_chung_tu', $ma_chung_tu)->first();
            
            if ($oldDoc && $oldDoc->hinh_anh && file_exists(public_path($oldDoc->hinh_anh))) {
                unlink(public_path($oldDoc->hinh_anh));
            }

            DB::table('chung_tu_so_hoa')->where('ma_chung_tu', $ma_chung_tu)->delete();
            return response()->json(['success' => true, 'message' => 'Đã xóa chứng từ thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
}