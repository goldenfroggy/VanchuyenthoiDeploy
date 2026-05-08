<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuanLyBooking extends Controller
{
    public function index()
    {
        try {
            $bookings = DB::table('booking')
                ->leftJoin('cang_bien as cang_di', 'booking.ma_cang_di', '=', 'cang_di.ma_cang')
                ->leftJoin('cang_bien as cang_den', 'booking.ma_cang_den', '=', 'cang_den.ma_cang')
                ->leftJoin('hang_tau', 'booking.ma_hang_tau', '=', 'hang_tau.ma_hang_tau')
                ->leftJoin('tai_khoan', 'booking.nguoi_sua_cuoi', '=', 'tai_khoan.ma_tai_khoan')
                ->select(
                    'booking.*', 
                    'cang_di.ten_cang as ten_cang_di', 
                    'cang_den.ten_cang as ten_cang_den', 
                    'hang_tau.ten_hang_tau', 
                    'tai_khoan.ho_ten as nguoi_sua_doi'
                )
                ->where('booking.thoi_gian_xoa', '<', '2000-01-01')
                ->orderBy('booking.ma_booking', 'desc')
                ->get();
            
            return response()->json(["success" => true, "data" => $bookings]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi DB: " . $e->getMessage()]);
        }
    }

    public function getReferences()
    {
        try {
            $cangBien = DB::table('cang_bien')->where('thoi_gian_xoa', '<', '2000-01-01')->get(['ma_cang', 'ten_cang']);
            $hangTau = DB::table('hang_tau')->where('thoi_gian_xoa', '<', '2000-01-01')->get(['ma_hang_tau', 'ten_hang_tau']);
            
            return response()->json([
                "success" => true, 
                "cang_bien" => $cangBien, 
                "hang_tau" => $hangTau
            ]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi tải danh mục."]);
        }
    }

    public function save(Request $request)
    {
        try {
            $data = [
                'so_booking' => $request->input('so_booking'),
                'ten_con_tau' => $request->input('ten_con_tau'),
                'so_chuyen' => $request->input('so_chuyen'),
                'etd' => $request->input('etd'),
                'eta' => $request->input('eta'),
                'gio_cat_mang' => $request->input('gio_cat_mang'),
                'ma_cang_di' => $request->input('ma_cang_di'),
                'ma_cang_den' => $request->input('ma_cang_den'),
                'ma_hang_tau' => $request->input('ma_hang_tau'),
                'nguoi_sua_cuoi' => $request->input('nguoi_sua_cuoi')
            ];

            if ($request->has('ma_booking') && $request->input('ma_booking')) {
                DB::table('booking')->where('ma_booking', $request->input('ma_booking'))->update($data);
                $msg = "Cập nhật Booking thành công!";
            } else {
                DB::table('booking')->insert($data);
                $msg = "Tạo Booking mới thành công!";
            }
            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        try {
            DB::table('booking')
                ->where('ma_booking', $request->input('ma_booking'))
                ->update([
                    'thoi_gian_xoa' => now(),
                    'nguoi_sua_cuoi' => $request->input('nguoi_sua_cuoi')
                ]);
            return response()->json(['success' => true, 'message' => 'Đã hủy Booking thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
}
