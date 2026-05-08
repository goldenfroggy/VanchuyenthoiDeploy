<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Tờ Khai Hải Quan</h3>
    
    <div class="toolbar" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
      <div style="display: flex; gap: 10px; width: 100%; flex-wrap: wrap;">
        <div class="search-box" style="flex: 1; min-width: 200px;">
          <input type="text" v-model="searchFilters.ma_to_khai" placeholder="🔍 Tìm theo mã tờ khai..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc;">
        </div>
        <div class="search-box" style="flex: 1; min-width: 200px;">
          <input type="text" v-model="searchFilters.ten_lo_hang" placeholder="🔍 Tìm theo tên lô hàng..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc;">
        </div>
      </div>

      <select v-model="searchFilters.phan_luong" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc; width: 180px;">
        <option value="">🌈 Phân luồng (Tất cả)</option>
        <option v-for="pl in listPhanLuong" :key="pl" :value="pl">{{ pl }}</option>
      </select>

      <select v-model="searchFilters.ket_qua_thong_quan" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc; width: 180px;">
        <option value="">✅ Kết quả (Tất cả)</option>
        <option v-for="kq in listKetQua" :key="kq" :value="kq">{{ kq }}</option>
      </select>

      <div style="display: flex; align-items: center; gap: 8px; background: #fff; padding: 0 10px; border: 1px solid #ccc; border-radius: 6px;">
        <label style="font-size: 13px; color: #666; white-space: nowrap;">Ngày thông quan:</label>
        <input type="date" v-model="searchFilters.ngay_thong_quan" style="padding: 7px; border: none; outline: none;">
      </div>

      <select v-model="searchFilters.ten_nguoi_sua" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc; width: 180px;">
        <option value="">👤 Người sửa (Tất cả)</option>
        <option v-for="name in uniqueNguoiSua" :key="name" :value="name">{{ name }}</option>
      </select>

      <button @click="clearFilters" style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 6px; background: #fff; cursor: pointer; transition: 0.2s;" title="Xóa lọc">🧹 Xóa lọc</button>
      <button class="btn btn-success" @click="router.push('/van-tai/to-khai-hai-quan/add')" style="border-radius: 6px;">+ TẠO TỜ KHAI MỚI</button>
    </div>

    <div v-if="isLoadingData" style="text-align: center; padding: 20px; color: #3498db;">Đang tải dữ liệu Tờ khai...</div>

    <div v-else style="display: flex; gap: 20px; align-items: flex-start;">
      <!-- BÊN TRÁI: DANH SÁCH -->
      <div style="flex: 1; min-width: 0;">
        <!-- Kiểm soát phân trang -->
        <div v-if="listToKhai.length > 0" class="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e1e4e8;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <span>Hiển thị</span>
            <select v-model="pageSize" style="padding: 5px 8px; border-radius: 5px; border: 1px solid #ccc;">
              <option v-for="size in pageSizes" :key="size" :value="size">{{ size }}</option>
            </select>
            <span>mục</span>
          </div>
          <div style="display: flex; align-items: center; gap: 10px;">
            <button @click="prevPage" :disabled="currentPage === 1" class="btn-pagination">◀ Trước</button>
            <span style="font-weight: bold;">Trang {{ currentPage }} / {{ totalPages }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages" class="btn-pagination">Sau ▶</button>
          </div>
        </div>

        <div class="table-card">
          <table>
            <thead>
              <tr>
                <th style="width: 50px; text-align: center;">STT</th>
                <th>Mã Tờ Khai</th>
                <th>Thuộc Lô Hàng</th>
                <th>Ngày Thông Quan</th>
                <th>Phân Luồng</th>
                <th>Kết Quả</th>
                <th>Người sửa</th>
                <th style="text-align: center;">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(tk, index) in paginatedData" :key="tk.ma_to_khai_hai_quan" :class="{ 'row-selected': (selectedItem?.ma_to_khai_hai_quan === tk.ma_to_khai_hai_quan) }">
                <td style="text-align: center; color: #7f8c8d;">{{ (currentPage - 1) * pageSize + index + 1 }}</td>
                <td class="fw-bold">{{ tk.ma_to_khai_hai_quan }}</td>
                <td class="fw-bold" style="color: #2980b9;">{{ tk.ten_lo_hang || '---' }}</td>
                <td>{{ formatDateTime(tk.ngay_thong_quan) }}</td>
                <td><span class="badge" :style="getPhanLuongStyle(tk.phan_luong)">{{ tk.phan_luong || 'N/A' }}</span></td>
                <td><span class="badge" :style="getKetQuaStyle(tk.ket_qua_thong_quan)">{{ tk.ket_qua_thong_quan || 'N/A' }}</span></td>
                <td>{{ tk.ten_nguoi_sua || 'N/A' }}</td>
                <td style="text-align: center;">
                  <div style="display: grid; grid-template-columns: repeat(3, 35px); gap: 5px; justify-content: center; margin: 0 auto; width: fit-content;">
                    <button class="action-btn-no-mg text-success" @click="showShipmentInfo(tk)" title="Xem thông tin lô hàng">📋</button>
                    <button class="action-btn-no-mg text-primary" @click="router.push('/van-tai/to-khai-hai-quan/edit/' + tk.ma_to_khai_hai_quan)" title="Sửa">✏️</button>
                    <button class="action-btn-no-mg text-danger" @click="handleDelete(tk.ma_to_khai_hai_quan)" title="Xóa">🗑️</button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredData.length === 0">
                <td colspan="8" style="text-align: center; padding: 20px; color: #7f8c8d;">Không tìm thấy tờ khai nào!</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- BÊN PHẢI: SIDE PANEL -->
      <div v-if="viewType !== 'none'" class="side-panel">
        <div class="panel-header">
          <h4>{{ panelTitle }}</h4>
          <button @click="viewType = 'none'" class="close-panel">✖</button>
        </div>
        <div class="panel-body">
          <div v-if="isPanelLoading" style="text-align: center; padding: 20px;">Đang tải...</div>
          <div v-else-if="!selectedShipment" style="text-align: center; padding: 20px;">Không tìm thấy thông tin lô hàng</div>
          <div v-else>
            <div class="info-list">
              <div class="info-item"><strong>Mã lô hàng:</strong> <span>#{{ selectedShipment.ma_lo_hang }}</span></div>
              <div class="info-item"><strong>Tên lô hàng:</strong> <span>{{ selectedShipment.ten_lo_hang }}</span></div>
              <div class="info-item"><strong>Khách hàng:</strong> <span>{{ selectedShipment.ten_khach_hang || 'N/A' }}</span></div>
              <div class="info-item"><strong>Điều kiện (Incoterms):</strong> <span>{{ selectedShipment.dieu_kien_thuong_mai || 'N/A' }}</span></div>
              <div class="info-item"><strong>Trạng thái:</strong> <span class="badge badge-active">{{ selectedShipment.trang_thai_lo_hang }}</span></div>
              <div class="info-item"><strong>Booking liên kết:</strong> <span>{{ selectedShipment.so_booking || 'N/A' }}</span></div>
              <div class="info-item" style="flex-direction: column; align-items: flex-start; gap: 5px; border-bottom: none;">
                <strong>Nguồn gốc / Ghi chú:</strong>
                <div style="padding: 10px; background: #f9f9f9; border-radius: 4px; width: 100%; font-size: 13px; color: #555;">{{ selectedShipment.nguon_goc || '(Trống)' }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const listToKhai = ref([]);
const isLoadingData = ref(true);

// Phân trang
const currentPage = ref(1);
const pageSize = ref(10);
const pageSizes = [10, 20, 50];

// Side Panel
const viewType = ref('none');
const panelTitle = ref('');
const selectedItem = ref(null);
const selectedShipment = ref(null);
const isPanelLoading = ref(false);

const listPhanLuong = ['Luồng Xanh', 'Luồng Vàng', 'Luồng Đỏ'];
const listKetQua = ['Chờ xử lý', 'Đã thông quan', 'Từ chối'];

const searchFilters = ref({
  ma_to_khai: '',
  ten_lo_hang: '',
  phan_luong: '',
  ket_qua_thong_quan: '',
  ngay_thong_quan: '',
  ten_nguoi_sua: ''
});

const uniqueNguoiSua = computed(() => {
  const names = listToKhai.value.map(item => item.ten_nguoi_sua).filter(Boolean);
  return [...new Set(names)];
});

const filteredData = computed(() => {
  return listToKhai.value.filter(item => {
    const matchMa = !searchFilters.value.ma_to_khai || 
      (item.ma_to_khai_hai_quan && String(item.ma_to_khai_hai_quan).includes(searchFilters.value.ma_to_khai));
    const matchLoHang = !searchFilters.value.ten_lo_hang || 
      (item.ten_lo_hang && item.ten_lo_hang.toLowerCase().includes(searchFilters.value.ten_lo_hang.toLowerCase()));
    const matchPhanLuong = !searchFilters.value.phan_luong || item.phan_luong === searchFilters.value.phan_luong;
    const matchKetQua = !searchFilters.value.ket_qua_thong_quan || item.ket_qua_thong_quan === searchFilters.value.ket_qua_thong_quan;
    
    const matchDate = !searchFilters.value.ngay_thong_quan || (item.ngay_thong_quan && item.ngay_thong_quan.includes(searchFilters.value.ngay_thong_quan));
    const matchNguoiSua = !searchFilters.value.ten_nguoi_sua || item.ten_nguoi_sua === searchFilters.value.ten_nguoi_sua;

    return matchMa && matchLoHang && matchPhanLuong && matchKetQua && matchDate && matchNguoiSua;
  });
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredData.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(filteredData.value.length / pageSize.value) || 1);
const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

const fetchData = async () => {
  isLoadingData.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/to-khai-hai-quan');
    const data = await response.json();
    if (data.success) {
      listToKhai.value = data.data;
    }
  } catch (error) {
    console.error('Fetch error:', error);
    alert("Không thể kết nối API lấy danh sách!");
  } finally {
    isLoadingData.value = false;
  }
};

const showShipmentInfo = async (tk) => {
  selectedItem.value = tk;
  viewType.value = 'shipment';
  panelTitle.value = '📦 Thông tin lô hàng: ' + (tk.ten_lo_hang || tk.ma_lo_hang);
  isPanelLoading.value = true;
  try {
    const res = await fetch(`http://127.0.0.1:8000/api/lo-hang`);
    const data = await res.json();
    if (data.success) {
      selectedShipment.value = data.data.find(lh => lh.ma_lo_hang === tk.ma_lo_hang);
    } else {
      selectedShipment.value = null;
    }
  } catch (error) {
    selectedShipment.value = null;
  } finally {
    isPanelLoading.value = false;
  }
};

const clearFilters = () => {
  searchFilters.value = {
    ma_to_khai: '',
    ten_lo_hang: '',
    phan_luong: '',
    ket_qua_thong_quan: '',
    ngay_thong_quan: '',
    ten_nguoi_sua: ''
  };
  currentPage.value = 1;
};

watch([searchFilters, pageSize], () => {
  currentPage.value = 1;
}, { deep: true });

const handleDelete = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa tờ khai này không?')) {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/to-khai-hai-quan/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ma_to_khai_hai_quan: id })
      });
      const data = await response.json();
      if (data.success) { alert("Đã xóa thành công!"); fetchData(); }
      else { alert("Lỗi: " + data.message); }
    } catch (error) {
      alert("Lỗi khi xóa kết nối!");
    }
  }
};

onMounted(() => {
  fetchData(); 
});

const formatDateTime = (str) => {
  if (!str) return '---';
  return new Date(str).toLocaleString('vi-VN');
};

const getPhanLuongStyle = (pl) => {
  if (pl === 'Luồng Xanh') return { backgroundColor: '#27ae60', color: 'white', whiteSpace: 'nowrap' };
  if (pl === 'Luồng Vàng') return { backgroundColor: '#f1c40f', color: '#333', whiteSpace: 'nowrap' };
  if (pl === 'Luồng Đỏ') return { backgroundColor: '#e74c3c', color: 'white', whiteSpace: 'nowrap' };
  return { backgroundColor: '#95a5a6', color: 'white', whiteSpace: 'nowrap' };
};

const getKetQuaStyle = (kq) => {
  if (kq === 'Đã thông quan') return { backgroundColor: '#27ae60', color: 'white', whiteSpace: 'nowrap' };
  if (kq === 'Chờ xử lý') return { backgroundColor: '#f39c12', color: 'white', whiteSpace: 'nowrap' };
  if (kq === 'Từ chối') return { backgroundColor: '#e74c3c', color: 'white', whiteSpace: 'nowrap' };
  return { backgroundColor: '#95a5a6', color: 'white', whiteSpace: 'nowrap' };
};
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.btn-pagination {
  padding: 5px 12px; background: white; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; transition: 0.2s;
}
.btn-pagination:hover:not(:disabled) { background: #f0f0f0; border-color: #3498db; color: #3498db; }
.btn-pagination:disabled { cursor: not-allowed; opacity: 0.5; }

.side-panel {
  width: 380px; background: white; border-radius: 8px; border: 1px solid #ddd;
  box-shadow: -5px 0 15px rgba(0,0,0,0.05); position: sticky; top: 10px; min-height: 400px;
  display: flex; flex-direction: column; animation: slideIn 0.3s ease;
}
@keyframes slideIn { from { transform: translateX(20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

.panel-header {
  padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;
  background: #f8f9fa; border-radius: 8px 8px 0 0;
}
.panel-header h4 { margin: 0; color: #2c3e50; font-size: 15px; }
.close-panel { background: none; border: none; cursor: pointer; font-size: 16px; color: #95a5a6; }

.panel-body { padding: 15px; flex: 1; overflow-y: auto; }
.info-list { display: flex; flex-direction: column; gap: 15px; }
.info-item { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #eee; padding-bottom: 8px; font-size: 14px; }
.info-item strong { color: #7f8c8d; font-weight: normal; }
.info-item span { color: #2c3e50; font-weight: 600; text-align: right; }

.row-selected { background-color: #f0f7ff !important; }
.action-btn-no-mg { background: none; border: none; cursor: pointer; font-size: 16px; transition: 0.2s; padding: 0; margin: 0; display: flex; align-items: center; justify-content: center; height: 35px; width: 35px; }
.action-btn-no-mg:hover { transform: scale(1.2); }

.badge-active { background-color: #27ae60; color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; }
</style>
