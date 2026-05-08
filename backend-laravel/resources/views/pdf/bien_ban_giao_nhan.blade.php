<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Proof of Delivery</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; line-height: 1.5; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; }
        .logo { width: 120px; font-weight: bold; font-size: 24px; color: #27ae60; }
        .company-info { padding-left: 20px; }
        
        .title { text-align: center; margin: 25px 0; }
        .title h1 { margin: 0; font-size: 22px; font-weight: bold; }
        .title h3 { margin: 0; font-size: 16px; font-weight: normal; }

        .info-table { width: 100%; margin-bottom: 20px; border: 1.5px solid #000; }
        .info-table td { padding: 10px; border: 1px solid #000; vertical-align: top; height: 35px; }
        .info-table strong { font-size: 14px; }
        .label-sub { font-weight: normal; font-style: italic; font-size: 11px; display: block; margin-top: 4px; color: #333; }

        .billing-info { margin-top: 20px; line-height: 1.8; }
        .note-box { border: 1px solid #000; padding: 10px; margin-top: 20px; text-align: justify; background-color: #f9f9f9;}
        
        .footer-table { margin-top: 40px; text-align: center; }
        .footer-table td { vertical-align: top; width: 50%; }
    </style>
</head>
<body>

    <table class="header-table" style="border-bottom: 2px solid #000; padding-bottom: 10px;">
        <tr>
            <td width="20%" class="logo">SLT</td>
            <td width="80%" class="company-info">
                <strong>SINCERE LOGISTICS CO., LTD</strong><br>
                Add: Số 35 Bình Kiều 1, phường Đông Hải, Thành phố Hải Phòng, Việt Nam<br>
                Tel: +84-904 315379<br>
                E-Mail: mng@sincerelogistics.com
            </td>
        </tr>
    </table>

    <div class="title">
        <h1>PROOF OF DELIVERY</h1>
        <h3>(BIÊN BẢN GIAO NHẬN HÀNG)</h3>
    </div>

    <table class="info-table">
        <tr>
            <td width="50%">
                <strong>Date:</strong> <span style="font-size: 14px; padding-left: 5px;">{{ $data->ngay_phat_hanh ? date('d/m/Y', strtotime($data->ngay_phat_hanh)) : '' }}</span>
                <span class="label-sub">(Ngày giao hàng)</span>
            </td>
            <td width="50%">
                <strong>Time:</strong> <span style="font-size: 14px; padding-left: 5px;">{{ $data->ngay_phat_hanh ? date('H:i', strtotime($data->ngay_phat_hanh)) : '' }}</span>
                <span class="label-sub">(Giờ)</span>
            </td>
        </tr>
        <tr>
            <td>
                <strong>B/L No.:</strong> <span style="font-size: 14px; padding-left: 5px;">{{ $data->so_van_don ?? '' }}</span>
                <span class="label-sub">(Vận tải đơn số)</span>
            </td>
            <td>
                <strong>Consignee:</strong> <span style="font-size: 14px; padding-left: 5px;">{{ $data->ten_khach_hang ?? '' }}</span>
                <span class="label-sub">(Người nhận hàng)</span>
            </td>
        </tr>
        <tr>
            <td>
                <strong>Place of delivery:</strong><br>
                <span style="font-size: 14px; display: block; margin-top: 5px;">{{ $data->dia_chi ?? '' }}</span>
                <span class="label-sub">(Địa chỉ giao hàng)</span>
            </td>
            <td>
                <strong>Truck ID:</strong> <span style="font-size: 14px; padding-left: 5px; font-weight: bold;">{{ $data->ten_hang_van_tai ?? '' }}</span>
                <span class="label-sub">(Số xe / Đơn vị vận tải)</span>
            </td>
        </tr>
        <tr>
            <td>
                <strong>PIC:</strong> <span style="font-size: 14px; padding-left: 5px;">{{ $data->ten_khach_hang ?? '' }}</span>
                <span class="label-sub">(Người liên hệ)</span>
                <span style="font-size: 11px; font-style: italic; display: block; margin-top: 5px;">* Lái xe liên hệ dự báo thời gian giao hàng cho khách hàng trước khi đi.</span>
            </td>
            <td>
                <strong>Liên hệ:</strong> <span style="font-size: 14px; padding-left: 5px;">{{ $data->so_dien_thoai ?? '' }}</span>
                <span class="label-sub">(Điện thoại)</span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>Description of Goods:</strong> <span style="font-size: 14px; padding-left: 5px;">{{ $data->ten_lo_hang ?? '' }}</span>
                <span class="label-sub">(Tên hàng)</span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>Quantity/G.W:</strong> <span style="font-size: 14px; padding-left: 5px;">
                {{ $data->tong_kien ? $data->tong_kien . ' PKGS' : '' }} 
                {{ $data->so_cont ? ' / ' . $data->so_cont : '' }} 
                {{ $data->so_chi ? ' / ' . $data->so_chi : '' }} 
                {{ $data->tong_trong_luong ? ' / ' . number_format($data->tong_trong_luong, 2) . ' KGS' : '' }}
                </span>
                <span class="label-sub">(Số lượng / Loại Cont / Số Chì / Trọng lượng)</span>
            </td>
        </tr>
    </table>

    <div class="billing-info">
        <strong>Thông tin viết hóa đơn:</strong><br>
        Tên công ty: <strong>{{ $data->ten_khach_hang ?? '' }}</strong><br>
        Địa chỉ: <strong>{{ $data->dia_chi ?? '' }}</strong><br>
        MST: <strong>{{ $data->ma_so_thue ?? '' }}</strong><br>
        <span style="font-style: italic; font-size: 12px;">(Lưu ý: Lái xe chú ý nhắc nhở nhân viên xuất hóa đơn cập nhật lại địa chỉ mới nếu có thay đổi)</span>
    </div>

    <div class="note-box">
        <strong>Chú ý:</strong> Trong quá trình lái xe lấy cont và trả hàng nếu phát hiện vấn đề về hỏng hóc cont lái xe phải có xác nhận của cảng hoặc chủ hàng về tình trạng hỏng hóc, nếu về bãi trả vỏ mà phát sinh chi phí sửa chữa (lỗi không có trên biên bản bàn giao). <strong>Thì lái xe chịu hoàn toàn mọi chi phí phát sinh.</strong>
    </div>

    <table class="footer-table">
        <tr>
            <td>
                <strong>Delivered and signed by</strong><br>
                <span style="font-style: italic;">(Lái xe/ Người giao hàng)</span>
            </td>
            <td>
                <strong>Received and signed by</strong><br>
                <span style="font-style: italic;">(Người nhận hàng)</span>
            </td>
        </tr>
        <tr>
            <td style="height: 120px;"></td>
            <td></td>
        </tr>
    </table>

</body>
</html>