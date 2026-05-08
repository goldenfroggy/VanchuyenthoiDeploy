<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class QuanLyTaiKhoan extends Controller
{
    public function index()
    {
        try {
            $accounts = DB::table('tai_khoan')
                ->leftJoin('chi_tiet_quyen', 'tai_khoan.ma_tai_khoan', '=', 'chi_tiet_quyen.ma_tai_khoan')
                ->leftJoin('quyen', 'chi_tiet_quyen.ma_quyen', '=', 'quyen.ma_quyen')
                ->select(
                    'tai_khoan.ma_tai_khoan', 
                    'tai_khoan.ho_ten', 
                    'tai_khoan.email', 
                    'tai_khoan.trang_thai', 
                    'tai_khoan.ngay_sinh', 
                    DB::raw('GROUP_CONCAT(quyen.ten_quyen SEPARATOR ", ") as ten_quyen'),
                    DB::raw('GROUP_CONCAT(quyen.ma_quyen) as ds_ma_quyen')
                )
                ->where('tai_khoan.thoi_gian_xoa', '<', '2000-01-01')
                ->groupBy('tai_khoan.ma_tai_khoan', 'tai_khoan.ho_ten', 'tai_khoan.email', 'tai_khoan.trang_thai', 'tai_khoan.ngay_sinh')
                ->orderBy('tai_khoan.ma_tai_khoan', 'asc')
                ->get();
            return response()->json(["success" => true, "data" => $accounts]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        $email = $request->input('email');
        $ma_tai_khoan = $request->input('ma_tai_khoan');

        if (!$request->input('ho_ten') || !$email) {
            return response()->json(['success' => false, 'message' => 'Vui lòng nhập đầy đủ Họ tên và Email.']);
        }

        try {
            DB::beginTransaction();

            $emailCheck = DB::table('tai_khoan')->where('email', $email)->where('thoi_gian_xoa', '<', '2000-01-01');
            if ($ma_tai_khoan) $emailCheck->where('ma_tai_khoan', '!=', $ma_tai_khoan);
            if ($emailCheck->exists()) return response()->json(['success' => false, 'message' => 'Email này đã tồn tại!']);

            $dataToSave = [
                'ho_ten' => $request->input('ho_ten'),
                'email' => $email,
                'ngay_sinh' => $request->input('ngay_sinh'),
            ];

            if ($request->has('trang_thai')) $dataToSave['trang_thai'] = $request->input('trang_thai');

            if ($ma_tai_khoan) {
                if ($request->filled('mat_khau')) $dataToSave['mat_khau'] = Hash::make($request->input('mat_khau'));
                DB::table('tai_khoan')->where('ma_tai_khoan', $ma_tai_khoan)->update($dataToSave);
                $message = "Cập nhật thành công!";
            } else {
                $dataToSave['mat_khau'] = Hash::make($request->input('mat_khau'));
                $ma_tai_khoan = DB::table('tai_khoan')->insertGetId($dataToSave);
                $message = "Tạo mới thành công!";
            }

            // Xử lý lưu nhiều quyền vào bảng chi_tiet_quyen
            $quyen_input = $request->input('ma_quyen'); // Có thể là mảng hoặc một giá trị đơn lẻ
            if ($quyen_input !== null) {
                $ds_quyen = is_array($quyen_input) ? $quyen_input : [$quyen_input];
                
                // Xóa các quyền cũ
                DB::table('chi_tiet_quyen')->where('ma_tai_khoan', $ma_tai_khoan)->delete();
                
                // Chèn các quyền mới
                $data_quyen = array_map(function($id_quyen) use ($ma_tai_khoan) {
                    return ['ma_tai_khoan' => $ma_tai_khoan, 'ma_quyen' => $id_quyen];
                }, $ds_quyen);
                
                DB::table('chi_tiet_quyen')->insert($data_quyen);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        $ma_tai_khoan = $request->input('ma_tai_khoan');
        if (empty($ma_tai_khoan)) return response()->json(['success' => false, 'message' => 'Thiếu mã tài khoản.']);
        if ($ma_tai_khoan == 1) return response()->json(['success' => false, 'message' => 'Không được phép xóa Admin!']);

        try {
            DB::table('tai_khoan')
                ->where('ma_tai_khoan', $ma_tai_khoan)
                ->update(['thoi_gian_xoa' => now()]);
            return response()->json(['success' => true, 'message' => 'Đã đưa vào thùng rác thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
}