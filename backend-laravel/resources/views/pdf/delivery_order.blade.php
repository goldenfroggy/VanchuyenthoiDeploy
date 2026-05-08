<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Delivery Order</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; line-height: 1.4; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; }
        .logo { width: 120px; font-weight: bold; font-size: 24px; color: #27ae60; }
        .company-info { padding-left: 20px; }
        .title { text-align: center; margin: 20px 0; }
        .title h1 { margin: 0; font-size: 22px; text-transform: uppercase; }
        .title h3 { margin: 0; font-size: 14px; font-weight: normal; }
        .info-table { margin-bottom: 15px; }
        .info-table td { padding: 3px 0; vertical-align: top; }
        
        .goods-table { width: 100%; border: 1px solid #000; margin-top: 15px; }
        .goods-table th, .goods-table td { border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; }
        .goods-table th { background-color: #f2f2f2; }
        
        .footer-table { margin-top: 40px; }
        .footer-table td { vertical-align: top; }
    </style>
</head>
<body>

    <table class="header-table" style="border-bottom: 2px solid #000; padding-bottom: 10px;">
        <tr>
            <td width="20%" class="logo">SLT</td>
            <td width="80%" class="company-info">
                <strong>SINCERE LOGISTICS CO., LTD</strong><br>
                Add: No. 22/32/258 Da Nang, Cau Tre, Ngo Quyen, Hai Phong, Viet Nam<br>
                Tel & Fax: + (84-31) 650 2826<br>
                E-Mail: mng@sincerelogistics.com
            </td>
        </tr>
    </table>

    <div class="title">
        <h1>LỆNH GIAO HÀNG</h1>
        <h3>(DELIVERY ORDER)</h3>
    </div>

    <table class="info-table">
        <tr>
            <td width="30%">Kính gửi (To):</td>
            <td width="70%">
                <strong>{{ $data->pod_name ?? '...' }}</strong>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 15px;">Đề nghị giao hàng cho<br>(Please kindly delivery to):</td>
            <td style="padding-top: 15px;">
                <strong style="text-transform: uppercase; font-size: 14px;">{{ $data->consignee_name ?? 'N/A' }}</strong><br>
                <span>{{ $data->consignee_address ?? '' }}</span>
            </td>
        </tr>
    </table>

    <div style="margin-top: 15px;">Lô hàng thuộc vận đơn số (The shipment under B/L No.):</div>
    <table class="info-table" style="margin-top: 5px;">
        <tr>
            <td width="20%">MBL No:</td>
            <td width="30%"><strong>{{ $data->so_van_don_goc ?? 'N/A' }}</strong></td>
            <td width="20%">HBL No:</td>
            <td width="30%"><strong>{{ $data->so_van_don ?? 'N/A' }}</strong></td>
        </tr>
        <tr>
            <td>Trên tàu (On the Vessel):</td>
            <td><strong>{{ $data->ten_con_tau ?? 'N/A' }} / {{ $data->so_chuyen ?? '' }}</strong></td>
            <td>Đến (To):</td>
            <td><strong>{{ $data->pod_name ?? 'N/A' }}</strong></td>
        </tr>
        <tr>
            <td>Từ (From):</td>
            <td><strong>{{ $data->pol_name ?? 'N/A' }}</strong></td>
            <td>Ngày ETA:</td>
            <td><strong>{{ $data->eta ? date('d/m/Y', strtotime($data->eta)) : 'N/A' }}</strong></td>
        </tr>
    </table>

    <table class="goods-table">
        <thead>
            <tr>
                <th width="25%">Số Cont. / Số chì<br>Cont. No. / Seal No.</th>
                <th width="15%">Số kiện<br>No. of Pkgs</th>
                <th width="35%">Mô tả hàng hóa<br>Description of goods</th>
                <th width="12%">Tr. Lượng<br>G.W(KGS)</th>
                <th width="13%">Thể tích<br>Meas (M3)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ $data->so_cont ?? 'N/A' }}<br>/<br>{{ $data->so_chi ?? 'N/A' }}
                </td>
                <td>{{ $data->tong_kien ?? '0' }} PKGS</td>
                <td>
                    <strong>AS PER B/L DETAILS</strong><br><br>
                    {{ $data->ten_lo_hang ?? 'N/A' }}
                </td>
                <td>{{ number_format($data->tong_trong_luong ?? 0, 2) }}</td>
                <td>{{ number_format($data->tong_the_tich ?? 0, 3) }}</td>
            </tr>
            <tr>
                <td style="height: 100px; border-top: none;"></td>
                <td style="border-top: none;"></td>
                <td style="border-top: none;"></td>
                <td style="border-top: none;"></td>
                <td style="border-top: none;"></td>
            </tr>
        </tbody>
    </table>

    <table class="footer-table">
        <tr>
            <td width="50%">
                <strong>Ký nhận lệnh giao hàng</strong><br>
                (Consignee's Signature)<br>
                Ngày nhận lệnh:.....................
            </td>
            <td width="50%" style="text-align: right;">
                Hải Phòng, ngày {{ date('d', strtotime($data->ngay_phat_hanh ?? now())) }} 
                tháng {{ date('m', strtotime($data->ngay_phat_hanh ?? now())) }} 
                năm {{ date('Y', strtotime($data->ngay_phat_hanh ?? now())) }}
            </td>
        </tr>
    </table>

</body>
</html>