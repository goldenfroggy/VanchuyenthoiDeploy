<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Arrival Notice</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; line-height: 1.4; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; }
        .logo { width: 120px; font-weight: bold; font-size: 24px; color: #27ae60; }
        .company-info { padding-left: 20px; }
        .title { text-align: center; margin: 15px 0; position: relative;}
        .title h1 { margin: 0; font-size: 22px; text-transform: uppercase; }
        .title h3 { margin: 0; font-size: 14px; font-weight: normal; font-style: italic; }
        .lan-in { position: absolute; right: 0; top: 5px; font-weight: bold; }
        
        .info-table { margin-bottom: 15px; }
        .info-table td { padding: 4px 0; vertical-align: top; }
        
        .goods-table { width: 100%; border: 1px solid #000; margin-top: 10px; margin-bottom: 5px;}
        .goods-table th, .goods-table td { border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; }
        .goods-table th { background-color: #f9f9f9; font-weight: normal;}
        
        .notes-section { margin-top: 30px; line-height: 1.6; }
        .text-red { color: red; }
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
        <h1>THÔNG BÁO HÀNG ĐẾN</h1>
        <h3>(ARRIVAL NOTICE)</h3>
        <div class="lan-in">Lần: 1</div>
    </div>

    @php
        $khach_ten = $data->notify_name ?? $data->consignee_name ?? '..................................';
        $khach_dia_chi = $data->notify_address ?? $data->consignee_address ?? '';
        $khach_tel = $data->notify_tel ?? $data->consignee_tel ?? 'N/A';
        $khach_fax = $data->notify_fax ?? $data->consignee_fax ?? 'N/A';
    @endphp

    <table class="info-table">
        <tr>
            <td width="15%">Kính gửi (To):</td>
            <td width="85%">
                <strong style="text-transform: uppercase; font-size: 14px;">{{ $khach_ten }}</strong><br>
                <span style="text-transform: uppercase;">{{ $khach_dia_chi }}</span>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>
                <table width="100%">
                    <tr>
                        <td width="50%">Tel: <strong>{{ $khach_tel }}</strong></td>
                        <td width="50%">Fax: <strong>{{ $khach_fax }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="margin-bottom: 8px;">Chúng tôi trân trọng thông báo lô hàng sau đây của Quý công ty (Kindly be informed about your shipment):</div>
    
    <table class="info-table">
        <tr>
            <td width="22%">Trên tàu (On the Vessel):</td>
            <td width="38%"><strong>{{ $data->ten_con_tau ?? 'N/A' }} / {{ $data->so_chuyen ?? '' }}</strong></td>
            <td width="15%"></td>
            <td width="25%"></td>
        </tr>
        <tr>
            <td>Từ (From):</td>
            <td><strong>{{ $data->pol_name ?? 'N/A' }}</strong></td>
            <td>Tới (To):</td>
            <td style="font-weight: bold; text-transform: uppercase;">
                {{ $data->pod_name ?? 'CHƯA CẬP NHẬT KHO/CẢNG' }}
            </td>
        </tr>
        <tr>
            <td>Dự kiến đến ngày (ETA):</td>
            <td class="text-red"><strong>{{ $data->eta ? date('d/m/Y', strtotime($data->eta)) : 'N/A' }}</strong></td>
            <td>HBL:</td>
            <td><strong>{{ $data->so_van_don ?? 'N/A' }}</strong></td>
        </tr>
        <tr>
            <td>Địa chỉ giao hàng:</td>
            <td style="font-weight: bold;">
                {{ $data->pod_address ?? $data->pod_name ?? 'CHƯA CẬP NHẬT ĐỊA CHỈ' }}
            </td>
            <td>MBL:</td>
            <td><strong>{{ $data->so_van_don_goc ?? 'N/A' }}</strong></td>
        </tr>
    </table>

    <table class="goods-table">
        <thead>
            <tr>
                <th width="25%">Số Cont. / Số chì<br>Cont. No. / Seal No.</th>
                <th width="15%">Số kiện<br>No. of Pkgs</th>
                <th width="35%">Tên hàng</th>
                <th width="12%">Tr. Lượng<br>G.W(KGS)</th>
                <th width="13%">Thể tích<br>Meas (M3)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $data->so_cont ?? 'N/A' }}</strong><br>
                    <strong>{{ $data->so_chi ?? 'N/A' }}</strong>
                </td>
                <td><strong>{{ $data->tong_kien ?? '0' }} PKGS</strong></td>
                <td>
                    <strong>AS PER B/L DETAILS</strong><br><br>
                    {{ $data->ten_lo_hang ?? 'N/A' }}
                </td>
                <td><strong>{{ number_format($data->tong_trong_luong ?? 0, 2) }}</strong></td>
                <td><strong>{{ number_format($data->tong_the_tich ?? 0, 3) }}</strong></td>
            </tr>
            <tr>
                <td style="height: 60px; border-top: none;"></td>
                <td style="border-top: none;"></td>
                <td style="border-top: none;"></td>
                <td style="border-top: none;"></td>
                <td style="border-top: none;"></td>
            </tr>
        </tbody>
    </table>

    <div style="text-align: right; font-style: italic; margin-bottom: 20px;">
        Hải Phòng, ngày {{ date('d/m/Y', strtotime($data->ngay_phat_hanh ?? now())) }}
    </div>

    <div class="notes-section">
        <p>Phí lưu cont / bãi bắt đầu tính từ ngày: ............................................</p>
        
        <p style="margin-top: 30px;">Khi đến lấy D/O, xin quý khách vui lòng mang theo:</p>
        <table width="100%">
            <tr>
                <td width="50%"><strong>* Giấy báo hàng đến</strong></td>
                <td width="50%" class="text-red"><strong>* Vận đơn gốc (nếu có)</strong></td>
            </tr>
            <tr>
                <td colspan="2"><strong>* Giấy giới thiệu của công ty</strong></td>
            </tr>
            <tr>
                <td colspan="2"><strong>* Khách hàng là FORWARDER đối với hàng LCL xin vui lòng gửi HBL cho chúng tôi qua email <a href="mailto:cus@sincerelogistics.com" style="color: blue;">cus@sincerelogistics.com</a></strong></td>
            </tr>
        </table>
    </div>

</body>
</html>