<template>
  <div style="padding: 20px;">
    <h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">THỐNG KÊ BÁO CÁO CƯỢC VỎ</h2>

    <div class="filter-section" style="display: flex; gap: 15px; margin-bottom: 25px; align-items: flex-end; flex-wrap: wrap;">
      <div>
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Từ kỳ (Tháng/Năm)</label>
        <input type="month" v-model="filters.tu_ky" class="form-control">
      </div>
      <div>
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Đến kỳ (Tháng/Năm)</label>
        <input type="month" v-model="filters.den_ky" class="form-control">
      </div>
      
      <div style="min-width: 180px;">
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Tình trạng cược vỏ</label>
        <select v-model="filters.tinh_trang_cuoc_vo" class="form-control">
          <option value="">-- Tất cả --</option>
          <option value="Có">Có cược vỏ</option>
          <option value="Không">Không cược vỏ</option>
        </select>
      </div>

      <div style="flex: 1; min-width: 200px;">
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Tìm kiếm</label>
        <input type="text" v-model="filters.tim_kiem" placeholder="Nhập số Cont, mã lô hàng..." class="form-control">
      </div>
      <div>
        <button @click="fetchData" class="btn btn-primary" style="margin-right: 10px;">🔍 Tìm kiếm</button>
        <button @click="exportExcel" class="btn btn-success">📊 Excel</button>
      </div>
    </div>

    <div class="stats-cards" style="display: flex; gap: 20px; margin-bottom: 25px;">
      <div class="card" style="border-left: 5px solid #e74c3c;">
        <div class="card-title">Số lượng Cont chưa trả vỏ:</div>
        <div class="card-value" style="color: #e74c3c;">{{ stats.chua_tra }}</div>
      </div>
      <div class="card" style="border-left: 5px solid #27ae60;">
        <div class="card-title">Số lượng đã trả vỏ an toàn:</div>
        <div class="card-value" style="color: #27ae60;">{{ stats.da_tra }}</div>
      </div>
      <div class="card" style="border-left: 5px solid #f39c12;">
        <div class="card-title">Số lượng Cont có cược vỏ:</div>
        <div class="card-value" style="color: #f39c12;">{{ stats.co_cuoc_vo }}</div>
      </div>
    </div>

    <div class="table-container" style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #f8f9fa; border-bottom: 2px solid #ccc;">
          <tr>
            <th style="padding: 12px; border-right: 1px solid #eee; text-align: center;">STT</th>
            <th style="padding: 12px; border-right: 1px solid #eee;">Mã lô hàng</th>
            <th style="padding: 12px; border-right: 1px solid #eee;">Số Cont</th>
            <th style="padding: 12px; border-right: 1px solid #eee; text-align: center;">Cược vỏ</th>
            <th style="padding: 12px; border-right: 1px solid #eee;">Ngày bắt đầu lưu</th>
            <th style="padding: 12px; text-align: center;">Trạng thái lưu vỏ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading"><td colspan="6" style="text-align: center; padding: 20px;">Đang tải dữ liệu...</td></tr>
          <tr v-else-if="listData.length === 0"><td colspan="6" style="text-align: center; padding: 20px;">Không có dữ liệu</td></tr>
          
          <tr v-for="(item, index) in listData" :key="index" style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px; border-right: 1px solid #eee; text-align: center;">{{ index + 1 }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee; font-weight: bold; color: #2c3e50;">L-{{ item.ma_lo_hang }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee; font-weight: bold; color: #2980b9;">{{ item.so_cont || 'Chưa có số Cont' }}</td>
            
            <td style="padding: 12px; border-right: 1px solid #eee; text-align: center;">
              <span class="badge" :class="item.cuoc_vo === 'Có' ? 'badge-warning' : 'badge-light'">
                {{ item.cuoc_vo }}
              </span>
            </td>
            
            <td style="padding: 12px; border-right: 1px solid #eee;">
              {{ formatDateTime(item.ngay_bat_dau_luu_bai) }}
            </td>
            
            <td style="padding: 12px; text-align: center;">
              <span class="badge" :class="{
                'badge-danger': item.trang_thai_luu_bai === 'Đang lưu bãi',
                'badge-info': item.trang_thai_luu_bai === 'Đã rút hàng',
                'badge-success': item.trang_thai_luu_bai === 'Đã trả vỏ'
              }">
                {{ item.trang_thai_luu_bai || 'Chưa cập nhật' }}
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
const stats = ref({ chua_tra: 0, da_tra: 0, co_cuoc_vo: 0 });
const isLoading = ref(false);

const filters = ref({
  tu_ky: '',
  den_ky: '',
  tinh_trang_cuoc_vo: '',
  tim_kiem: ''
});

const formatDateTime = (dateStr) => {
  if (!dateStr) return '---';
  const date = new Date(dateStr);
  return `${date.toLocaleDateString('vi-VN')} ${date.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const query = new URLSearchParams(filters.value).toString();
    const res = await fetch(`http://127.0.0.1:8000/api/bao-cao/cuoc-vo?${query}`);
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
  csvContent += "Mã lô hàng,Số Cont,Cược vỏ,Ngày bắt đầu lưu,Trạng thái lưu vỏ\r\n";

  listData.value.forEach(row => {
    let tr = [
      `L-${row.ma_lo_hang}`,
      `"${row.so_cont || ''}"`,
      row.cuoc_vo,
      formatDateTime(row.ngay_bat_dau_luu_bai),
      row.trang_thai_luu_bai
    ];
    csvContent += tr.join(",") + "\r\n";
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", "Bao_Cao_Cuoc_Vo.csv");
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
.badge-danger { background: #fadbd8; color: #c0392b; }
.badge-warning { background: #fcf3cf; color: #d35400; }
.badge-success { background: #d4efdf; color: #27ae60; }
.badge-info { background: #d6eaf8; color: #2980b9; }
.badge-light { background: #ecf0f1; color: #7f8c8d; border: 1px solid #bdc3c7;}
</style>