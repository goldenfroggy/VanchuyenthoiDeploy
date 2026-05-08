<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuanLyHangVanTai extends Controller
{
    public function index()
    {
        try {
            $hang_van_tai = DB::table('hang_van_tai')
                ->select('ma_hang_van_tai', 'ten_hang_van_tai', 'tuyen_thuong_xuyen', 'cac_loai_xe', 'ghi_chu')
                ->where('thoi_gian_xoa', '<', '2000-01-01')
                ->orderBy('ma_hang_van_tai', 'asc')
                ->get();
            return response()->json(["success" => true, "data" => $hang_van_tai]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        $ten_hang_van_tai = $request->input('ten_hang_van_tai');
        $ma_hang_van_tai = $request->input('ma_hang_van_tai');

        if (!$ten_hang_van_tai) {
            return response()->json(['success' => false, 'message' => 'Vui lòng nhập tên hãng vận tải.']);
        }

        try {
            $dataToSave = [
                'ten_hang_van_tai' => $ten_hang_van_tai,
                'tuyen_thuong_xuyen' => $request->input('tuyen_thuong_xuyen'),
                'cac_loai_xe' => $request->input('cac_loai_xe'),
                'ghi_chu' => $request->input('ghi_chu')
            ];

            if ($ma_hang_van_tai) {
                DB::table('hang_van_tai')->where('ma_hang_van_tai', $ma_hang_van_tai)->update($dataToSave);
                $message = "Đã cập nhật thông tin hãng vận tải thành công!";
            } else {
                DB::table('hang_van_tai')->insert($dataToSave);
                $message = "Đã tạo hãng vận tải mới thành công!";
            }

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        $ma_hang_van_tai = $request->input('ma_hang_van_tai');
        
        if (empty($ma_hang_van_tai)) {
            return response()->json(['success' => false, 'message' => 'Thiếu mã hãng vận tải.']);
        }

        try {
            DB::table('hang_van_tai')
                ->where('ma_hang_van_tai', $ma_hang_van_tai)
                ->update(['thoi_gian_xoa' => now()]);
            return response()->json(['success' => true, 'message' => 'Đã xóa hãng vận tải thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
}
