<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class QuanLyDangNhap extends Controller
{
    /**
     * API Đăng nhập
     */
    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('mat_khau');

        try {
            $user = DB::table('tai_khoan')
                ->select('tai_khoan.*')
                ->where('tai_khoan.email', $email)
                ->where('tai_khoan.thoi_gian_xoa', '<', '2000-01-01')
                ->first();

            if ($user && Hash::check($password, $user->mat_khau)) {
                if ($user->trang_thai === 'Tạm khóa') {
                    return response()->json(['success' => false, 'message' => 'Tài khoản của bạn đã bị khóa!']);
                }


                $list_quyen = DB::table('chi_tiet_quyen')
                    ->join('quyen', 'chi_tiet_quyen.ma_quyen', '=', 'quyen.ma_quyen')
                    ->where('chi_tiet_quyen.ma_tai_khoan', $user->ma_tai_khoan)
                    ->pluck('quyen.ten_quyen')
                    ->toArray();

                return response()->json([
                    'success' => true,
                    'message' => 'Đăng nhập thành công! Chào mừng ' . $user->ho_ten,
                    'user' => [
                        'ma_tai_khoan' => $user->ma_tai_khoan,
                        'ho_ten' => $user->ho_ten,
                        'chuc_vu' => implode(', ', $list_quyen),
                        'email' => $user->email
                    ]
                ]);
            }
            return response()->json(['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    /**
     * Lấy thông tin cá nhân
     */
    public function userInfo(Request $request)
    {
        $ma_tai_khoan = $request->query('ma_tai_khoan');
        if (!$ma_tai_khoan) return response()->json(["success" => false, "message" => "Thiếu mã tài khoản"]);

        try {
            $user = DB::table('tai_khoan')
                ->where('tai_khoan.ma_tai_khoan', $ma_tai_khoan)
                ->where('tai_khoan.thoi_gian_xoa', '<', '2000-01-01') 
                ->select('tai_khoan.*') 
                ->first();
                
            if ($user) {
                // Lấy danh sách chi tiết các mã quyền và tên quyền
                $ds_quyen = DB::table('chi_tiet_quyen')
                    ->join('quyen', 'chi_tiet_quyen.ma_quyen', '=', 'quyen.ma_quyen')
                    ->where('chi_tiet_quyen.ma_tai_khoan', $ma_tai_khoan)
                    ->select('quyen.ma_quyen', 'quyen.ten_quyen')
                    ->get();
                
                $user->ds_quyen = $ds_quyen;
                return response()->json(["success" => true, "data" => $user]);
            }
            return response()->json(["success" => false, "message" => "Không tìm thấy user."]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi DB: " . $e->getMessage()], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $ma_tai_khoan = $request->input('ma_tai_khoan');
        if (empty($ma_tai_khoan)) return response()->json(["success" => false, "message" => "Thiếu mã tài khoản!"]);

        try {
            DB::table('tai_khoan')->where('ma_tai_khoan', $ma_tai_khoan)->update([
                'ho_ten' => $request->input('ho_ten'),
                'email' => $request->input('email'),
                'ngay_sinh' => $request->input('ngay_sinh')
            ]);
            return response()->json(["success" => true, "message" => "Cập nhật thành công!"]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi DB: " . $e->getMessage()], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $ma_tai_khoan = $request->input('ma_tai_khoan');
        $current_pass = $request->input('current_pass');
        $new_pass = $request->input('new_pass');

        if (empty($ma_tai_khoan) || empty($current_pass) || empty($new_pass)) {
            return response()->json(["success" => false, "message" => "Vui lòng nhập đầy đủ thông tin."]);
        }

        try {
            $user = DB::table('tai_khoan')->where('ma_tai_khoan', $ma_tai_khoan)->first();
            if (!$user || !Hash::check($current_pass, $user->mat_khau)) {
                return response()->json(["success" => false, "message" => "Mật khẩu hiện tại không chính xác!"]);
            }

            DB::table('tai_khoan')->where('ma_tai_khoan', $ma_tai_khoan)->update(['mat_khau' => Hash::make($new_pass)]);
            return response()->json(["success" => true, "message" => "Đổi mật khẩu thành công!"]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
        }
    }
}