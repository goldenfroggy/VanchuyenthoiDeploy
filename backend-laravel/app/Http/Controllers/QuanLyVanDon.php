<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class QuanLyVanDon extends Controller
{
    public function index()
    {
        try {
            $van_don = DB::table('van_don')
                ->leftJoin('khach_hang as nguoi_gui', 'van_don.ma_nguoi_gui_hang', '=', 'nguoi_gui.ma_khach_hang')
                ->leftJoin('khach_hang as nguoi_nhan', 'van_don.ma_nguoi_nhan_hang', '=', 'nguoi_nhan.ma_khach_hang')
                ->leftJoin('khach_hang as ben_thong_bao', 'van_don.ma_ben_duoc_thong_bao', '=', 'ben_thong_bao.ma_khach_hang')
                ->leftJoin('cang_bien as cang_di', 'van_don.ma_cang_di', '=', 'cang_di.ma_cang')
                ->leftJoin('cang_bien as cang_den', 'van_don.ma_cang_den', '=', 'cang_den.ma_cang')
                ->leftJoin('lo_hang', 'van_don.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('tai_khoan', 'van_don.nguoi_sua_cuoi', '=', 'tai_khoan.ma_tai_khoan')
                ->select(
                    'van_don.*',
                    'nguoi_gui.ten_khach_hang as ten_nguoi_gui_hang',
                    'nguoi_nhan.ten_khach_hang as ten_nguoi_nhan_hang',
                    'ben_thong_bao.ten_khach_hang as ten_ben_duoc_thong_bao',
                    'cang_di.ten_cang as ten_cang_di',
                    'cang_den.ten_cang as ten_cang_den',
                    'lo_hang.ten_lo_hang',
                    'tai_khoan.ho_ten as nguoi_sua_doi' // Lấy tên người sửa cuối cùng từ bảng tai_khoan
                )
                ->where('van_don.thoi_gian_xoa', '<', '2000-01-01')
                ->orderBy('van_don.ma_van_don', 'desc')
                ->get();
            return response()->json(["success" => true, "data" => $van_don]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
        }
    }

    public function getReferences()
    {
        try {
            $khachHang = DB::table('khach_hang')
                ->where('thoi_gian_xoa', '<', '2000-01-01')
                ->get(['ma_khach_hang', 'ten_khach_hang']);

            $cangBien = DB::table('cang_bien')
                ->where('thoi_gian_xoa', '<', '2000-01-01')
                ->get(['ma_cang', 'ten_cang']);

            $loHang = DB::table('lo_hang')
                ->where('thoi_gian_xoa', '<', '2000-01-01')
                ->get(['ma_lo_hang', 'ten_lo_hang']);

            return response()->json([
                "success" => true,
                "khach_hang" => $khachHang,
                "cang_bien" => $cangBien,
                "lo_hang" => $loHang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Lỗi tải danh mục tham chiếu: " . $e->getMessage()
            ]);
        }
    }

    public function exportPdf($id)
    {
        try {
            $vanDon = DB::table('van_don')
                ->leftJoin('khach_hang as nguoi_gui', 'van_don.ma_nguoi_gui_hang', '=', 'nguoi_gui.ma_khach_hang')
                ->leftJoin('khach_hang as nguoi_nhan', 'van_don.ma_nguoi_nhan_hang', '=', 'nguoi_nhan.ma_khach_hang')
                ->leftJoin('khach_hang as ben_thong_bao', 'van_don.ma_ben_duoc_thong_bao', '=', 'ben_thong_bao.ma_khach_hang')
                ->leftJoin('cang_bien as cang_di', 'van_don.ma_cang_di', '=', 'cang_di.ma_cang')
                ->leftJoin('cang_bien as cang_den', 'van_don.ma_cang_den', '=', 'cang_den.ma_cang')
                ->leftJoin('lo_hang', 'van_don.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->select(
                    'van_don.*',
                    'nguoi_gui.ten_khach_hang as ten_nguoi_gui_hang',
                    'nguoi_gui.dia_chi as dia_chi_nguoi_gui',
                    'nguoi_nhan.ten_khach_hang as ten_nguoi_nhan_hang',
                    'nguoi_nhan.dia_chi as dia_chi_nguoi_nhan',
                    'ben_thong_bao.ten_khach_hang as ten_ben_duoc_thong_bao',
                    'ben_thong_bao.dia_chi as dia_chi_ben_thong_bao',
                    'cang_di.ten_cang as ten_cang_di',
                    'cang_den.ten_cang as ten_cang_den',
                    'lo_hang.ten_lo_hang',
                    'booking.ten_con_tau as ten_tau'
                )
                ->where('van_don.ma_van_don', $id)
                ->where('van_don.thoi_gian_xoa', '<', '2000-01-01') // Only active records
                ->first();

            if (!$vanDon) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy vận đơn.'], 404);
            }

            // Định dạng ngày phát hành cho PDF 
            $vanDon->ngay_phat_hanh_formatted = $vanDon->ngay_phat_hanh ? Carbon::parse($vanDon->ngay_phat_hanh)->format('d/m/Y') : 'N/A';

            // Lấy chi tiết hàng hóa từ lô hàng
            $chiTietHangHoa = DB::table('chi_tiet_lo_hang')
                ->leftJoin('hang_hoa', 'chi_tiet_lo_hang.ma_hang_hoa', '=', 'hang_hoa.ma_hang_hoa')
                ->leftJoin('don_vi_tinh', 'chi_tiet_lo_hang.ma_don_vi_tinh', '=', 'don_vi_tinh.ma_don_vi_tinh')
                ->where('chi_tiet_lo_hang.ma_lo_hang', $vanDon->ma_lo_hang)
                ->select(
                    'chi_tiet_lo_hang.*',
                    'hang_hoa.ten_hang_hoa',
                    'hang_hoa.hs_code',
                    'don_vi_tinh.ten_don_vi_tinh'
                )
                ->get();

            // Load view PDF với dữ liệu vận đơn và chi tiết hàng hóa
            $pdf = Pdf::loadView('pdfs.van_don', compact('vanDon', 'chiTietHangHoa'));

            // Trả về file PDF để tải xuống
            return $pdf->download('VanDon_' . $vanDon->so_van_don . '.pdf');

        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi khi tạo PDF: " . $e->getMessage()], 500);
        }
    }

    public function save(Request $request)
    {
        $ma_van_don = $request->input('ma_van_don');
        $so_van_don = $request->input('so_van_don');
        $ma_lo_hang = $request->input('ma_lo_hang');
        $nguoi_sua_cuoi = $request->input('nguoi_sua_cuoi'); 

        if (!$so_van_don) {
            return response()->json(['success' => false, 'message' => 'Vui lòng nhập số vận đơn.']);
        }
        if (!$ma_lo_hang) {
            return response()->json(['success' => false, 'message' => 'Vui lòng chọn lô hàng.']);
        }

        try {
            // Kiểm tra trùng số vận đơn (chỉ với các bản ghi chưa bị xóa)
            $query = DB::table('van_don')
                        ->where('so_van_don', $so_van_don)
                        ->where('thoi_gian_xoa', '<', '2000-01-01'); // Only check active records

            if ($ma_van_don) { // Nếu đang cập nhật, loại trừ bản ghi hiện tại khỏi kiểm tra
                $query->where('ma_van_don', '!=', $ma_van_don);
            }

            if ($query->exists()) {
                return response()->json(['success' => false, 'message' => 'Số vận đơn đã tồn tại. Vui lòng nhập số khác.']);
            }

            $dataToSave = [
                'loai_van_don' => $request->input('loai_van_don'),
                'ngay_phat_hanh' => $request->input('ngay_phat_hanh'),
                'so_van_don_goc' => $request->input('so_van_don_goc'),
                'so_van_don' => $so_van_don,
                'so_cont' => $request->input('so_cont'),
                'so_chi' => $request->input('so_chi'),
                'phuong_thuc_dong_cont' => $request->input('phuong_thuc_dong_cont'),
                'dieu_kien_cuoc' => $request->input('dieu_kien_cuoc'),
                'ma_nguoi_gui_hang' => $request->input('ma_nguoi_gui_hang'),
                'ma_nguoi_nhan_hang' => $request->input('ma_nguoi_nhan_hang'),
                'ma_ben_duoc_thong_bao' => $request->input('ma_ben_duoc_thong_bao'),
                'ma_cang_di' => $request->input('ma_cang_di'),
                'ma_cang_den' => $request->input('ma_cang_den'),
                'ma_lo_hang' => $ma_lo_hang,
                'nguoi_sua_cuoi' => $nguoi_sua_cuoi // Cập nhật người sửa cuối cùng
            ];

            if ($ma_van_don) {
                DB::table('van_don')->where('ma_van_don', $ma_van_don)->update($dataToSave);
                $message = "Đã cập nhật vận đơn thành công!";
            } else {
                DB::table('van_don')->insert($dataToSave);
                $message = "Đã tạo vận đơn mới thành công!";
            }

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        $ma_van_don = $request->input('ma_van_don');
        $nguoi_sua_cuoi = $request->input('nguoi_sua_cuoi'); // Lấy người sửa cuối cùng để ghi nhận ai đã xóa

        if (empty($ma_van_don)) {
            return response()->json(['success' => false, 'message' => 'Thiếu mã vận đơn.']);
        }

        try {
            DB::table('van_don')
                ->where('ma_van_don', $ma_van_don)
                ->update([
                    'thoi_gian_xoa' => Carbon::now(),
                    'nguoi_sua_cuoi' => $nguoi_sua_cuoi // Ghi nhận người đã xóa
                ]);
            return response()->json(['success' => true, 'message' => 'Đã xóa vận đơn thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
}