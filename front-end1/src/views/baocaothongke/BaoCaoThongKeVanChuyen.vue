<template>
  <div style="padding: 20px;">
    <h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">BÁO CÁO THỐNG KÊ VẬN CHUYỂN</h2>

    <div class="filter-section" style="display: flex; gap: 15px; margin-bottom: 25px; align-items: flex-end; flex-wrap: wrap;">
      <div>
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Ngày đi (ETD) từ</label>
        <input type="date" v-model="filters.tu_ngay" class="form-control">
      </div>
      <div>
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Ngày đến (ETA) đến</label>
        <input type="date" v-model="filters.den_ngay" class="form-control">
      </div>
      <div>
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Trạng thái</label>
        <select v-model="filters.trang_thai" class="form-control">
          <option value="">-- Tất cả --</option>
          <option value="Mới tạo">Mới tạo</option>
          <option value="Đang chờ xử lý">Đang chờ xử lý</option>
          <option value="Đang vận chuyển">Đang vận chuyển</option>
          <option value="Đã thông quan">Đã thông quan</option>
          <option value="Hoàn tất">Hoàn tất</option>
        </select>
      </div>
      <div style="flex: 1; min-width: 200px;">
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Tìm kiếm</label>
        <input type="text" v-model="filters.tim_kiem" placeholder="Nhập mã lô, booking, tên hàng..." class="form-control">
      </div>
      <div>
        <button @click="fetchData" class="btn btn-primary" style="margin-right: 10px;">🔍 Tìm kiếm</button>
        <button @click="exportExcel" class="btn btn-success">📊 Excel</button>
      </div>
    </div>

    <div class="stats-cards" style="display: flex; gap: 20px; margin-bottom: 25px;">
      <div class="card" style="border-left: 5px solid #3498db;">
        <div class="card-title">Tổng số lô hàng trong kỳ:</div>
        <div class="card-value" style="color: #3498db;">{{ stats.tong_so }}</div>
      </div>
      <div class="card" style="border-left: 5px solid #f39c12;">
        <div class="card-title">Đang vận chuyển:</div>
        <div class="card-value" style="color: #f39c12;">{{ stats.dang_van_chuyen }}</div>
      </div>
      <div class="card" style="border-left: 5px solid #2ecc71;">
        <div class="card-title">Đã hoàn thành:</div>
        <div class="card-value" style="color: #2ecc71;">{{ stats.hoan_thanh }}</div>
      </div>
    </div>

    <div class="table-container" style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #34495e; color: white;">
          <tr>
            <th style="padding: 12px; border-right: 1px solid #2c3e50; text-align: center;">STT</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50;">Mã lô hàng</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50;">Số Booking</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50;">Tên hàng</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50;">Cảng đi ➔ Đến</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50;">Ngày đi (ETD) / Đến (ETA)</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50; text-align: center;">Trạng thái</th>
            <th style="padding: 12px; text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading"><td colspan="8" style="text-align: center; padding: 20px;">Đang tải dữ liệu...</td></tr>
          <tr v-else-if="listData.length === 0"><td colspan="8" style="text-align: center; padding: 20px;">Không có dữ liệu</td></tr>
          
          <tr v-for="(item, index) in listData" :key="item.ma_lo_hang" style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px; border-right: 1px solid #eee; text-align: center;">{{ index + 1 }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee; font-weight: bold; color: #2980b9;">L-{{ item.ma_lo_hang }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee;">{{ item.so_booking || '---' }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee; font-weight: bold;">{{ item.ten_hang_hoa || '---' }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee;">
              {{ item.cang_di || '?' }} <br> <span style="color: #7f8c8d; font-size: 12px;">➔ {{ item.cang_den || '?' }}</span>
            </td>
            <td style="padding: 12px; border-right: 1px solid #eee; font-size: 13px;">
              ETD: <strong>{{ formatDate(item.etd) }}</strong> <br>
              ETA: <strong style="color: #27ae60;">{{ formatDate(item.eta) }}</strong>
            </td>
            <td style="padding: 12px; border-right: 1px solid #eee; text-align: center;">
              <span class="badge" :class="getBadgeClass(item.trang_thai_lo_hang)">
                {{ item.trang_thai_lo_hang }}
              </span>
            </td>
            <td style="padding: 12px; text-align: center;">
              <button @click="openModal(item)" class="btn-xem">👁️ Xem</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <h2 style="text-align: center; margin-top: 0; color: #2c3e50; text-transform: uppercase;">Hồ sơ vận chuyển</h2>
        
        <div style="display: flex; gap: 40px; margin-top: 25px;">
          <div style="flex: 1;">
            <h4 style="border-bottom: 2px solid #16a085; padding-bottom: 5px; color: #16a085; margin-bottom: 15px;">📦 Thông tin lô hàng</h4>
            <table class="detail-table">
              <tr><td>Khách hàng:</td><td><strong>{{ selectedItem.ten_khach_hang || '---' }}</strong></td></tr>
              <tr><td>Tên hàng:</td><td><strong>{{ selectedItem.ten_hang_hoa || '---' }}</strong></td></tr>
              <tr><td>Số lượng:</td><td><strong>{{ selectedItem.tong_so_luong || 0 }} kiện/hộp</strong></td></tr>
              <tr><td>Trọng lượng:</td><td><strong>{{ selectedItem.tong_trong_luong || 0 }} KGS</strong></td></tr>
              <tr><td>Thể tích:</td><td><strong>{{ selectedItem.tong_the_tich || 0 }} CBM</strong></td></tr>
            </table>
          </div>

          <div style="flex: 1;">
            <h4 style="border-bottom: 2px solid #2980b9; padding-bottom: 5px; color: #2980b9; margin-bottom: 15px;">🚢 Thông tin vận tải</h4>
            <table class="detail-table">
              <tr><td>Hãng tàu:</td><td><strong>{{ selectedItem.ten_hang_tau || '---' }}</strong></td></tr>
              <tr><td>Vận đơn B/L:</td><td><strong>{{ selectedItem.so_van_don || '---' }}</strong></td></tr>
              <tr><td>Tuyến đường:</td><td><strong>{{ selectedItem.cang_di || '?' }} ➔ {{ selectedItem.cang_den || '?' }}</strong></td></tr>
              <tr><td>Ngày đi (ETD):</td><td><strong>{{ formatDateTime(selectedItem.etd) }}</strong></td></tr>
              <tr><td>Ngày đến (ETA):</td><td><strong>{{ formatDateTime(selectedItem.eta) }}</strong></td></tr>
            </table>
          </div>
        </div>

        <div style="text-align: center; margin-top: 35px;">
          <button @click="isModalOpen = false" class="btn btn-close">Đóng Hồ Sơ</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const listData = ref([]);
const stats = ref({ tong_so: 0, dang_van_chuyen: 0, hoan_thanh: 0 });
const isLoading = ref(false);
const isModalOpen = ref(false);
const selectedItem = ref({});

const filters = ref({
  tu_ngay: '',
  den_ngay: '',
  trang_thai: '',
  tim_kiem: ''
});

const formatDate = (dateStr) => {
  if (!dateStr) return '---';
  return new Date(dateStr).toLocaleDateString('vi-VN');
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return '---';
  const date = new Date(dateStr);
  return `${date.toLocaleDateString('vi-VN')} ${date.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const getBadgeClass = (status) => {
  if (status === 'Hoàn tất' || status === 'Đã thông quan') return 'badge-success';
  if (status === 'Đang vận chuyển') return 'badge-warning';
  if (status === 'Hủy') return 'badge-danger';
  return 'badge-info';
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const query = new URLSearchParams(filters.value).toString();
    const res = await fetch(`http://127.0.0.1:8000/api/bao-cao/van-chuyen?${query}`);
    const data = await res.json();
    if (data.success) {
      listData.value = data.data;
      stats.value = data.thong_ke;
    }
  } catch (error) {
    console.error("Lỗi:", error);
  } finally {
    isLoading.value = false;
  }
};

const openModal = (item) => {
  selectedItem.value = item;
  isModalOpen.value = true;
};

// Hàm xuất Excel (CSV)
const exportExcel = () => {
  if (listData.value.length === 0) {
    alert("Không có dữ liệu để xuất!");
    return;
  }
  
  let csvContent = "data:text/csv;charset=utf-8,\uFEFF"; 
  csvContent += "Mã lô hàng,Số Booking,Tên hàng,Cảng đi,Cảng đến,ETD,ETA,Trạng thái\r\n"; 

  listData.value.forEach(row => {
    let tr = [
      `L-${row.ma_lo_hang}`,
      row.so_booking || '',
      `"${row.ten_hang_hoa || ''}"`,
      `"${row.cang_di || ''}"`,
      `"${row.cang_den || ''}"`,
      formatDate(row.etd),
      formatDate(row.eta),
      row.trang_thai_lo_hang
    ];
    csvContent += tr.join(",") + "\r\n";
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", "Bao_Cao_Van_Chuyen.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(fetchData);
</script>

<style scoped>
.form-control { width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background: white;}
.btn { padding: 9px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
.btn-primary { background: #3498db; color: white; }
.btn-success { background: #27ae60; color: white; }
.btn-close { background: #ecf0f1; border: 1px solid #bdc3c7; color: #2c3e50; padding: 10px 30px; border-radius: 6px; font-weight: bold; cursor: pointer;}
.btn-close:hover { background: #bdc3c7; }
.btn-xem { background: #f1f2f6; border: 1px solid #dcdde1; border-radius: 4px; padding: 5px 12px; cursor: pointer; transition: 0.2s; font-weight: bold;}
.btn-xem:hover { background: #3498db; color: white; border-color: #3498db;}

.stats-cards .card { flex: 1; padding: 25px; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; }
.card-title { font-size: 15px; font-weight: bold; color: #34495e; margin-bottom: 12px; text-transform: uppercase;}
.card-value { font-size: 32px; font-weight: bold; }

.badge { padding: 5px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
.badge-info { background: #d6eaf8; color: #2980b9; }
.badge-success { background: #d4efdf; color: #27ae60; }
.badge-warning { background: #fcf3cf; color: #d35400; }
.badge-danger { background: #fadbd8; color: #c0392b; }

/* CSS Modal */
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-content { background: white; padding: 35px; border-radius: 10px; width: 850px; max-width: 95%; box-shadow: 0 15px 30px rgba(0,0,0,0.3); animation: slideDown 0.3s ease-out;}
.detail-table { width: 100%; border-collapse: collapse; }
.detail-table td { padding: 12px 0; border-bottom: 1px dashed #ecf0f1; font-size: 15px;}
.detail-table td:first-child { color: #7f8c8d; width: 40%; }
.detail-table strong { color: #2c3e50; }

@keyframes slideDown {
  from { transform: translateY(-50px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
</style>