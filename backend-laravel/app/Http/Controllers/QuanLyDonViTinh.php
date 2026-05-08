<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuanLyDonViTinh extends Controller
{
    public function index()
    {
        try {
            $don_vi_tinh = DB::table('don_vi_tinh')
                ->select('ma_don_vi_tinh', 'ten_don_vi_tinh')
                ->where('thoi_gian_xoa', '<', '2000-01-01')
                ->orderBy('ma_don_vi_tinh', 'asc')
                ->get();
            return response()->json(["success" => true, "data" => $don_vi_tinh]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        $ten_don_vi_tinh = $request->input('ten_don_vi_tinh');
        $ma_don_vi_tinh = $request->input('ma_don_vi_tinh');

        if (!$ten_don_vi_tinh) {
            return response()->json(['success' => false, 'message' => 'Vui lòng nhập tên đơn vị tính.']);
        }

        try {
            $dataToSave = ['ten_don_vi_tinh' => $ten_don_vi_tinh];

            if ($ma_don_vi_tinh) {
                DB::table('don_vi_tinh')->where('ma_don_vi_tinh', $ma_don_vi_tinh)->update($dataToSave);
                $message = "Đã cập nhật thông tin đơn vị tính thành công!";
            } else {
                DB::table('don_vi_tinh')->insert($dataToSave);
                $message = "Đã tạo đơn vị tính mới thành công!";
            }

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        $ma_don_vi_tinh = $request->input('ma_don_vi_tinh');
        
        if (empty($ma_don_vi_tinh)) {
            return response()->json(['success' => false, 'message' => 'Thiếu mã đơn vị tính.']);
        }

        try {
            DB::table('don_vi_tinh')
                ->where('ma_don_vi_tinh', $ma_don_vi_tinh)
                ->update(['thoi_gian_xoa' => now()]);
            return response()->json(['success' => true, 'message' => 'Đã xóa đơn vị tính thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
}
