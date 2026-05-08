<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuanLyKhachHang extends Controller
{
    public function index()
    {
        try {
            $khach_hang = DB::table('khach_hang')
                ->leftJoin('tai_khoan', 'khach_hang.nguoi_sua_cuoi', '=', 'tai_khoan.ma_tai_khoan')
                ->select('khach_hang.*', 'tai_khoan.ho_ten as nguoi_sua_doi')
                ->where('khach_hang.thoi_gian_xoa', '<', '2000-01-01')
                ->orderBy('khach_hang.ma_khach_hang', 'asc')
                ->get();
            return response()->json(["success" => true, "data" => $khach_hang]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        $ten_khach_hang = $request->input('ten_khach_hang');
        $ma_khach_hang = $request->input('ma_khach_hang');
        if (!$ten_khach_hang) return response()->json(['success' => false, 'message' => 'Vui lòng nhập tên khách hàng.']);

        try {
            $dataToSave = [
                'ten_khach_hang' => $ten_khach_hang,
                'dia_chi' => $request->input('dia_chi'),
                'so_dien_thoai' => $request->input('so_dien_thoai'),
                'so_fax' => $request->input('so_fax'),
                'ghi_chu' => $request->input('ghi_chu'),
                'nguoi_sua_cuoi' => $request->input('nguoi_sua_cuoi')
            ];

            if ($ma_khach_hang) {
                DB::table('khach_hang')->where('ma_khach_hang', $ma_khach_hang)->update($dataToSave);
                $message = "Cập nhật thành công!";
            } else {
                DB::table('khach_hang')->insert($dataToSave);
                $message = "Tạo mới thành công!";
            }
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        $ma_khach_hang = $request->input('ma_khach_hang');
        if (empty($ma_khach_hang)) return response()->json(['success' => false, 'message' => 'Thiếu mã.']);
        try {
            DB::table('khach_hang')->where('ma_khach_hang', $ma_khach_hang)->update(['thoi_gian_xoa' => now()]);
            return response()->json(['success' => true, 'message' => 'Xóa thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
}