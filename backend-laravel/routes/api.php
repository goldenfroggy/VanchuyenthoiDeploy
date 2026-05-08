<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\QuanLyDangNhap;
use App\Http\Controllers\QuanLyTaiKhoan;
use App\Http\Controllers\QuanLyKhachHang;
use App\Http\Controllers\QuanLyHangTau;
use App\Http\Controllers\QuanLyKhoCang;
use App\Http\Controllers\QuanLyHangHoa;
use App\Http\Controllers\QuanLyHangVanTai;
use App\Http\Controllers\QuanLyDonViTinh;
use App\Http\Controllers\QuanLyChiPhi;
use App\Http\Controllers\QuanLyQuyen;
use App\Http\Controllers\QuanLyBooking;
use App\Http\Controllers\QuanLyLoHang;
use App\Http\Controllers\QuanLyChiTietLoHang;
use App\Http\Controllers\QuanLyVanDon;
use App\Http\Controllers\QuanLyChungTu;
use App\Http\Controllers\QuanLyToKhaiHaiQuan;
use App\Http\Controllers\QuanLyLuuBai;
use App\Http\Controllers\QuanLyLenhGiaoHang;
use App\Http\Controllers\BaoCaoThongKe;
use App\Http\Controllers\QuanLyTrangThaiThanhToan;

/*
|--------------------------------------------------------------------------
| 1. API ĐĂNG NHẬP
|--------------------------------------------------------------------------
*/
Route::post('/login', [QuanLyDangNhap::class, 'login']);

/*
|--------------------------------------------------------------------------
| 2. API QUẢN LÝ TÀI KHOẢN
|--------------------------------------------------------------------------
*/
Route::get('/accounts', [QuanLyTaiKhoan::class, 'index']);
Route::post('/accounts/save', [QuanLyTaiKhoan::class, 'save']);
Route::post('/accounts/delete', [QuanLyTaiKhoan::class, 'delete']);

/*
|--------------------------------------------------------------------------
| 3. API HỒ SƠ CÁ NHÂN
|--------------------------------------------------------------------------
*/
Route::get('/user-info', [QuanLyDangNhap::class, 'userInfo']);
Route::post('/profile/update', [QuanLyDangNhap::class, 'updateProfile']);
Route::post('/profile/change-password', [QuanLyDangNhap::class, 'changePassword']);

/*
|--------------------------------------------------------------------------
| 4. API QUẢN LÝ DANH MỤC - KHÁCH HÀNG
|--------------------------------------------------------------------------
*/
Route::get('/khach-hang', [QuanLyKhachHang::class, 'index']);
Route::post('/khach-hang/save', [QuanLyKhachHang::class, 'save']);
Route::post('/khach-hang/delete', [QuanLyKhachHang::class, 'delete']);

/*
|--------------------------------------------------------------------------
| 5. API QUẢN LÝ DANH MỤC - HÃNG TÀU
|--------------------------------------------------------------------------
*/
Route::get('/hang-tau', [QuanLyHangTau::class, 'index']);
Route::post('/hang-tau/save', [QuanLyHangTau::class, 'save']);
Route::post('/hang-tau/delete', [QuanLyHangTau::class, 'delete']);

/*
|--------------------------------------------------------------------------
| 6. API QUẢN LÝ DANH MỤC - CẢNG BIỂN
|--------------------------------------------------------------------------
*/
Route::get('/cang-bien', [QuanLyKhoCang::class, 'index']);
Route::post('/cang-bien/save', [QuanLyKhoCang::class, 'save']);
Route::post('/cang-bien/delete', [QuanLyKhoCang::class, 'delete']);

/*
|--------------------------------------------------------------------------
| 7. API QUẢN LÝ DANH MỤC - HÀNG HÓA
|--------------------------------------------------------------------------
*/
Route::get('/hang-hoa', [QuanLyHangHoa::class, 'index']);
Route::post('/hang-hoa/save', [QuanLyHangHoa::class, 'save']);
Route::post('/hang-hoa/delete', [QuanLyHangHoa::class, 'delete']);

/*
|--------------------------------------------------------------------------
| 8. API QUẢN LÝ DANH MỤC - HÃNG VẬN TẢI
|--------------------------------------------------------------------------
*/
Route::get('/hang-van-tai', [QuanLyHangVanTai::class, 'index']);
Route::post('/hang-van-tai/save', [QuanLyHangVanTai::class, 'save']);
Route::post('/hang-van-tai/delete', [QuanLyHangVanTai::class, 'delete']);

/*
|--------------------------------------------------------------------------
| 9. API QUẢN LÝ DANH MỤC - ĐƠN VỊ TÍNH
|--------------------------------------------------------------------------
*/
Route::get('/don-vi-tinh', [QuanLyDonViTinh::class, 'index']);
Route::post('/don-vi-tinh/save', [QuanLyDonViTinh::class, 'save']);
Route::post('/don-vi-tinh/delete', [QuanLyDonViTinh::class, 'delete']);

/*
|--------------------------------------------------------------------------
| QUẢN LÝ CHI PHÍ 
|--------------------------------------------------------------------------
*/
Route::get('/chi-phi', [QuanLyChiPhi::class, 'index']);
Route::post('/chi-phi/save', [QuanLyChiPhi::class, 'store']);
Route::post('/chi-phi/delete', [QuanLyChiPhi::class, 'destroy']);
Route::post('/chi-phi/update-status', [QuanLyChiPhi::class, 'updateStatus']);



/*
|--------------------------------------------------------------------------
| 11. API QUẢN LÝ BOOKING
|--------------------------------------------------------------------------
*/
Route::get('/bookings', [QuanLyBooking::class, 'index']);
Route::get('/bookings/references', [QuanLyBooking::class, 'getReferences']);
Route::post('/bookings/save', [QuanLyBooking::class, 'save']);
Route::post('/bookings/delete', [QuanLyBooking::class, 'delete']);


/*
|--------------------------------------------------------------------------
| 12. API QUẢN LÝ LÔ HÀNG
|--------------------------------------------------------------------------
*/
Route::get('/lo-hang', [QuanLyLoHang::class, 'index']);
Route::post('/lo-hang/save', [QuanLyLoHang::class, 'save']);
Route::post('/lo-hang/delete', [QuanLyLoHang::class, 'delete']);
Route::get('/lo-hang/references', [QuanLyLoHang::class, 'getReferences']);

/*
|--------------------------------------------------------------------------
| 13. API CHI TIẾT LÔ HÀNG
|--------------------------------------------------------------------------
*/
Route::get('/chi-tiet-lo-hang', [QuanLyChiTietLoHang::class, 'index']);
Route::post('/chi-tiet-lo-hang/save', [QuanLyChiTietLoHang::class, 'save']);
Route::post('/chi-tiet-lo-hang/delete', [QuanLyChiTietLoHang::class, 'delete']);
Route::get('/chi-tiet-lo-hang/references', [QuanLyChiTietLoHang::class, 'getReferences']);

/*
|--------------------------------------------------------------------------
| 14. QUẢN LÝ CHỨNG TỪ SỐ HÓA
|--------------------------------------------------------------------------
*/
Route::get('/chung-tu', [QuanLyChungTu::class, 'index']);
Route::post('/chung-tu/save', [QuanLyChungTu::class, 'store']);
Route::post('/chung-tu/delete', [QuanLyChungTu::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| 15. QUẢN LÝ LỆNH GIAO HÀNG (D/O)
|--------------------------------------------------------------------------
*/

Route::get('/lenh-giao-hang', [QuanLyLenhGiaoHang::class, 'index']);
Route::post('/lenh-giao-hang/save', [QuanLyLenhGiaoHang::class, 'store']);
Route::post('/lenh-giao-hang/delete', [QuanLyLenhGiaoHang::class, 'destroy']);
Route::get('/lenh-giao-hang/export-pdf', [QuanLyLenhGiaoHang::class, 'exportPdf']);

/*
|--------------------------------------------------------------------------
| 16 . API QUẢN LÝ VẬN ĐƠN
|--------------------------------------------------------------------------
*/
Route::get('/van-don', [QuanLyVanDon::class, 'index']);
Route::get('/van-don/references', [QuanLyVanDon::class, 'getReferences']);
Route::post('/van-don/save', [QuanLyVanDon::class, 'save']);
Route::post('/van-don/delete', [QuanLyVanDon::class, 'delete']);
Route::get('/van-don/export-pdf/{id}', [App\Http\Controllers\QuanLyVanDon::class, 'exportPdf']);


/*
|--------------------------------------------------------------------------
| 17. API QUẢN LÝ TỜ KHAI HẢI QUAN
|--------------------------------------------------------------------------
*/
Route::get('/to-khai-hai-quan', [QuanLyToKhaiHaiQuan::class, 'index']);
Route::get('/to-khai-hai-quan/references', [QuanLyToKhaiHaiQuan::class, 'getReferences']);
Route::post('/to-khai-hai-quan/save', [QuanLyToKhaiHaiQuan::class, 'save']);
Route::post('/to-khai-hai-quan/delete', [QuanLyToKhaiHaiQuan::class, 'delete']);

/*
|--------------------------------------------------------------------------
| 18. API QUẢN LÝ THÔNG TIN LƯU BÃI
|--------------------------------------------------------------------------
*/
Route::get('/luu-bai', [QuanLyLuuBai::class, 'index']);
Route::get('/luu-bai/references', [QuanLyLuuBai::class, 'getReferences']);
Route::post('/luu-bai/save', [QuanLyLuuBai::class, 'save']);
Route::post('/luu-bai/delete', [QuanLyLuuBai::class, 'delete']);

/*
|--------------------------------------------------------------------------
| 19. API QUẢN LÝ BÁO CÁO THỐNG KÊ
|--------------------------------------------------------------------------
*/
Route::get('/bao-cao/van-chuyen', [BaoCaoThongKe::class, 'baoCaoVanChuyen']);
Route::get('/bao-cao/san-luong', [BaoCaoThongKe::class, 'baoCaoSanLuong']);
Route::get('/bao-cao/booking', [BaoCaoThongKe::class, 'baoCaoBooking']);
Route::get('/bao-cao/chi-phi-ton-dong', [BaoCaoThongKe::class, 'baoCaoChiPhiTonDong']);
Route::get('/bao-cao/cuoc-vo', [BaoCaoThongKe::class, 'baoCaoCuocVo']);
Route::get('/bao-cao/canh-bao-luu-bai', [BaoCaoThongKe::class, 'baoCaoCanhBaoLuuBai']);

// 20.API DÀNH RIÊNG CHO QUẢN LÝ TRẠNG THÁI THANH TOÁN
Route::get('/trang-thai-thanh-toan', [QuanLyTrangThaiThanhToan::class, 'index']);
Route::post('/trang-thai-thanh-toan/update-status', [QuanLyTrangThaiThanhToan::class, 'updateStatus']);