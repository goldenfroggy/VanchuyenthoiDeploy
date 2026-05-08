<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class QuanLyLenhGiaoHang extends Controller 
{
    public function index()
    {
        try {
            // 1. Lấy danh sách Thông Báo Hàng Đến (AN)
            $thongBaoHangDen = DB::table('thong_bao_hang_den')
                ->leftJoin('lo_hang', 'thong_bao_hang_den.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->leftJoin('khach_hang', 'lo_hang.ma_khach_hang', '=', 'khach_hang.ma_khach_hang')
                ->leftJoin('van_don', 'lo_hang.ma_lo_hang', '=', 'van_don.ma_lo_hang')
                ->select(
                    'thong_bao_hang_den.ma_thong_bao_hang_den as ma_phieu', 
                    'thong_bao_hang_den.ngay_phat_hanh', 
                    'thong_bao_hang_den.ma_lo_hang', 
                    'lo_hang.ma_booking',
                    'lo_hang.ten_lo_hang',
                    'lo_hang.dieu_kien_thuong_mai', 
                    'lo_hang.trang_thai_lo_hang',
                    'booking.so_booking',
                    'booking.ten_con_tau',
                    'booking.so_chuyen', 
                    'booking.eta', 
                    'booking.etd', 
                    'booking.gio_cat_mang',
                    'khach_hang.ten_khach_hang',
                    'van_don.so_van_don',
                    'van_don.so_cont',
                    'van_don.loai_van_don',
                    'van_don.so_van_don_goc',
                    'van_don.so_chi',
                    'van_don.phuong_thuc_dong_cont'
                )
                ->orderBy('thong_bao_hang_den.ma_thong_bao_hang_den', 'desc')
                ->get();

            // 2. Lấy danh sách Lệnh giao hàng (DO)
            $lenhGiaoHang = DB::table('lenh_giao_hang')
                ->leftJoin('lo_hang', 'lenh_giao_hang.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->leftJoin('van_don', 'lo_hang.ma_lo_hang', '=', 'van_don.ma_lo_hang')
                ->leftJoin('khach_hang', 'lo_hang.ma_khach_hang', '=', 'khach_hang.ma_khach_hang')
                ->select(
                    'lenh_giao_hang.ma_lenh_giao_hang as ma_phieu', 
                    'lenh_giao_hang.ngay_phat_hanh', 
                    'lenh_giao_hang.ma_lo_hang', 
                    'lo_hang.ma_booking',
                    'lo_hang.ten_lo_hang',
                    'lo_hang.dieu_kien_thuong_mai', 
                    'lo_hang.trang_thai_lo_hang',
                    'booking.so_booking', 
                    'booking.ten_con_tau', 
                    'booking.so_chuyen', 
                    'booking.eta', 
                    'booking.etd', 
                    'booking.gio_cat_mang',
                    'van_don.so_van_don',
                    'van_don.so_van_don_goc', 
                    'van_don.so_chi', 
                    'van_don.loai_van_don', 
                    'van_don.phuong_thuc_dong_cont',
                    'van_don.so_cont',
                    'khach_hang.ten_khach_hang'
                )
                ->orderBy('lenh_giao_hang.ma_lenh_giao_hang', 'desc')
                ->get();
            
            // 3. Lấy danh sách Biên bản giao nhận (BBGN) - Đã cập nhật ma_hang_van_tai
            $bienBanGiaoNhan = DB::table('bien_ban_giao_nhan')
                ->leftJoin('lo_hang', 'bien_ban_giao_nhan.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->leftJoin('van_don', 'lo_hang.ma_lo_hang', '=', 'van_don.ma_lo_hang')
                ->leftJoin('khach_hang', 'lo_hang.ma_khach_hang', '=', 'khach_hang.ma_khach_hang')
                ->leftJoin('hang_van_tai', 'bien_ban_giao_nhan.ma_hang_van_tai', '=', 'hang_van_tai.ma_hang_van_tai') // JOIN thêm Hãng vận tải
                ->select(
                    'bien_ban_giao_nhan.ma_bien_ban_giao_nhan as ma_phieu', 
                    'bien_ban_giao_nhan.ngay_phat_hanh', // Chuẩn theo Database của bạn
                    'bien_ban_giao_nhan.ma_lo_hang', 
                    'bien_ban_giao_nhan.ma_hang_van_tai', 
                    'hang_van_tai.ten_hang_van_tai', // Lấy tên hãng vận tải để hiện trên Vue
                    'lo_hang.ma_booking',
                    'lo_hang.ten_lo_hang',
                    'lo_hang.dieu_kien_thuong_mai', 
                    'lo_hang.trang_thai_lo_hang',
                    'booking.so_booking', 
                    'booking.ten_con_tau', 
                    'booking.so_chuyen', 
                    'booking.eta', 
                    'booking.etd', 
                    'booking.gio_cat_mang',
                    'van_don.so_van_don',
                    'van_don.so_van_don_goc', 
                    'van_don.so_chi', 
                    'van_don.loai_van_don', 
                    'van_don.phuong_thuc_dong_cont',
                    'van_don.so_cont',
                    'khach_hang.ten_khach_hang'
                )
                ->orderBy('bien_ban_giao_nhan.ma_bien_ban_giao_nhan', 'desc')
                ->get();

            // Lấy danh sách Lô hàng cho Combobox
            $loHang = DB::table('lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->where('lo_hang.thoi_gian_xoa', '<=', '2000-01-01 00:00:00')
                ->select('lo_hang.ma_lo_hang', 'lo_hang.ma_booking', 'lo_hang.ten_lo_hang', 'booking.so_booking as so_booking')
                ->get();

            // Lấy danh sách Hãng vận tải cho Combobox Tab BBGN
            $hangVanTai = DB::table('hang_van_tai')
                ->select('ma_hang_van_tai', 'ten_hang_van_tai')
                ->get();

            return response()->json([
                "success" => true, 
                "data_an" => $thongBaoHangDen, 
                "data_do" => $lenhGiaoHang, 
                "data_bbgn" => $bienBanGiaoNhan, 
                "lo_hang" => $loHang,
                "hang_van_tai" => $hangVanTai // Trả thêm Data hãng xe
            ]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi DB: " . $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $loai = $request->input('loai'); // 'AN', 'DO', hoặc 'BBGN'
            $ma_phieu = $request->input('ma_phieu');
            
            // Xử lý động bảng và khóa chính dựa theo loại
            if ($loai === 'AN') {
                $table = 'thong_bao_hang_den';
                $pk = 'ma_thong_bao_hang_den';
                $data = ['ngay_phat_hanh' => $request->input('ngay_phat_hanh'), 'ma_lo_hang' => $request->input('ma_lo_hang')];
            } elseif ($loai === 'DO') {
                $table = 'lenh_giao_hang';
                $pk = 'ma_lenh_giao_hang';
                $data = ['ngay_phat_hanh' => $request->input('ngay_phat_hanh'), 'ma_lo_hang' => $request->input('ma_lo_hang')];
            } elseif ($loai === 'BBGN') {
                $table = 'bien_ban_giao_nhan';
                $pk = 'ma_bien_ban_giao_nhan';
                // BBGN có thêm Hãng vận tải
                $data = [
                    'ngay_phat_hanh' => $request->input('ngay_phat_hanh'), 
                    'ma_lo_hang' => $request->input('ma_lo_hang'),
                    'ma_hang_van_tai' => $request->input('ma_hang_van_tai') // Cập nhật nhà xe chở hàng
                ];
            } else {
                throw new \Exception("Loại chứng từ không hợp lệ");
            }

            if ($ma_phieu) {
                DB::table($table)->where($pk, $ma_phieu)->update($data);
                $msg = "Đã cập nhật thành công!";
            } else {
                DB::table($table)->insert($data);
                $msg = "Đã thêm mới thành công!";
            }

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $loai = $request->input('loai');
            $id = $request->input('ma_phieu');
            
            if ($loai === 'AN') {
                $table = 'thong_bao_hang_den';
                $pk = 'ma_thong_bao_hang_den';
            } elseif ($loai === 'DO') {
                $table = 'lenh_giao_hang';
                $pk = 'ma_lenh_giao_hang';
            } elseif ($loai === 'BBGN') {
                $table = 'bien_ban_giao_nhan';
                $pk = 'ma_bien_ban_giao_nhan';
            }

            DB::table($table)->where($pk, $id)->delete();
            return response()->json(['success' => true, 'message' => 'Đã xóa dữ liệu thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $loai = $request->query('loai'); 
            $id = $request->query('id');

            if ($loai === 'AN') {
                // ... (Phần AN giữ nguyên như cũ)
                $data = DB::table('thong_bao_hang_den')
                    ->leftJoin('lo_hang', 'thong_bao_hang_den.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                    ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                    ->leftJoin('van_don', 'lo_hang.ma_lo_hang', '=', 'van_don.ma_lo_hang')
                    ->leftJoin('khach_hang as notify_party', 'van_don.ma_ben_duoc_thong_bao', '=', 'notify_party.ma_khach_hang')
                    ->leftJoin('khach_hang as consignee', 'van_don.ma_nguoi_nhan_hang', '=', 'consignee.ma_khach_hang')
                    ->leftJoin('cang_bien as pol', 'van_don.ma_cang_di', '=', 'pol.ma_cang')
                    ->leftJoin('cang_bien as pod', 'van_don.ma_cang_den', '=', 'pod.ma_cang')
                    ->leftJoin(DB::raw('(SELECT ma_lo_hang, SUM(so_kien) as tong_kien, SUM(trong_luong) as tong_trong_luong, SUM(the_tich) as tong_the_tich FROM chi_tiet_lo_hang GROUP BY ma_lo_hang) as ctlh'), 'lo_hang.ma_lo_hang', '=', 'ctlh.ma_lo_hang')
                    ->where('thong_bao_hang_den.ma_thong_bao_hang_den', $id)
                    ->select(
                        'thong_bao_hang_den.ngay_phat_hanh',
                        'lo_hang.ten_lo_hang',
                        'booking.ten_con_tau', 'booking.so_chuyen', 'booking.eta',
                        'van_don.so_van_don', 'van_don.so_van_don_goc', 'van_don.so_cont', 'van_don.so_chi',
                        'notify_party.ten_khach_hang as notify_name', 'notify_party.dia_chi as notify_address', 'notify_party.so_dien_thoai as notify_tel', 'notify_party.so_fax as notify_fax',
                        'consignee.ten_khach_hang as consignee_name', 'consignee.dia_chi as consignee_address', 'consignee.so_dien_thoai as consignee_tel', 'consignee.so_fax as consignee_fax',
                        'pol.ten_cang as pol_name',
                        'pod.ten_cang as pod_name',
                        'pod.dia_chi as pod_address',
                        'ctlh.tong_kien', 'ctlh.tong_trong_luong', 'ctlh.tong_the_tich'
                    )
                    ->first();

                if (!$data) return response()->json(['success' => false, 'message' => 'Không tìm thấy dữ liệu AN']);
                $pdf = Pdf::loadView('pdf.arrival_notice', ['data' => $data]);
                return $pdf->download('Arrival_Notice_' . ($data->so_van_don ?? 'AN') . '.pdf');

            } elseif ($loai === 'DO') {
                // ... (Phần DO giữ nguyên như cũ)
                $data = DB::table('lenh_giao_hang')
                    ->leftJoin('lo_hang', 'lenh_giao_hang.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                    ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                    ->leftJoin('van_don', 'lo_hang.ma_lo_hang', '=', 'van_don.ma_lo_hang')
                    ->leftJoin('khach_hang as consignee', 'van_don.ma_nguoi_nhan_hang', '=', 'consignee.ma_khach_hang')
                    ->leftJoin('cang_bien as pol', 'van_don.ma_cang_di', '=', 'pol.ma_cang')
                    ->leftJoin('cang_bien as pod', 'van_don.ma_cang_den', '=', 'pod.ma_cang')
                    ->leftJoin(DB::raw('(SELECT ma_lo_hang, SUM(so_kien) as tong_kien, SUM(trong_luong) as tong_trong_luong, SUM(the_tich) as tong_the_tich FROM chi_tiet_lo_hang GROUP BY ma_lo_hang) as ctlh'), 'lo_hang.ma_lo_hang', '=', 'ctlh.ma_lo_hang')
                    ->where('lenh_giao_hang.ma_lenh_giao_hang', $id)
                    ->select(
                        'lenh_giao_hang.ngay_phat_hanh',
                        'lo_hang.ten_lo_hang',
                        'booking.ten_con_tau', 'booking.so_chuyen', 'booking.eta',
                        'van_don.so_van_don', 'van_don.so_van_don_goc', 'van_don.so_cont', 'van_don.so_chi',
                        'consignee.ten_khach_hang as consignee_name', 'consignee.dia_chi as consignee_address',
                        'pol.ten_cang as pol_name',
                        'pod.ten_cang as pod_name',
                        'pod.dia_chi as pod_address',
                        'ctlh.tong_kien', 'ctlh.tong_trong_luong', 'ctlh.tong_the_tich'
                    )
                    ->first();

                if (!$data) return response()->json(['success' => false, 'message' => 'Không tìm thấy dữ liệu DO']);
                $pdf = Pdf::loadView('pdf.delivery_order', ['data' => $data]);
                return $pdf->download('Delivery_Order_' . ($data->so_van_don ?? 'DO') . '.pdf');
                
            } elseif ($loai === 'BBGN') {
                // XUẤT PDF CHO BIÊN BẢN GIAO NHẬN (Đã JOIN thêm Hãng vận tải)
                $data = DB::table('bien_ban_giao_nhan')
                    ->leftJoin('lo_hang', 'bien_ban_giao_nhan.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                    ->leftJoin('van_don', 'lo_hang.ma_lo_hang', '=', 'van_don.ma_lo_hang')
                    ->leftJoin('khach_hang as consignee', 'van_don.ma_nguoi_nhan_hang', '=', 'consignee.ma_khach_hang')
                    ->leftJoin('hang_van_tai', 'bien_ban_giao_nhan.ma_hang_van_tai', '=', 'hang_van_tai.ma_hang_van_tai') // JOIN nhà xe
                    ->leftJoin(DB::raw('(SELECT ma_lo_hang, SUM(so_kien) as tong_kien, SUM(trong_luong) as tong_trong_luong FROM chi_tiet_lo_hang GROUP BY ma_lo_hang) as ctlh'), 'lo_hang.ma_lo_hang', '=', 'ctlh.ma_lo_hang')
                    ->where('bien_ban_giao_nhan.ma_bien_ban_giao_nhan', $id)
                    ->select(
                        'bien_ban_giao_nhan.*', 
                        'lo_hang.ten_lo_hang', 
                        'van_don.so_van_don', 'van_don.so_cont', 'van_don.so_chi',
                        'consignee.ten_khach_hang', 'consignee.dia_chi', 'consignee.so_dien_thoai',
                        'hang_van_tai.ten_hang_van_tai', // Lấy tên nhà xe in ra PDF
                        'ctlh.tong_kien', 'ctlh.tong_trong_luong'
                    )
                    ->first();

                if (!$data) return response()->json(['success' => false, 'message' => 'Không tìm thấy dữ liệu BBGN']);
                
                $pdf = Pdf::loadView('pdf.bien_ban_giao_nhan', ['data' => $data]);
                return $pdf->download('BBGN_' . ($data->so_van_don ?? $id) . '.pdf');
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi xuất PDF: ' . $e->getMessage()]);
        }
    }
}