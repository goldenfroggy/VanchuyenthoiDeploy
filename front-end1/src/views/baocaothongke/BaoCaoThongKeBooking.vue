<template>
  <div style="padding: 20px;">
    <h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">THỐNG KÊ BÁO CÁO BOOKING</h2>

    <div class="filter-section" style="display: flex; gap: 15px; margin-bottom: 25px; align-items: flex-end; flex-wrap: wrap;">
      <div>
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Từ kỳ (Tháng/Năm)</label>
        <input type="month" v-model="filters.tu_ky" class="form-control">
      </div>
      <div>
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Đến kỳ (Tháng/Năm)</label>
        <input type="month" v-model="filters.den_ky" class="form-control">
      </div>
      
      <div style="min-width: 200px;">
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Hãng tàu</label>
        <select v-model="filters.ma_hang_tau" class="form-control">
          <option value="">-- Tất cả hãng tàu --</option>
          <option v-for="ht in listHangTau" :key="ht.ma_hang_tau" :value="ht.ma_hang_tau">
            {{ ht.ten_hang_tau }}
          </option>
        </select>
      </div>

      <div style="flex: 1; min-width: 200px;">
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Tìm kiếm</label>
        <input type="text" v-model="filters.tim_kiem" placeholder="Nhập số booking, tên tàu..." class="form-control">
      </div>
      <div>
        <button @click="fetchData" class="btn btn-primary" style="margin-right: 10px;">🔍 Tìm kiếm</button>
        <button @click="exportExcel" class="btn btn-success">📊 Excel</button>
      </div>
    </div>

    <div class="stats-cards" style="display: flex; gap: 20px; margin-bottom: 25px;">
      <div class="card" style="border-left: 5px solid #8e44ad;">
        <div class="card-title">Tổng số booking trong kỳ:</div>
        <div class="card-value" style="color: #8e44ad;">{{ stats.tong_so }}</div>
      </div>
      <div class="card" style="border-left: 5px solid #e74c3c;">
        <div class="card-title">Chưa hoàn thành:</div>
        <div class="card-value" style="color: #e74c3c;">{{ stats.chua_hoan_thanh }}</div>
      </div>
      <div class="card" style="border-left: 5px solid #2ecc71;">
        <div class="card-title">Đã hoàn tất:</div>
        <div class="card-value" style="color: #2ecc71;">{{ stats.da_hoan_tat }}</div>
      </div>
    </div>

    <div class="table-container" style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #34495e; color: white;">
          <tr>
            <th style="padding: 12px; border-right: 1px solid #2c3e50; text-align: center;">STT</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50;">Số booking</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50;">Hãng tàu</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50;">Tên tàu</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50;">Cảng đi ➔ Đến</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50;">Ngày đi (ETD)</th>
            <th style="padding: 12px; text-align: center; color: #f1c40f;">Giờ cut-off</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading"><td colspan="7" style="text-align: center; padding: 20px;">Đang tải dữ liệu...</td></tr>
          <tr v-else-if="listData.length === 0"><td colspan="7" style="text-align: center; padding: 20px;">Không có dữ liệu</td></tr>
          
          <tr v-for="(item, index) in listData" :key="item.ma_booking" style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px; border-right: 1px solid #eee; text-align: center;">{{ index + 1 }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee; font-weight: bold; color: #2980b9;">
              {{ item.so_booking }}
              <br><span style="font-size: 11px; color: #7f8c8d; font-weight: normal;">({{ item.trang_thai }})</span>
            </td>
            <td style="padding: 12px; border-right: 1px solid #eee; font-weight: bold;">{{ item.ten_hang_tau || '---' }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee;">{{ item.ten_con_tau || '---' }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee;">
              {{ item.cang_di || '?' }} ➔ {{ item.cang_den || '?' }}
            </td>
            <td style="padding: 12px; border-right: 1px solid #eee; font-weight: bold;">
              {{ formatDate(item.etd) }}
            </td>
            <td style="padding: 12px; text-align: center; font-weight: bold; color: #e74c3c;">
              {{ formatDateTime(item.gio_cat_mang) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const listData = ref([]);
const listHangTau = ref([]); // Danh sách đổ vào Combobox
const stats = ref({ tong_so: 0, chua_hoan_thanh: 0, da_hoan_tat: 0 });
const isLoading = ref(false);

const filters = ref({
  tu_ky: '',
  den_ky: '',
  ma_hang_tau: '',
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

const fetchData = async () => {
  isLoading.value = true;
  try {
    const query = new URLSearchParams(filters.value).toString();
    const res = await fetch(`http://127.0.0.1:8000/api/bao-cao/booking?${query}`);
    const data = await res.json();
    if (data.success) {
      listData.value = data.data;
      listHangTau.value = data.hang_tau; // Gán data Hãng tàu từ API
      stats.value = data.thong_ke;
    }
  } catch (error) {
    console.error("Lỗi:", error);
  } finally {
    isLoading.value = false;
  }
};

const exportExcel = () => {
  if (listData.value.length === 0) {
    alert("Không có dữ liệu để xuất!");
    return;
  }
  
  let csvContent = "data:text/csv;charset=utf-8,\uFEFF";
  csvContent += "Số booking,Hãng tàu,Tên tàu,Cảng đi,Cảng đến,Ngày đi (ETD),Giờ cut-off,Trạng thái\r\n";

  listData.value.forEach(row => {
    let tr = [
      `"${row.so_booking}"`,
      `"${row.ten_hang_tau || ''}"`,
      `"${row.ten_con_tau || ''}"`,
      `"${row.cang_di || ''}"`,
      `"${row.cang_den || ''}"`,
      formatDate(row.etd),
      formatDateTime(row.gio_cat_mang),
      row.trang_thai
    ];
    csvContent += tr.join(",") + "\r\n";
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", "Bao_Cao_Booking.csv");
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

.stats-cards .card { flex: 1; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); text-align: center; }
.card-title { font-size: 16px; font-weight: bold; color: #34495e; margin-bottom: 10px; }
.card-value { font-size: 28px; font-weight: bold; }
</style>