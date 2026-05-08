<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuanLyToKhaiHaiQuan extends Controller
{
    public function index()
    {
        try {
            $data = DB::table('to_khai_hai_quan')
                ->leftJoin('lo_hang', 'to_khai_hai_quan.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('tai_khoan', 'to_khai_hai_quan.nguoi_sua_cuoi', '=', 'tai_khoan.ma_tai_khoan')
                ->select(
                    'to_khai_hai_quan.*',
                    'lo_hang.ten_lo_hang',
                    'tai_khoan.ho_ten as ten_nguoi_sua'
                )
                ->orderBy('to_khai_hai_quan.ma_to_khai_hai_quan', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy dữ liệu tờ khai: ' . $e->getMessage()
            ]);
        }
    }

    public function getReferences(Request $request)
    {
        try {
            $ma_to_khai = $request->query('ma_to_khai_hai_quan');

            // Lấy danh sách lô hàng chưa bị xóa
            // Chỉ lấy các lô hàng chưa được liên kết với tờ khai nào khác
            $loHang = DB::table('lo_hang')
                ->where('thoi_gian_xoa', '<', '2000-01-01')
                ->whereNotIn('ma_lo_hang', function($q) use ($ma_to_khai) {
                    $q->select('ma_lo_hang')
                      ->from('to_khai_hai_quan')
                      ->whereNotNull('ma_lo_hang');
                    
                    if ($ma_to_khai) {
                        $q->where('ma_to_khai_hai_quan', '!=', $ma_to_khai);
                    }
                })
                ->get(['ma_lo_hang', 'ten_lo_hang']);

            return response()->json([
                'success' => true,
                'lo_hang' => $loHang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy danh mục tham chiếu: ' . $e->getMessage()
            ]);
        }
    }

    public function save(Request $request)
    {
        $ma_to_khai = $request->input('ma_to_khai_hai_quan');
        $ma_lo_hang = $request->input('ma_lo_hang');

        if (empty($ma_lo_hang)) {
            return response()->json([
                'success' => false,
                'message' => 'Lô hàng liên kết là thông tin bắt buộc.'
            ]);
        }
        
        $data = [
            'ngay_thong_quan'    => $request->input('ngay_thong_quan'),
            'phan_luong'         => $request->input('phan_luong'),
            'ket_qua_thong_quan' => $request->input('ket_qua_thong_quan'),
            'ma_lo_hang'         => $request->input('ma_lo_hang'),
            'nguoi_sua_cuoi'     => $request->input('nguoi_sua_cuoi'),
        ];

        try {
            if ($ma_to_khai) {
                DB::table('to_khai_hai_quan')
                    ->where('ma_to_khai_hai_quan', $ma_to_khai)
                    ->update($data);
                $message = "Cập nhật thông tin tờ khai hải quan thành công!";
            } else {
                DB::table('to_khai_hai_quan')->insert($data);
                $message = "Thêm mới tờ khai hải quan thành công!";
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xử lý dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    public function delete(Request $request)
    {
        $id = $request->input('ma_to_khai_hai_quan');
        try {
            DB::table('to_khai_hai_quan')->where('ma_to_khai_hai_quan', $id)->delete();
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa tờ khai hải quan thành công!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xóa dữ liệu: ' . $e->getMessage()
            ]);
        }
    }
}