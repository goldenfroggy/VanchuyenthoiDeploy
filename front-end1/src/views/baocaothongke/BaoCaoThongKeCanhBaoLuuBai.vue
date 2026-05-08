<template>
  <div style="padding: 20px;">
    <h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">BÁO CÁO CẢNH BÁO LƯU BÃI</h2>

    <div class="filter-section" style="display: flex; gap: 15px; margin-bottom: 25px; align-items: flex-end;">
      <div style="flex: 1; min-width: 250px;">
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Tìm kiếm</label>
        <input type="text" v-model="filters.tim_kiem" placeholder="Nhập số Cont, mã lô hàng..." class="form-control">
      </div>
      <div>
        <button @click="fetchData" class="btn btn-primary" style="margin-right: 10px;">🔍 Tìm kiếm</button>
        <button @click="exportExcel" class="btn btn-success">📊 Excel</button>
      </div>
    </div>

    <div class="stats-cards" style="display: flex; gap: 20px; margin-bottom: 25px;">
      <div class="card" style="border-left: 5px solid #27ae60;">
        <div class="card-title">Số lượng Cont an toàn:</div>
        <div class="card-value" style="color: #27ae60;">{{ stats.an_toan }}</div>
      </div>
      <div class="card" style="border-left: 5px solid #f39c12;">
        <div class="card-title">Số lượng Cont sắp hết hạn:</div>
        <div class="card-value" style="color: #f39c12;">{{ stats.sap_het_han }}</div>
      </div>
      <div class="card" style="border-left: 5px solid #e74c3c;">
        <div class="card-title">Số lượng Cont quá hạn lưu:</div>
        <div class="card-value" style="color: #e74c3c;">{{ stats.qua_han }}</div>
      </div>
    </div>

    <div class="table-container" style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #34495e; color: white;">
          <tr>
            <th style="padding: 12px; border-right: 1px solid #2c3e50; text-align: center;">STT</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50;">Mã lô hàng</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50;">Số Cont</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50;">Ngày bắt đầu lưu</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50; text-align: center;">Ngày miễn phí</th>
            <th style="padding: 12px; border-right: 1px solid #2c3e50; text-align: center;">Số ngày CÒN LẠI</th>
            <th style="padding: 12px; text-align: center;">Mức độ cảnh báo</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading"><td colspan="7" style="text-align: center; padding: 20px;">Đang tải dữ liệu...</td></tr>
          <tr v-else-if="listData.length === 0"><td colspan="7" style="text-align: center; padding: 20px;">Tuyệt vời! Không có Cont nào đang lưu bãi.</td></tr>
          
          <tr v-for="(item, index) in listData" :key="index" style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px; border-right: 1px solid #eee; text-align: center;">{{ index + 1 }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee; font-weight: bold; color: #2c3e50;">L-{{ item.ma_lo_hang }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee; font-weight: bold; color: #2980b9;">{{ item.so_cont || 'Chưa có số' }}</td>
            
            <td style="padding: 12px; border-right: 1px solid #eee;">
              {{ formatDate(item.ngay_bat_dau_luu_bai) }}
            </td>
            
            <td style="padding: 12px; border-right: 1px solid #eee; text-align: center; color: #7f8c8d;">
              {{ item.ngay_luu_bai_mien_phi }} ngày
            </td>

            <td style="padding: 12px; border-right: 1px solid #eee; text-align: center; font-size: 16px; font-weight: bold;"
                :style="{ color: item.so_ngay_con_lai < 0 ? '#e74c3c' : (item.so_ngay_con_lai <= 2 ? '#f39c12' : '#27ae60') }">
              {{ item.so_ngay_con_lai }}
            </td>
            
            <td style="padding: 12px; text-align: center;">
              <span class="badge" :class="{
                'badge-danger': item.muc_do_canh_bao === 'Quá hạn lưu',
                'badge-warning': item.muc_do_canh_bao === 'Sắp hết hạn',
                'badge-success': item.muc_do_canh_bao === 'An toàn'
              }">
                {{ item.muc_do_canh_bao }}
              </span>
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
const stats = ref({ an_toan: 0, sap_het_han: 0, qua_han: 0 });
const isLoading = ref(false);
const filters = ref({ tim_kiem: '' });

const formatDate = (dateStr) => {
  if (!dateStr) return '---';
  return new Date(dateStr).toLocaleDateString('vi-VN');
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const query = new URLSearchParams(filters.value).toString();
    const res = await fetch(`http://127.0.0.1:8000/api/bao-cao/canh-bao-luu-bai?${query}`);
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

const exportExcel = () => {
  if (listData.value.length === 0) {
    alert("Không có dữ liệu để xuất!");
    return;
  }
  
  let csvContent = "data:text/csv;charset=utf-8,\uFEFF";
  csvContent += "Mã lô hàng,Số Cont,Ngày bắt đầu lưu,Ngày miễn phí,Số ngày còn lại,Mức độ cảnh báo\r\n";

  listData.value.forEach(row => {
    let tr = [
      `L-${row.ma_lo_hang}`,
      `"${row.so_cont || ''}"`,
      formatDate(row.ngay_bat_dau_luu_bai),
      row.ngay_luu_bai_mien_phi,
      row.so_ngay_con_lai,
      row.muc_do_canh_bao
    ];
    csvContent += tr.join(",") + "\r\n";
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", "Canh_Bao_Luu_Bai.csv");
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

.stats-cards .card { flex: 1; padding: 25px; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; }
.card-title { font-size: 16px; font-weight: bold; color: #34495e; margin-bottom: 15px; }
.card-value { font-size: 32px; font-weight: bold; }

.badge { padding: 5px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
.badge-danger { background: #fadbd8; color: #c0392b; animation: blink 1.5s infinite;}
.badge-warning { background: #fcf3cf; color: #d35400; }
.badge-success { background: #d4efdf; color: #27ae60; }

@keyframes blink {
  0% { opacity: 1; }
  50% { opacity: 0.6; }
  100% { opacity: 1; }
}
</style>