<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuanLyChiPhi extends Controller
{
    public function index()
    {
        try {
            // Đã JOIN thêm bảng tai_khoan để lấy tên người sửa
            $danhSachChiPhi = DB::table('chi_phi')
                ->leftJoin('lo_hang', 'chi_phi.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->leftJoin('khach_hang', 'lo_hang.ma_khach_hang', '=', 'khach_hang.ma_khach_hang')
                ->leftJoin('tai_khoan', 'chi_phi.nguoi_sua_cuoi', '=', 'tai_khoan.ma_tai_khoan') // Join tài khoản
                ->select(
                    'chi_phi.*',
                    'lo_hang.ten_lo_hang', 
                    'lo_hang.dieu_kien_thuong_mai',
                    'lo_hang.trang_thai_lo_hang',
                    'booking.so_booking',
                    'khach_hang.ten_khach_hang',
                    'tai_khoan.ho_ten as nguoi_cap_nhat' // Lấy tên người sửa ra
                )
                ->where('chi_phi.thoi_gian_xoa', '<=', '2000-01-01 00:00:00')
                ->orderBy('chi_phi.ma_chi_phi', 'desc')
                ->get();

            $loHang = DB::table('lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->where('lo_hang.thoi_gian_xoa', '<=', '2000-01-01 00:00:00')
                ->select('lo_hang.ma_lo_hang', 'lo_hang.ten_lo_hang', 'booking.so_booking')
                ->get();

            $tongThu = 0; $tongChi = 0; $tonDong = 0;
            foreach ($danhSachChiPhi as $cp) {
                if ($cp->loai_giao_dich === 'THU') $tongThu += $cp->tong_tien;
                elseif ($cp->loai_giao_dich === 'CHI') $tongChi += $cp->tong_tien;

                if (in_array($cp->trang_thai_thanh_toan, ['Chưa thanh toán', 'Thanh toán một phần'])) {
                    $tonDong += $cp->tong_tien;
                }
            }

            return response()->json([
                "success" => true, "data" => $danhSachChiPhi, "lo_hang" => $loHang,
                "thong_ke" => ["tong_thu" => $tongThu, "tong_chi" => $tongChi, "ton_dong" => $tonDong]
            ]);
        } catch (\Exception $e) { return response()->json(["success" => false, "message" => "Lỗi DB: " . $e->getMessage()]); }
    }

   public function store(Request $request)
    {
        try {
            $ma_chi_phi = $request->input('ma_chi_phi');
            $data = [
                'ten_chi_phi' => $request->input('ten_chi_phi'),
                'tong_tien' => $request->input('tong_tien'),
                'trang_thai_thanh_toan' => $request->input('trang_thai_thanh_toan'),
                'ngay_thanh_toan' => $request->input('ngay_thanh_toan'),
                'loai_giao_dich' => $request->input('loai_giao_dich'),
                'ma_lo_hang' => $request->input('ma_lo_hang'),
                'nguoi_sua_cuoi' => $request->input('nguoi_sua_cuoi') // Ghi nhận ID người sửa
            ];

            if ($ma_chi_phi) {
                DB::table('chi_phi')->where('ma_chi_phi', $ma_chi_phi)->update($data);
                $msg = "Đã cập nhật chi phí thành công!";
            } else {
                DB::table('chi_phi')->insert($data);
                $msg = "Đã thêm mới chi phí thành công!";
            }
            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) { return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]); }
    }
    public function destroy(Request $request)
    {
        try {
            $id = $request->input('ma_chi_phi');
            
            // Xóa cứng (hoặc bạn có thể update thoi_gian_xoa = now() nếu muốn xóa mềm)
            DB::table('chi_phi')->where('ma_chi_phi', $id)->delete();
            
            return response()->json(['success' => true, 'message' => 'Đã xóa chi phí thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    // Hàm cập nhật nhanh trạng thái thanh toán
    public function updateStatus(Request $request)
    {
        try {
            $id = $request->input('ma_chi_phi');
            DB::table('chi_phi')->where('ma_chi_phi', $id)->update([
                'trang_thai_thanh_toan' => $request->input('trang_thai_thanh_toan'),
                'ngay_thanh_toan' => $request->input('ngay_thanh_toan'),
                'nguoi_sua_cuoi' => $request->input('nguoi_sua_cuoi') // Ghi nhận ID người vừa ấn nút Thu tiền
            ]);
            return response()->json(['success' => true, 'message' => 'Đã cập nhật trạng thái thanh toán!']);
        } catch (\Exception $e) { return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]); }
    }
}