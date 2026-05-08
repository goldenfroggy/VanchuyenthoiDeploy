<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuanLyKhoCang extends Controller
{
    public function index()
    {
        try {
            $cang_bien = DB::table('cang_bien')
                ->select('ma_cang', 'ten_cang', 'dia_chi', 'ghi_chu')
                ->where('thoi_gian_xoa', '<', '2000-01-01')
                ->orderBy('ma_cang', 'asc')
                ->get();
            return response()->json(["success" => true, "data" => $cang_bien]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        $ten_cang = $request->input('ten_cang');
        $ma_cang = $request->input('ma_cang');

        if (!$ten_cang) {
            return response()->json(['success' => false, 'message' => 'Vui lòng nhập tên cảng.']);
        }

        try {
            $dataToSave = [
                'ten_cang' => $ten_cang,
                'dia_chi' => $request->input('dia_chi'),
                'ghi_chu' => $request->input('ghi_chu')
            ];

            if ($ma_cang) {
                DB::table('cang_bien')->where('ma_cang', $ma_cang)->update($dataToSave);
                $message = "Đã cập nhật thông tin cảng thành công!";
            } else {
                DB::table('cang_bien')->insert($dataToSave);
                $message = "Đã tạo cảng mới thành công!";
            }

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        $ma_cang = $request->input('ma_cang');
    
        if (empty($ma_cang)) {
            return response()->json(['success' => false, 'message' => 'Thiếu mã cảng.']);
        }

        try {
            DB::table('cang_bien')->where('ma_cang', $ma_cang)->update(['thoi_gian_xoa' => now()]);
            return response()->json(['success' => true, 'message' => 'Đã xóa cảng thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
}