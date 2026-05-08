<template>
  <div style="padding: 20px;">
    <h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">BÁO CÁO SẢN LƯỢNG VẬN CHUYỂN</h2>

    <div class="filter-section" style="display: flex; gap: 15px; margin-bottom: 25px; align-items: flex-end;">
      <div>
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Từ kỳ (Tháng/Năm)</label>
        <input type="month" v-model="filters.tu_ky" class="form-control">
      </div>
      <div>
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Đến kỳ (Tháng/Năm)</label>
        <input type="month" v-model="filters.den_ky" class="form-control">
      </div>
      <div style="flex: 1; min-width: 250px;">
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Tìm kiếm</label>
        <input type="text" v-model="filters.tim_kiem" placeholder="Nhập tên khách hàng..." class="form-control">
      </div>
      <div>
        <button @click="fetchData" class="btn btn-primary" style="margin-right: 10px;">🔍 Tìm kiếm</button>
        <button @click="exportExcel" class="btn btn-success">📊 Excel</button>
      </div>
    </div>

    <div class="table-container" style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: center;">
        <thead style="background: #f8f9fa; border-bottom: 2px solid #ccc;">
          <tr>
            <th style="padding: 12px; border-right: 1px solid #eee;">STT</th>
            <th style="padding: 12px; border-right: 1px solid #eee; text-align: left;">Tên khách hàng</th>
            <th style="padding: 12px; border-right: 1px solid #eee;">Tổng số lô hàng</th>
            <th style="padding: 12px; border-right: 1px solid #eee;">Tổng số kiện</th>
            <th style="padding: 12px; border-right: 1px solid #eee;">Tổng trọng lượng (KGS)</th>
            <th style="padding: 12px; border-right: 1px solid #eee;">Tổng thể tích (CBM)</th>
            <th style="padding: 12px;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading"><td colspan="7" style="padding: 20px;">Đang tải dữ liệu...</td></tr>
          <tr v-else-if="listData.length === 0"><td colspan="7" style="padding: 20px;">Không có dữ liệu</td></tr>
          
          <tr v-for="(item, index) in listData" :key="item.ma_khach_hang" style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px; border-right: 1px solid #eee;">{{ index + 1 }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee; text-align: left; font-weight: bold; color: #2980b9;">{{ item.ten_khach_hang }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee; font-weight: bold;">{{ item.tong_so_lo }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee;">{{ item.tong_so_kien }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee;">{{ formatNumber(item.tong_trong_luong) }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee;">{{ formatNumber(item.tong_the_tich) }}</td>
            <td style="padding: 12px;">
              <button @click="openModal(item)" class="btn-xem">👁️ Xem</button>
            </td>
          </tr>
        </tbody>
        <tfoot style="background: #ecf0f1; font-weight: bold;">
          <tr>
            <td colspan="2" style="padding: 15px; border-right: 1px solid #ccc; text-align: right;">TỔNG CỘNG:</td>
            <td style="padding: 15px; border-right: 1px solid #ccc;">{{ grandTotals.tong_lo }}</td>
            <td style="padding: 15px; border-right: 1px solid #ccc;">{{ grandTotals.tong_kien }}</td>
            <td style="padding: 15px; border-right: 1px solid #ccc;">{{ formatNumber(grandTotals.tong_trong) }}</td>
            <td style="padding: 15px; border-right: 1px solid #ccc;">{{ formatNumber(grandTotals.tong_the_tich) }}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <h3 style="text-align: center; margin-top: 0; color: #2c3e50;">Sản lượng chi tiết</h3>
        <p style="text-align: center; font-style: italic; margin-bottom: 20px;">Khách hàng: <strong>{{ selectedCustomer.ten_khach_hang }}</strong></p>
        
        <div style="max-height: 400px; overflow-y: auto;">
          <table style="width: 100%; border-collapse: collapse; text-align: center;">
            <thead style="background: #34495e; color: white;">
              <tr>
                <th style="padding: 10px; border: 1px solid #2c3e50;">STT</th>
                <th style="padding: 10px; border: 1px solid #2c3e50;">Mã lô hàng</th>
                <th style="padding: 10px; border: 1px solid #2c3e50;">ETD</th>
                <th style="padding: 10px; border: 1px solid #2c3e50;">Tuyến đường</th>
                <th style="padding: 10px; border: 1px solid #2c3e50;">Số kiện</th>
                <th style="padding: 10px; border: 1px solid #2c3e50;">Trọng lượng (KGS)</th>
                <th style="padding: 10px; border: 1px solid #2c3e50;">Thể tích (CBM)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(ct, idx) in selectedCustomer.chi_tiet" :key="idx" style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px; border: 1px solid #eee;">{{ idx + 1 }}</td>
                <td style="padding: 10px; border: 1px solid #eee; font-weight: bold;">L-{{ ct.ma_lo_hang }}</td>
                <td style="padding: 10px; border: 1px solid #eee;">{{ formatDate(ct.etd) }}</td>
                <td style="padding: 10px; border: 1px solid #eee; font-size: 13px;">{{ ct.tuyen_duong }}</td>
                <td style="padding: 10px; border: 1px solid #eee;">{{ ct.so_kien }}</td>
                <td style="padding: 10px; border: 1px solid #eee;">{{ formatNumber(ct.trong_luong) }}</td>
                <td style="padding: 10px; border: 1px solid #eee;">{{ formatNumber(ct.the_tich) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div style="text-align: center; margin-top: 25px;">
          <button @click="isModalOpen = false" class="btn btn-close">Đóng</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const listData = ref([]);
const isLoading = ref(false);
const isModalOpen = ref(false);
const selectedCustomer = ref({});

const filters = ref({
  tu_ky: '',
  den_ky: '',
  tim_kiem: ''
});

// Format hiển thị ngày và số
const formatDate = (dateStr) => {
  if (!dateStr) return '---';
  return new Date(dateStr).toLocaleDateString('vi-VN');
};
const formatNumber = (num) => {
  if (!num) return 0;
  return Number(num).toLocaleString('vi-VN');
};

// Tính tự động Dòng Tổng Cộng
const grandTotals = computed(() => {
  let tong_lo = 0, tong_kien = 0, tong_trong = 0, tong_the_tich = 0;
  listData.value.forEach(item => {
    tong_lo += item.tong_so_lo;
    tong_kien += Number(item.tong_so_kien);
    tong_trong += Number(item.tong_trong_luong);
    tong_the_tich += Number(item.tong_the_tich);
  });
  return { tong_lo, tong_kien, tong_trong, tong_the_tich };
});

const fetchData = async () => {
  isLoading.value = true;
  try {
    const query = new URLSearchParams(filters.value).toString();
    const res = await fetch(`http://127.0.0.1:8000/api/bao-cao/san-luong?${query}`);
    const data = await res.json();
    if (data.success) {
      listData.value = data.data;
    }
  } catch (error) {
    console.error("Lỗi:", error);
  } finally {
    isLoading.value = false;
  }
};

const openModal = (customer) => {
  selectedCustomer.value = customer; // Gắn dữ liệu chi tiết của khách hàng đó vào Modal
  isModalOpen.value = true;
};

// Hàm xuất Excel
const exportExcel = () => {
  if (listData.value.length === 0) {
    alert("Không có dữ liệu để xuất!");
    return;
  }
  
  let csvContent = "data:text/csv;charset=utf-8,\uFEFF";
  csvContent += "Tên khách hàng,Tổng số lô hàng,Tổng số kiện,Tổng trọng lượng (KGS),Tổng thể tích (CBM)\r\n";

  listData.value.forEach(row => {
    let tr = [
      `"${row.ten_khach_hang}"`,
      row.tong_so_lo,
      row.tong_so_kien,
      row.tong_trong_luong,
      row.tong_the_tich
    ];
    csvContent += tr.join(",") + "\r\n";
  });
  // Dòng tổng cộng
  csvContent += `TỔNG CỘNG,${grandTotals.value.tong_lo},${grandTotals.value.tong_kien},${grandTotals.value.tong_trong},${grandTotals.value.tong_the_tich}\r\n`;

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", "Bao_Cao_San_Luong.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(fetchData);
</script>

<style scoped>
.form-control { width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
.btn { padding: 9px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
.btn-primary { background: #3498db; color: white; }
.btn-success { background: #27ae60; color: white; }
.btn-close { background: #fff; border: 1px solid #ccc; color: #333; padding: 8px 35px;}
.btn-xem { background: #f1f2f6; border: 1px solid #dcdde1; border-radius: 4px; padding: 5px 10px; cursor: pointer; transition: 0.2s; }
.btn-xem:hover { background: #eccc68; }

.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-content { background: white; padding: 30px; border-radius: 8px; width: 850px; max-width: 95%; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
</style>