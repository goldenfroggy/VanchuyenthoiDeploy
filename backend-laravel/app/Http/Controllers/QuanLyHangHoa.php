<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuanLyHangHoa extends Controller
{
    public function index()
    {
        try {
            $hang_hoa = DB::table('hang_hoa')
                ->select('ma_hang_hoa', 'ten_hang_hoa', 'hs_code')
                ->where('thoi_gian_xoa', '<', '2000-01-01')
                ->orderBy('ma_hang_hoa', 'asc')
                ->get();
            return response()->json(["success" => true, "data" => $hang_hoa]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        $ten_hang_hoa = $request->input('ten_hang_hoa');
        $ma_hang_hoa = $request->input('ma_hang_hoa');

        if (!$ten_hang_hoa) {
            return response()->json(['success' => false, 'message' => 'Vui lòng nhập tên hàng hóa.']);
        }

        try {
            $dataToSave = [
                'ten_hang_hoa' => $ten_hang_hoa,
                'hs_code' => $request->input('hs_code')
            ];

            if ($ma_hang_hoa) {
                DB::table('hang_hoa')->where('ma_hang_hoa', $ma_hang_hoa)->update($dataToSave);
                $message = "Đã cập nhật thông tin hàng hóa thành công!";
            } else {
                DB::table('hang_hoa')->insert($dataToSave);
                $message = "Đã tạo hàng hóa mới thành công!";
            }

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        $ma_hang_hoa = $request->input('ma_hang_hoa');
        
        if (empty($ma_hang_hoa)) {
            return response()->json(['success' => false, 'message' => 'Thiếu mã hàng hóa.']);
        }

        try {
            DB::table('hang_hoa')
                ->where('ma_hang_hoa', $ma_hang_hoa)
                ->update(['thoi_gian_xoa' => now()]);
            return response()->json(['success' => true, 'message' => 'Đã xóa hàng hóa thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
}
