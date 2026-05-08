<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuanLyTrangThaiThanhToan extends Controller
{
    // 1. Lấy danh sách chi phí để hiển thị lên bảng
    public function index()
    {
        try {
            $danhSach = DB::table('chi_phi')
                ->leftJoin('lo_hang', 'chi_phi.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->leftJoin('khach_hang', 'lo_hang.ma_khach_hang', '=', 'khach_hang.ma_khach_hang')
                ->leftJoin('tai_khoan', 'chi_phi.nguoi_sua_cuoi', '=', 'tai_khoan.ma_tai_khoan')
                ->select(
                    'chi_phi.*',
                    'lo_hang.ten_lo_hang', 
                    'lo_hang.dieu_kien_thuong_mai',
                    'lo_hang.trang_thai_lo_hang',
                    'booking.so_booking',
                    'khach_hang.ten_khach_hang',
                    'tai_khoan.ho_ten as nguoi_cap_nhat'
                )
                ->where('chi_phi.thoi_gian_xoa', '<=', '2000-01-01 00:00:00')
                ->orderBy('chi_phi.ma_chi_phi', 'desc')
                ->get();

            return response()->json([
                "success" => true, 
                "data" => $danhSach
            ]);
        } catch (\Exception $e) { 
            return response()->json(["success" => false, "message" => "Lỗi DB: " . $e->getMessage()]); 
        }
    }

    // 2. Cập nhật trạng thái thanh toán và lưu vết người cập nhật
    public function updateStatus(Request $request)
    {
        try {
            $id = $request->input('ma_chi_phi');
            DB::table('chi_phi')->where('ma_chi_phi', $id)->update([
                'trang_thai_thanh_toan' => $request->input('trang_thai_thanh_toan'),
                'ngay_thanh_toan' => $request->input('ngay_thanh_toan'),
                'nguoi_sua_cuoi' => $request->input('nguoi_sua_cuoi')
            ]);
            
            return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công!']);
        } catch (\Exception $e) { 
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]); 
        }
    }
}