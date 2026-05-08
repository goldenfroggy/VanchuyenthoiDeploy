<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ocean Bill of Lading</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
        }

        .bl-wrapper {
            position: relative;
        }

        .logo {
            position: absolute;
            top: -15px;
            left: 0;
        }

        .bl-no {
            position: absolute;
            top: 0;
            right: 0;
            font-weight: bold;
            font-size: 14px;
        }

        .container {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            margin-top: 55px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }

        /* Loại bỏ khung dọc hai bên */
        td:first-child { border-left: none; }
        td:last-child { border-right: none; }

        .no-border-v td {
            border-left: none !important;
            border-right: none !important;
        }

        .no-border {
            border: none !important;
        }

        .header {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }

        .sub-header {
            text-align: center;
            font-size: 11px;
        }

        .label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .content {
            font-size: 10px;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .goods-box {
            height: 260px;
        }

        .as-agent {
            font-size: 9px;
            padding-top: 5px;
        }

        .watermark {
            position: fixed;
            top: 35%;
            left: 0;
            width: 100%;
            text-align: center;
            opacity: 0.08;
            font-size: 80px;
            font-weight: bold;
            color: #000;
            transform: rotate(-35deg);
            z-index: -1000;
            line-height: 1.1;
        }
    </style>
</head>

<body>

    <div class="watermark">
        COPY<br>
        NON-NEGOTIABLE
    </div>

@php
    $tongKhoiLuong = $chiTietHangHoa->sum('trong_luong');
    $tongTheTich = $chiTietHangHoa->sum('the_tich');
    $tongSoKien = $chiTietHangHoa->sum('so_kien');
@endphp

<div class="bl-wrapper">

    <!-- LOGO -->
    <div class="logo">
        <img src="{{ resource_path('views/pdfs/slt_logo.jpg') }}" style="height: 60px;">
    </div>

    <!-- B/L NO -->
    <div class="bl-no">
        B/L No: {{ $vanDon->so_van_don }}
    </div>

    <div class="container">
        <table>
            <tr>
                <td colspan="2" width="50%">
                    <span class="label">Shipper</span>
                    <div class="content">
                        {{ $vanDon->ten_nguoi_gui_hang }}<br>
                        {{ $vanDon->dia_chi_nguoi_gui }}
                    </div>
                </td>

                <td colspan="2" rowspan="3" class="header" width="50%">
                    OCEAN BILL OF LADING
                    <div class="sub-header">(COMBINED TRANSPORT / PORT TO PORT)</div>
                    <div class="sub-header">COPY NON-NEGOTIABLE</div>
                    <div style="margin-top: 15px; text-align: left; font-weight: normal; font-size: 8px;">
                        <span class="label">For delivery please apply to</span>
                        <div class="content">
                            SINCERE LOGISTICS AND TRADING CO., LTD<br>
                            NO.35 BINH KIEU 1, DONG HAI, HAIPHONG, VIET NAM
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <span class="label">Consignee</span>
                    <div class="content">
                        {{ $vanDon->ten_nguoi_nhan_hang }}<br>
                        {{ $vanDon->dia_chi_nguoi_nhan }}
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <span class="label">Notify Party</span>
                    <div class="content">
                        @if($vanDon->ma_ben_duoc_thong_bao == $vanDon->ma_nguoi_nhan_hang)
                            SAME AS CONSIGNEE
                        @else
                            {{ $vanDon->ten_ben_duoc_thong_bao }}<br>
                            {{ $vanDon->dia_chi_ben_thong_bao }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td colspan="3" width="50%">
                    <span class="label">Vessel & Voyage</span>
                    <div class="content">{{ $vanDon->ten_tau ?? '' }}</div>
                </td>
                <td colspan="3" width="50%">
                    <span class="label">Port of Loading</span>
                    <div class="content">{{ $vanDon->ten_cang_di }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2" width="33.33%">
                    <span class="label">Port of Discharge</span>
                    <div class="content">{{ $vanDon->ten_cang_den }}</div>
                </td>
                <td colspan="2" width="33.33%">
                    <span class="label">Place of Delivery</span>
                    <div class="content">{{ $vanDon->ten_cang_den }}</div>
                </td>
                <td colspan="2" width="33.34%">
                    <span class="label">Final Destination</span>
                    <div class="content">{{ $vanDon->ten_cang_den }}</div>
                </td>
            </tr>
        </table>

        <!-- GOODS TABLE -->
        <table style="border-bottom: none;">
            <tr class="text-center">
                <td width="25%">Container No. Seal No.</td>
                <td width="55%">Description of Packages & Goods</td>
                <td width="20%">Gross Weight (kgs)/ Measurement CBM (M³)</td>
            </tr>

            <tr class="goods-box no-border-v">
                <td style="border-top: none; border-bottom: none;">
                    {{ $vanDon->so_cont }} / {{ $vanDon->so_chi }}
                </td>

                <td style="border-top: none; border-bottom: none;">
                    SHIPPER'S LOAD COUNT & SEAL S.T.C.<br>
                    {{ $tongSoKien }} PACKAGES<br>
                    ===========<br>
                    @foreach($chiTietHangHoa as $item)
                        {{ $item->ten_hang }}<br>
                        HS CODE: {{ $item->hs_code ?? 'N/A' }}<br>
                    @endforeach
                </td>

                <td class="text-center" style="border-top: none; border-bottom: none;">
                    {{ number_format($tongKhoiLuong, 3) }} KGS<br>
                    {{ number_format($tongTheTich, 3) }} CBM
                </td>
            </tr>
        </table>

        <!-- FOOTER -->
        @php
            $dkStr = strtolower($vanDon->dieu_kien_cuoc);
        @endphp
        <table>
            <tr>
                <td colspan="4" style="border-top: none;">
                    <div class="content">{{ $vanDon->dieu_kien_cuoc }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <span class="label">Total Containers</span>
                    <div class="content">1 CONTAINER</div>
                </td>
            </tr>
            <tr>
                <td width="25%">
                    <span class="label">Freight and Charges</span>
                    <div class="content">FREIGHT AS ARRANGED</div>
                </td>
                <td width="25%">
                    <span class="label">Rate</span>
                </td>
                <td width="25%">
                    <span class="label">Prepaid</span>
                </td>
                <td width="25%">
                    <span class="label">Collect</span>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 0;">
                    <table style="width: 100%; border-collapse: collapse; border: none;">
                        <tr>
                            <td width="33.33%" style="border: none; border-right: 1px solid #000; height: 35px;">
                                <span class="label">Ex. Rate</span>
                            </td>
                            <td width="33.33%" style="border: none; border-right: 1px solid #000;">
                                <span class="label">Prepaid at</span>
                            </td>
                            <td width="33.34%" style="border: none;">
                                <span class="label">Payable at</span>
                                <div class="content">
                                    @if(str_contains($dkStr, 'collect')) DESTINATION
                                    @elseif(str_contains($dkStr, 'prepaid')) ORIGIN
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td colspan="2">
                    <span class="label">Place & Date of Issue</span>
                    <div class="content">
                        HAIPHONG, {{ $vanDon->ngay_phat_hanh_formatted }}
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <!-- Cột bên trái dưới cùng để trống -->
                </td>
                <td colspan="2">
                    For and on behalf of<br>
                    <span style="font-weight: bold;">SINCERE LOGISTICS AND TRADING CO., LTD</span>
                    <br><br><br><br>
                    <div style="text-align: right; padding-right: 40px; font-weight: bold; font-style: italic;">
                        Authorized Signature(s)
                    </div>
                    <div style="text-align: left; font-size: 9px; padding-top: 5px; margin-top: 10px; font-weight: normal; font-style: normal; border-top: 1px solid #000;">
                        As agent for the Carrier
                    </div>
                </td>
            </tr>
        </table>

    </div>
</div>

</body>
</html>