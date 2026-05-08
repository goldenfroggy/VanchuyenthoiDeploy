<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BaoCaoThongKe extends Controller
{
    // ==========================================
    // 1. BÁO CÁO THỐNG KÊ VẬN CHUYỂN
    // ==========================================
    public function baoCaoVanChuyen(Request $request)
    {
        try {
            $tuNgayETD = $request->query('tu_ngay');
            $denNgayETA = $request->query('den_ngay');
            $trangThai = $request->query('trang_thai');
            $timKiem = $request->query('tim_kiem');

            $query = DB::table('lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->leftJoin('cang_bien as pol', 'booking.ma_cang_di', '=', 'pol.ma_cang')
                ->leftJoin('cang_bien as pod', 'booking.ma_cang_den', '=', 'pod.ma_cang')
                ->leftJoin('khach_hang', 'lo_hang.ma_khach_hang', '=', 'khach_hang.ma_khach_hang')
                ->leftJoin('hang_tau', 'booking.ma_hang_tau', '=', 'hang_tau.ma_hang_tau')
                ->leftJoin('van_don', 'lo_hang.ma_lo_hang', '=', 'van_don.ma_lo_hang')
                ->leftJoin(DB::raw('(SELECT ma_lo_hang, GROUP_CONCAT(ten_hang SEPARATOR ", ") as ten_hang_hoa, SUM(so_luong) as tong_so_luong, SUM(trong_luong) as tong_trong_luong, SUM(the_tich) as tong_the_tich FROM chi_tiet_lo_hang GROUP BY ma_lo_hang) as ctlh'), 'lo_hang.ma_lo_hang', '=', 'ctlh.ma_lo_hang')
                ->select(
                    'lo_hang.ma_lo_hang', 'lo_hang.trang_thai_lo_hang',
                    'booking.so_booking', 'booking.etd', 'booking.eta',
                    'pol.ten_cang as cang_di', 'pod.ten_cang as cang_den',
                    'khach_hang.ten_khach_hang',
                    'hang_tau.ten_hang_tau',
                    'van_don.so_van_don',
                    'ctlh.ten_hang_hoa', 'ctlh.tong_so_luong', 'ctlh.tong_trong_luong', 'ctlh.tong_the_tich'
                )
                ->where('lo_hang.thoi_gian_xoa', '<=', '2000-01-01 00:00:00');

            if ($tuNgayETD) $query->whereDate('booking.etd', '>=', $tuNgayETD);
            if ($denNgayETA) $query->whereDate('booking.eta', '<=', $denNgayETA);
            if ($trangThai) $query->where('lo_hang.trang_thai_lo_hang', $trangThai);
            if ($timKiem) {
                $query->where(function($q) use ($timKiem) {
                    $q->where('lo_hang.ma_lo_hang', 'LIKE', "%{$timKiem}%")
                      ->orWhere('booking.so_booking', 'LIKE', "%{$timKiem}%")
                      ->orWhere('ctlh.ten_hang_hoa', 'LIKE', "%{$timKiem}%");
                });
            }

            $danhSach = $query->orderBy('lo_hang.ma_lo_hang', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $danhSach,
                'thong_ke' => [
                    'tong_so' => $danhSach->count(),
                    'dang_van_chuyen' => $danhSach->where('trang_thai_lo_hang', 'Đang vận chuyển')->count(),
                    'hoan_thanh' => $danhSach->where('trang_thai_lo_hang', 'Hoàn tất')->count()
                ]
            ]);
        } catch (\Exception $e) { return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]); }
    }

    // ==========================================
    // 2. BÁO CÁO THỐNG KÊ SẢN LƯỢNG
    // ==========================================
    public function baoCaoSanLuong(Request $request)
    {
        try {
            $tuKy = $request->query('tu_ky'); // Định dạng: YYYY-MM
            $denKy = $request->query('den_ky'); // Định dạng: YYYY-MM
            $timKiem = $request->query('tim_kiem'); // Tên khách hàng

            $query = DB::table('lo_hang')
                ->join('khach_hang', 'lo_hang.ma_khach_hang', '=', 'khach_hang.ma_khach_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->leftJoin('cang_bien as pol', 'booking.ma_cang_di', '=', 'pol.ma_cang')
                ->leftJoin('cang_bien as pod', 'booking.ma_cang_den', '=', 'pod.ma_cang')
                ->leftJoin(DB::raw('(SELECT ma_lo_hang, SUM(so_kien) as tong_kien, SUM(trong_luong) as tong_trong_luong, SUM(the_tich) as tong_the_tich FROM chi_tiet_lo_hang GROUP BY ma_lo_hang) as ctlh'), 'lo_hang.ma_lo_hang', '=', 'ctlh.ma_lo_hang')
                ->where('lo_hang.thoi_gian_xoa', '<=', '2000-01-01 00:00:00')
                ->select(
                    'khach_hang.ma_khach_hang', 'khach_hang.ten_khach_hang',
                    'lo_hang.ma_lo_hang',
                    'booking.etd',
                    'pol.ten_cang as cang_di', 'pod.ten_cang as cang_den',
                    'ctlh.tong_kien', 'ctlh.tong_trong_luong', 'ctlh.tong_the_tich'
                );

            // Lọc theo tháng/năm (Dựa vào ngày ETD của chuyến tàu)
            if ($tuKy) {
                $query->whereDate('booking.etd', '>=', $tuKy . '-01');
            }
            if ($denKy) {
                $lastDay = date('Y-m-t', strtotime($denKy . '-01')); // Lấy ngày cuối cùng của tháng "đến kỳ"
                $query->whereDate('booking.etd', '<=', $lastDay);
            }
            if ($timKiem) {
                $query->where('khach_hang.ten_khach_hang', 'LIKE', "%{$timKiem}%");
            }

            $rawData = $query->get();

            // Xử lý GOM NHÓM THEO KHÁCH HÀNG (Group By Customer)
            $grouped = [];
            foreach ($rawData as $item) {
                $khId = $item->ma_khach_hang;
                if (!isset($grouped[$khId])) {
                    $grouped[$khId] = [
                        'ma_khach_hang' => $khId,
                        'ten_khach_hang' => $item->ten_khach_hang,
                        'tong_so_lo' => 0,
                        'tong_so_kien' => 0,
                        'tong_trong_luong' => 0,
                        'tong_the_tich' => 0,
                        'chi_tiet' => []
                    ];
                }

                // Cộng dồn các chỉ số cho khách hàng đó
                $grouped[$khId]['tong_so_lo'] += 1;
                $grouped[$khId]['tong_so_kien'] += $item->tong_kien ?? 0;
                $grouped[$khId]['tong_trong_luong'] += $item->tong_trong_luong ?? 0;
                $grouped[$khId]['tong_the_tich'] += $item->tong_the_tich ?? 0;

                // Lưu lại thông tin chi tiết của lô hàng đó để xem ở Modal
                $grouped[$khId]['chi_tiet'][] = [
                    'ma_lo_hang' => $item->ma_lo_hang,
                    'etd' => $item->etd,
                    'tuyen_duong' => ($item->cang_di ?? '?') . ' ➔ ' . ($item->cang_den ?? '?'),
                    'so_kien' => $item->tong_kien ?? 0,
                    'trong_luong' => $item->tong_trong_luong ?? 0,
                    'the_tich' => $item->tong_the_tich ?? 0,
                ];
            }

            // Ép kiểu mảng liên hợp về mảng tuần tự cho Vue dễ xử lý
            $result = array_values($grouped);

            return response()->json(['success' => true, 'data' => $result]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    // ==========================================
    // 3. BÁO CÁO THỐNG KÊ BOOKING
    // ==========================================
    public function baoCaoBooking(Request $request)
    {
        try {
            $tuKy = $request->query('tu_ky'); // YYYY-MM
            $denKy = $request->query('den_ky'); // YYYY-MM
            $timKiem = $request->query('tim_kiem'); // Số booking, tên tàu
            $maHangTau = $request->query('ma_hang_tau'); // Lọc theo hãng tàu

            $query = DB::table('booking')
                ->leftJoin('hang_tau', 'booking.ma_hang_tau', '=', 'hang_tau.ma_hang_tau')
                ->leftJoin('cang_bien as pol', 'booking.ma_cang_di', '=', 'pol.ma_cang')
                ->leftJoin('cang_bien as pod', 'booking.ma_cang_den', '=', 'pod.ma_cang')
                ->leftJoin('lo_hang', 'booking.ma_booking', '=', 'lo_hang.ma_booking')
                ->select(
                    'booking.ma_booking',
                    'booking.so_booking',
                    'booking.ten_con_tau',
                    'booking.etd',
                    'booking.gio_cat_mang',
                    'hang_tau.ten_hang_tau',
                    'pol.ten_cang as cang_di',
                    'pod.ten_cang as cang_den',
                    'lo_hang.trang_thai_lo_hang'
                )
                ->where('booking.thoi_gian_xoa', '<=', '2000-01-01 00:00:00');

            // --- LỌC DỮ LIỆU ---
            if ($tuKy) {
                $query->whereDate('booking.etd', '>=', $tuKy . '-01');
            }
            if ($denKy) {
                $lastDay = date('Y-m-t', strtotime($denKy . '-01'));
                $query->whereDate('booking.etd', '<=', $lastDay);
            }
            if ($maHangTau) {
                $query->where('booking.ma_hang_tau', $maHangTau);
            }
            if ($timKiem) {
                $query->where(function($q) use ($timKiem) {
                    $q->where('booking.so_booking', 'LIKE', "%{$timKiem}%")
                      ->orWhere('booking.ten_con_tau', 'LIKE', "%{$timKiem}%");
                });
            }

            // Lấy danh sách booking và Gom nhóm theo mã booking (tránh trùng lặp do 1 booking có nhiều lô hàng)
            // Lấy luôn trạng thái của Lô hàng đầu tiên để biết Booking đã hoàn thành hay chưa
            $danhSachGoc = $query->orderBy('booking.etd', 'desc')->get();
            
            $danhSachUnique = [];
            $tongSo = 0;
            $hoanTat = 0;
            $chuaHoanThanh = 0;

            foreach ($danhSachGoc as $item) {
                if (!isset($danhSachUnique[$item->ma_booking])) {
                    $danhSachUnique[$item->ma_booking] = clone $item;
                    $tongSo++;
                    
                    // Logic: Nếu Lô hàng thuộc Booking này có trạng thái 'Hoàn tất' -> Booking Hoàn Tất
                    if ($item->trang_thai_lo_hang === 'Hoàn tất' || $item->trang_thai_lo_hang === 'Đã thông quan') {
                        $danhSachUnique[$item->ma_booking]->trang_thai = 'Đã hoàn tất';
                        $hoanTat++;
                    } else {
                        $danhSachUnique[$item->ma_booking]->trang_thai = 'Chưa hoàn thành';
                        $chuaHoanThanh++;
                    }
                }
            }

            // Lấy danh sách Hãng tàu cho Combobox
            $danhSachHangTau = DB::table('hang_tau')
                ->where('thoi_gian_xoa', '<=', '2000-01-01 00:00:00')
                ->select('ma_hang_tau', 'ten_hang_tau')
                ->orderBy('ten_hang_tau', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => array_values($danhSachUnique),
                'hang_tau' => $danhSachHangTau,
                'thong_ke' => [
                    'tong_so' => $tongSo,
                    'chua_hoan_thanh' => $chuaHoanThanh,
                    'da_hoan_tat' => $hoanTat
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    // ==========================================
    // 4. BÁO CÁO THỐNG KÊ CHI PHÍ TỒN ĐỌNG
    // ==========================================
    public function baoCaoChiPhiTonDong(Request $request)
    {
        try {
            $tuKy = $request->query('tu_ky'); // YYYY-MM
            $denKy = $request->query('den_ky'); // YYYY-MM
            $loaiGiaoDich = $request->query('loai_giao_dich'); // THU hoặc CHI
            $trangThaiTT = $request->query('trang_thai_tt'); // Mới: Lọc trạng thái thanh toán
            $timKiem = $request->query('tim_kiem');

            // Lấy TẤT CẢ chi phí, kể cả đã hoàn thành
            $query = DB::table('chi_phi')
                ->join('lo_hang', 'chi_phi.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->select(
                    'chi_phi.ma_chi_phi',
                    'chi_phi.ten_chi_phi',
                    'chi_phi.tong_tien',
                    'chi_phi.loai_giao_dich',
                    'chi_phi.trang_thai_thanh_toan',
                    'lo_hang.ma_lo_hang',
                    'booking.etd'
                )
                ->where('chi_phi.thoi_gian_xoa', '<=', '2000-01-01 00:00:00');

            // --- LỌC DỮ LIỆU ---
            if ($tuKy) {
                $query->whereDate('booking.etd', '>=', $tuKy . '-01');
            }
            if ($denKy) {
                $lastDay = date('Y-m-t', strtotime($denKy . '-01'));
                $query->whereDate('booking.etd', '<=', $lastDay);
            }
            if ($loaiGiaoDich) {
                $query->where('chi_phi.loai_giao_dich', $loaiGiaoDich);
            }
            if ($trangThaiTT) { // Áp dụng bộ lọc trạng thái nếu người dùng chọn
                $query->where('chi_phi.trang_thai_thanh_toan', $trangThaiTT);
            }
            if ($timKiem) {
                $query->where(function($q) use ($timKiem) {
                    $q->where('chi_phi.ten_chi_phi', 'LIKE', "%{$timKiem}%")
                      ->orWhere('lo_hang.ma_lo_hang', 'LIKE', "%{$timKiem}%");
                });
            }

            $danhSach = $query->orderBy('chi_phi.ma_chi_phi', 'desc')->get();

            // --- TÍNH TỔNG TIỀN TỒN ĐỌNG CHO 2 THẺ THỐNG KÊ ---
            $phaiThu = 0;
            $phaiTra = 0;

            foreach ($danhSach as $cp) {
                // CHỈ CỘNG TIỀN VÀO TỔNG NẾU CHƯA THANH TOÁN XONG
                if ($cp->trang_thai_thanh_toan !== 'Đã thanh toán') {
                    if ($cp->loai_giao_dich === 'THU') {
                        $phaiThu += $cp->tong_tien;
                    } else if ($cp->loai_giao_dich === 'CHI') {
                        $phaiTra += $cp->tong_tien;
                    }
                }
            }
            return response()->json([
                'success' => true,
                'data' => $danhSach,
                'thong_ke' => [
                    'tong_phai_thu' => $phaiThu,
                    'tong_phai_tra' => $phaiTra
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    // ==========================================
    // 5. BÁO CÁO THỐNG KÊ CƯỢC VỎ
    // ==========================================
    public function baoCaoCuocVo(Request $request)
    {
        try {
            $tuKy = $request->query('tu_ky'); // YYYY-MM
            $denKy = $request->query('den_ky'); // YYYY-MM
            $tinhTrangCuocVo = $request->query('tinh_trang_cuoc_vo'); // Có / Không
            $timKiem = $request->query('tim_kiem');

            $query = DB::table('thong_tin_luu_bai')
                ->join('lo_hang', 'thong_tin_luu_bai.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->leftJoin('van_don', 'lo_hang.ma_lo_hang', '=', 'van_don.ma_lo_hang')
                ->select(
                    'lo_hang.ma_lo_hang',
                    'van_don.so_cont',
                    'thong_tin_luu_bai.cuoc_vo',
                    'thong_tin_luu_bai.ngay_bat_dau_luu_bai',
                    'thong_tin_luu_bai.trang_thai_luu_bai',
                    'booking.etd'
                )
                ->where('lo_hang.thoi_gian_xoa', '<=', '2000-01-01 00:00:00');

            // --- LỌC DỮ LIỆU ---
            if ($tuKy) {
                $query->whereDate('booking.etd', '>=', $tuKy . '-01');
            }
            if ($denKy) {
                $lastDay = date('Y-m-t', strtotime($denKy . '-01'));
                $query->whereDate('booking.etd', '<=', $lastDay);
            }
            if ($tinhTrangCuocVo) {
                $query->where('thong_tin_luu_bai.cuoc_vo', $tinhTrangCuocVo);
            }
            if ($timKiem) {
                $query->where(function($q) use ($timKiem) {
                    $q->where('van_don.so_cont', 'LIKE', "%{$timKiem}%")
                      ->orWhere('lo_hang.ma_lo_hang', 'LIKE', "%{$timKiem}%");
                });
            }

            $danhSach = $query->orderBy('thong_tin_luu_bai.ma_luu_bai', 'desc')->get();

            // --- TÍNH THỐNG KÊ CHO 3 BOX ---
            $chuaTra = $danhSach->where('trang_thai_luu_bai', 'Đang lưu bãi')->count();
            $daTra = $danhSach->where('trang_thai_luu_bai', 'Đã trả vỏ')->count();
            $coCuocVo = $danhSach->where('cuoc_vo', 'Có')->count(); 

            return response()->json([
                'success' => true,
                'data' => $danhSach,
                'thong_ke' => [
                    'chua_tra' => $chuaTra,
                    'da_tra' => $daTra,
                    'co_cuoc_vo' => $coCuocVo
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    // ==========================================
    // 6. BÁO CÁO THỐNG KÊ CẢNH BÁO LƯU BÃI
    // ==========================================
    public function baoCaoCanhBaoLuuBai(Request $request)
    {
        try {
            $timKiem = $request->query('tim_kiem');

            // CHỈ LẤY CÁC CONT ĐANG CÒN NẰM TRONG BÃI
            $query = DB::table('thong_tin_luu_bai')
                ->join('lo_hang', 'thong_tin_luu_bai.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('van_don', 'lo_hang.ma_lo_hang', '=', 'van_don.ma_lo_hang')
                ->select(
                    'thong_tin_luu_bai.ma_luu_bai',
                    'lo_hang.ma_lo_hang',
                    'van_don.so_cont',
                    'thong_tin_luu_bai.ngay_bat_dau_luu_bai',
                    'thong_tin_luu_bai.ngay_luu_bai_mien_phi',
                    'thong_tin_luu_bai.trang_thai_luu_bai'
                )
                ->where('lo_hang.thoi_gian_xoa', '<=', '2000-01-01 00:00:00')
                ->where('thong_tin_luu_bai.trang_thai_luu_bai', 'Đang lưu bãi'); // Chốt chỉ lấy Đang lưu bãi

            if ($timKiem) {
                $query->where(function($q) use ($timKiem) {
                    $q->where('van_don.so_cont', 'LIKE', "%{$timKiem}%")
                      ->orWhere('lo_hang.ma_lo_hang', 'LIKE', "%{$timKiem}%");
                });
            }

            $danhSach = $query->orderBy('thong_tin_luu_bai.ngay_bat_dau_luu_bai', 'asc')->get();

            // --- XỬ LÝ LOGIC NGÀY CÒN LẠI VÀ PHÂN LOẠI CẢNH BÁO ---
            $anToan = 0;
            $sapHetHan = 0;
            $quaHan = 0;
            
            // Lấy 0h00 của ngày hôm nay để tính toán
            $today = strtotime(date('Y-m-d')); 

            foreach ($danhSach as $item) {
                // Tính số ngày đã trôi qua
                $ngayBatDau = strtotime(date('Y-m-d', strtotime($item->ngay_bat_dau_luu_bai)));
                $soNgayDaLuu = floor(($today - $ngayBatDau) / (60 * 60 * 24));
                if ($soNgayDaLuu < 0) $soNgayDaLuu = 0;

                // Tính số ngày CÒN LẠI
                $soNgayConLai = $item->ngay_luu_bai_mien_phi - $soNgayDaLuu;
                $item->so_ngay_con_lai = $soNgayConLai;

                // Phân loại cảnh báo
                if ($soNgayConLai < 0) {
                    $item->muc_do_canh_bao = 'Quá hạn lưu';
                    $quaHan++;
                } elseif ($soNgayConLai <= 2) {
                    $item->muc_do_canh_bao = 'Sắp hết hạn';
                    $sapHetHan++;
                } else {
                    $item->muc_do_canh_bao = 'An toàn';
                    $anToan++;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $danhSach,
                'thong_ke' => [
                    'an_toan' => $anToan,
                    'sap_het_han' => $sapHetHan,
                    'qua_han' => $quaHan
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
}