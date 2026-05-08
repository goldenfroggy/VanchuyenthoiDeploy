<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuanLyHangTau extends Controller
{
    public function index()
    {
        try {
            $hang_tau = DB::table('hang_tau')
                ->leftJoin('tai_khoan', 'hang_tau.nguoi_sua_cuoi', '=', 'tai_khoan.ma_tai_khoan')
                ->select('hang_tau.ma_hang_tau', 'hang_tau.ten_hang_tau', 'hang_tau.dia_chi', 'hang_tau.so_dien_thoai', 'hang_tau.so_fax', 'hang_tau.ghi_chu', 'hang_tau.nguoi_sua_cuoi', 'tai_khoan.ho_ten as nguoi_sua_doi')
                ->where('hang_tau.thoi_gian_xoa', '<', '2000-01-01')
                ->orderBy('hang_tau.ma_hang_tau', 'asc')
                ->get();
            return response()->json(["success" => true, "data" => $hang_tau]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        $ten_hang_tau = $request->input('ten_hang_tau');
        $ma_hang_tau = $request->input('ma_hang_tau');

        if (!$ten_hang_tau) {
            return response()->json(['success' => false, 'message' => 'Vui lòng nhập tên hãng tàu.']);
        }

        try {
            $dataToSave = [
                'ten_hang_tau' => $ten_hang_tau,
                'dia_chi' => $request->input('dia_chi'),
                'so_dien_thoai' => $request->input('so_dien_thoai'),
                'so_fax' => $request->input('so_fax'),
                'ghi_chu' => $request->input('ghi_chu'),
                'nguoi_sua_cuoi' => $request->input('nguoi_sua_cuoi')
            ];

            if ($ma_hang_tau) {
                DB::table('hang_tau')->where('ma_hang_tau', $ma_hang_tau)->update($dataToSave);
                $message = "Đã cập nhật thông tin hãng tàu thành công!";
            } else {
                DB::table('hang_tau')->insert($dataToSave);
                $message = "Đã tạo hãng tàu mới thành công!";
            }

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        $ma_hang_tau = $request->input('ma_hang_tau');
    
        if (empty($ma_hang_tau)) {
            return response()->json(['success' => false, 'message' => 'Thiếu mã hãng tàu.']);
        }

        try {
            DB::table('hang_tau')->where('ma_hang_tau', $ma_hang_tau)->update(['thoi_gian_xoa' => now()]);
            return response()->json(['success' => true, 'message' => 'Đã xóa hãng tàu thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
}