<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuanLyLuuBai extends Controller
{
    public function index()
    {
        try {
            $data = DB::table('thong_tin_luu_bai')
                ->leftJoin('lo_hang', 'thong_tin_luu_bai.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('van_don', 'lo_hang.ma_lo_hang', '=', 'van_don.ma_lo_hang')
                ->leftJoin('tai_khoan', 'thong_tin_luu_bai.nguoi_sua_cuoi', '=', 'tai_khoan.ma_tai_khoan')
                ->select(
                    'thong_tin_luu_bai.*',
                    'lo_hang.ten_lo_hang',
                    'van_don.so_cont',
                    'van_don.so_van_don',
                    'tai_khoan.ho_ten as ten_nguoi_sua'
                )
                ->orderBy('thong_tin_luu_bai.ma_luu_bai', 'desc')
                ->get();

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    public function getReferences(Request $request)
    {
        try {
            $ma_luu_bai = $request->query('ma_luu_bai');
            
            // Lấy danh sách lô hàng chưa có thông tin lưu bãi (hoặc chính lô hàng đang sửa)
            $loHang = DB::table('lo_hang')
                ->where('thoi_gian_xoa', '<', '2000-01-01')
                ->whereNotIn('ma_lo_hang', function($q) use ($ma_luu_bai) {
                    $q->select('ma_lo_hang')->from('thong_tin_luu_bai');
                    if ($ma_luu_bai) $q->where('ma_luu_bai', '!=', $ma_luu_bai);
                })
                ->get(['ma_lo_hang', 'ten_lo_hang']);

            return response()->json(['success' => true, 'lo_hang' => $loHang]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        $ma_luu_bai = $request->input('ma_luu_bai');
        $data = [
            'trang_thai_luu_bai'    => $request->input('trang_thai_luu_bai'),
            'ngay_bat_dau_luu_bai'  => $request->input('ngay_bat_dau_luu_bai'),
            'ngay_luu_bai_mien_phi' => $request->input('ngay_luu_bai_mien_phi'),
            'cuoc_vo'               => $request->input('cuoc_vo'),
            'ma_lo_hang'            => $request->input('ma_lo_hang'),
            'nguoi_sua_cuoi'        => $request->input('nguoi_sua_cuoi'),
        ];

        try {
            if ($ma_luu_bai) {
                DB::table('thong_tin_luu_bai')->where('ma_luu_bai', $ma_luu_bai)->update($data);
                $message = "Cập nhật thông tin lưu bãi thành công!";
            } else {
                DB::table('thong_tin_luu_bai')->insert($data);
                $message = "Thêm mới thông tin lưu bãi thành công!";
            }
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        try {
            DB::table('thong_tin_luu_bai')->where('ma_luu_bai', $request->input('ma_luu_bai'))->delete();
            return response()->json(['success' => true, 'message' => 'Đã xóa thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}