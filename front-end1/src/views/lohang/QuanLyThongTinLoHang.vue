<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý thông tin Lô Hàng</h3>
    
    <div class="toolbar" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="🔍 Tìm theo tên, mã, nguồn gốc..."
          style="width: 100%; padding: 8px 12px; border-radius: 4px; border: 1px solid #ccc;"
        >
      </div>

      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input 
          type="text" 
          v-model="searchItemQuery" 
          placeholder="🔍 Tìm theo tên hàng hóa..."
          style="width: 100%; padding: 8px 12px; border-radius: 4px; border: 1px solid #ccc;"
        >
      </div>

      <select v-model="filterKhachHang" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 180px;">
        <option :value="null">👤 Khách hàng (Tất cả)</option>
        <option v-for="kh in listKhachHang" :key="kh.ma_khach_hang" :value="kh.ma_khach_hang">{{ kh.ten_khach_hang }}</option>
      </select>

      <select v-model="filterIncoterms" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 150px;">
        <option :value="null">🚚 Incoterms (Tất cả)</option>
        <option v-for="dk in ['FOB', 'CIF', 'EXW', 'DAP', 'DDP', 'CFR']" :key="dk" :value="dk">{{ dk }}</option>
      </select>

      <select v-model="filterHangHoa" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 180px;">
        <option :value="null">📦 Loại hàng (Tất cả)</option>
        <option v-for="h in listHangHoa" :key="h.ma_hang_hoa" :value="h.ma_hang_hoa">{{ h.ten_hang_hoa }}</option>
      </select>

      <select v-model="filterTrangThai" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 180px;">
        <option value="ALL">- Tất cả Trạng thái -</option>
        <option v-for="st in listTrangThai" :key="st" :value="st">{{ st }}</option>
      </select>

      <select v-model="filterUser" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 180px;">
        <option :value="null">👤 Người sửa (Tất cả)</option>
        <option v-for="user in uniqueUsers" :key="user" :value="user">{{ user }}</option>
      </select>

      <button 
        @click="resetFilters" 
        style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 4px; background: #fff; cursor: pointer; transition: 0.2s;"
        title="Xóa lọc"
      >
        🧹 Xóa lọc
      </button>

      <button class="btn btn-success" @click="router.push('/lo-hang/thong-tin-lo-hang/add')">+ TẠO LÔ HÀNG MỚI</button>
    </div>

    <div style="display: flex; gap: 20px; align-items: flex-start;">
      <!-- BÊN TRÁI: DANH SÁCH LÔ HÀNG -->
      <div style="flex: 1; min-width: 0;">
        <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
          Đang tải dữ liệu Lô hàng...
        </div>

        <template v-if="!isLoading">
          <!-- Pagination Controls -->
          <div v-if="listLoHang.length > 0" class="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e1e4e8;">
            <div style="display: flex; align-items: center; gap: 10px;">
              <span>Hiển thị</span>
              <select v-model="pageSize" style="padding: 5px 8px; border-radius: 5px; border: 1px solid #ccc;">
                <option v-for="size in pageSizes" :key="size" :value="size">{{ size }}</option>
              </select>
              <span>mục mỗi trang</span>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
              <button @click="prevPage" :disabled="currentPage === 1" class="btn-pagination">◀ Trước</button>
              <span style="font-weight: bold;">Trang {{ currentPage }} / {{ totalPages }}</span>
              <button @click="nextPage" :disabled="currentPage === totalPages" class="btn-pagination">Sau ▶</button>
            </div>
          </div>

          <div class="table-card" style="margin-top: 15px;">
            <table>
              <thead>
                <tr>
                  <th style="width: 50px; text-align: center;">STT</th>
                  <th>Mã Lô</th>
                  <th>Tên Lô Hàng</th>
                  <th>Khách Hàng</th>
                  <th>Điều kiện</th>
                  <th>Booking</th>
                  <th>Nguồn gốc</th>
                  <th>Trạng thái</th>
                  <th>Người sửa cuối</th>
                  <th style="text-align: center;">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(lh, index) in paginatedLoHang" :key="lh.ma_lo_hang" :class="{ 'row-selected': (selectedItem?.ma_lo_hang === lh.ma_lo_hang) }">
                  <td style="text-align: center; color: #7f8c8d;">{{ (currentPage - 1) * pageSize + index + 1 }}</td>
                  <td class="fw-bold">#{{ lh.ma_lo_hang }}</td>
                  <td class="fw-bold" style="color: #2980b9;">{{ lh.ten_lo_hang }}</td>
                  <td>{{ lh.ten_khach_hang || '---' }}</td>
                  <td><span class="badge" style="background-color: #9b59b6; color: white;">{{ lh.dieu_kien_thuong_mai }}</span></td>
                  <td>
                    <div style="display: flex; align-items: center; gap: 5px;">
                      {{ lh.so_booking || 'Chưa gắn' }}
                      <button v-if="lh.ma_booking" @click="showBookingInfo(lh)" class="view-btn" title="Xem Booking">👁️</button>
                    </div>
                  </td>
                  <td>{{ lh.nguon_goc || '---' }}</td>
                  <td>
                    <span class="badge" :class="statusClass(lh.trang_thai_lo_hang)" style="white-space: nowrap;">
                      {{ lh.trang_thai_lo_hang }}
                    </span>
                  </td>
                  <td>{{ lh.nguoi_sua_doi || 'N/A' }}</td>
                  <td style="text-align: center;">
                    <div style="display: flex; gap: 8px; justify-content: center;">
                      <button 
                        :class="['action-btn', lh.has_items ? 'text-success' : 'text-warning']" 
                        @click="lh.has_items && showShipmentItems(lh)" 
                        :title="lh.has_items ? 'Xem chi tiết hàng hóa' : 'Lô hàng chưa có chi tiết'">
                        {{ lh.has_items ? '📋' : '⚠️' }}</button>
                      <button class="action-btn text-primary" @click="router.push('/lo-hang/thong-tin-lo-hang/edit/' + lh.ma_lo_hang)" title="Sửa">✏️</button>
                      <button class="action-btn text-danger" @click="handleDelete(lh.ma_lo_hang)" title="Xóa">🗑️</button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredLoHang.length === 0">
                  <td colspan="9" style="text-align: center; padding: 20px; color: #7f8c8d;">
                    Không tìm thấy lô hàng nào!
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>
      </div>

      <!-- BÊN PHẢI: KHU VỰC CHI TIẾT (SIDE PANEL) -->
      <div v-if="viewType !== 'none'" class="side-panel">
        <div class="panel-header">
          <h4>{{ panelTitle }}</h4>
          <button @click="viewType = 'none'" class="close-panel">✖</button>
        </div>

        <!-- Nội dung 1: Thông tin Booking -->
        <div v-if="viewType === 'booking' && selectedBooking" class="panel-body">
          <div class="info-row"><span>Số Booking:</span> <strong>{{ selectedBooking.so_booking }}</strong></div>
          <div class="info-row"><span>Hãng tàu:</span> <strong>{{ selectedBooking.ten_hang_tau || 'N/A' }}</strong></div>
          <div class="info-row"><span>Tên tàu:</span> <strong>{{ selectedBooking.ten_con_tau }}</strong></div>
          <div class="info-row"><span>Số chuyến:</span> <strong>{{ selectedBooking.so_chuyen }}</strong></div>
          <div class="info-row"><span>Giờ cắt máng:</span> <strong class="text-danger">{{ formatDateTime(selectedBooking.gio_cat_mang) }}</strong></div>
          <hr>
          <div class="info-row"><span>Cảng đi (POL):</span> <strong>{{ selectedBooking.ten_cang_di || '---' }}</strong></div>
          <div class="info-row"><span>Cảng đến (POD):</span> <strong>{{ selectedBooking.ten_cang_den || '---' }}</strong></div>
          <div class="info-row"><span>Ngày đi (ETD):</span> <strong class="text-primary">{{ formatDateTime(selectedBooking.etd) }}</strong></div>
          <div class="info-row"><span>Ngày đến (ETA):</span> <strong class="text-success">{{ formatDateTime(selectedBooking.eta) }}</strong></div>
        </div>

        <!-- Nội dung 2: Chi tiết hàng hóa -->
        <div v-if="viewType === 'items'" class="panel-body">
          <div v-if="isPanelLoading" style="text-align: center; padding: 20px;">Đang tải...</div>
          <div v-else-if="selectedItems.length === 0" style="text-align: center; padding: 20px;">Lô hàng trống</div>
          <div v-else>
             <table class="mini-table">
               <thead>
                 <tr>
                   <th>Tên hàng</th>
                   <th style="text-align: center;">SL</th>
                   <th style="text-align: right;">KL (kg)</th>
                 </tr>
               </thead>
               <tbody>
                 <tr v-for="item in selectedItems" :key="item.ma_chi_tiet_lo_hang">
                   <td>{{ item.ten_hang }}</td>
                   <td style="text-align: center;">{{ item.so_luong }} {{ item.ten_don_vi_tinh }}</td>
                   <td style="text-align: right;">{{ item.trong_luong }}</td>
                 </tr>
               </tbody>
             </table>
             <div style="margin-top: 15px; background: #fdfdfd; padding: 10px; border-radius: 4px; font-size: 13px;">
                <strong>Tổng kết sơ bộ:</strong><br>
                - Tổng số kiện: {{ selectedItems.reduce((a, b) => a + (b.so_kien || 0), 0) }}<br>
                - Tổng thể tích: {{ selectedItems.reduce((a, b) => a + (Number(b.the_tich) || 0), 0).toFixed(3) }} CBM
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
const listLoHang = ref([]);
const listKhachHang = ref([]);
const listBooking = ref([]);
const isLoading = ref(true);
const searchQuery = ref('');
const searchItemQuery = ref('');
const filterTrangThai = ref('ALL');
const filterKhachHang = ref(null);
const filterIncoterms = ref(null);
const filterUser = ref(null);
const filterHangHoa = ref(null);
const listHangHoa = ref([]);

// State cho Side Panel
const viewType = ref('none'); // 'none', 'booking', 'items'
const panelTitle = ref('');
const selectedItem = ref(null);
const selectedBooking = ref(null);
const selectedItems = ref([]);
const isPanelLoading = ref(false);

const currentPage = ref(1);
const pageSize = ref(10);
const pageSizes = [10, 20, 50];

const listTrangThai = ['Mới tạo', 'Đang chờ xử lý', 'Đang vận chuyển', 'Đã thông quan', 'Hoàn tất', 'Hủy'];

const statusClass = (status) => {
  if (['Hoàn tất', 'Đang vận chuyển', 'Đã thông quan'].includes(status)) return 'badge-active';
  if (status === 'Hủy') return 'badge-locked';
  return 'badge-warning';
};

// Hàm format ngày giờ hiển thị
const formatDateTime = (dateString) => {
  if (!dateString || dateString.startsWith('1970') || dateString.startsWith('0000')) return "--";
  const d = new Date(dateString);
  return `${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')} ${d.toLocaleDateString('vi-VN')}`;
};

const filteredLoHang = computed(() => {
  return listLoHang.value.filter(lh => {
    const search = searchQuery.value.toLowerCase();
    const matchSearch = !search || 
      (lh.ten_lo_hang && lh.ten_lo_hang.toLowerCase().includes(search)) || 
      (lh.ma_lo_hang && lh.ma_lo_hang.toString().includes(search)) ||
      (lh.nguon_goc && lh.nguon_goc.toLowerCase().includes(search));
      
    const itemSearch = searchItemQuery.value.toLowerCase();
    const matchItemSearch = !itemSearch || (lh.ds_ten_hang && lh.ds_ten_hang.toLowerCase().includes(itemSearch));

    const matchTrangThai = filterTrangThai.value === 'ALL' || lh.trang_thai_lo_hang === filterTrangThai.value;
    const matchKhachHang = !filterKhachHang.value || lh.ma_khach_hang === filterKhachHang.value;
    const matchIncoterms = !filterIncoterms.value || lh.dieu_kien_thuong_mai === filterIncoterms.value;
    const matchUser = !filterUser.value || (lh.nguoi_sua_cuoi === filterUser.value || lh.nguoi_sua_doi === filterUser.value);

    let matchHangHoa = true;
    if (filterHangHoa.value) {
      const ids = lh.ds_ma_hang_hoa ? String(lh.ds_ma_hang_hoa).split(',').map(Number) : [];
      matchHangHoa = ids.includes(Number(filterHangHoa.value));
    }

    return matchSearch && matchItemSearch && matchTrangThai && matchKhachHang && matchIncoterms && matchUser && matchHangHoa;
  });
});

const paginatedLoHang = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredLoHang.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredLoHang.value.length / pageSize.value) || 1;
});

const uniqueUsers = computed(() => {
  const users = listLoHang.value.map(lh => lh.nguoi_sua_cuoi || lh.nguoi_sua_doi).filter(Boolean);
  return [...new Set(users)];
});

const resetFilters = () => {
  searchQuery.value = '';
  filterTrangThai.value = 'ALL';
  filterKhachHang.value = null;
  filterIncoterms.value = null;
  filterUser.value = null;
  filterHangHoa.value = null;
  searchItemQuery.value = '';
  currentPage.value = 1;
  fetchData();
  fetchReferences();
};

// Logic Xem Booking
const showBookingInfo = (lh) => {
  selectedItem.value = lh;
  viewType.value = 'booking';
  panelTitle.value = '📦 Thông tin Booking';
  
  // Tìm booking trong list references
  const found = listBooking.value.find(b => b.ma_booking === lh.ma_booking);
  selectedBooking.value = found || null;
};

// Logic Xem Chi tiết hàng hóa
const showShipmentItems = async (lh) => {
  selectedItem.value = lh;
  viewType.value = 'items';
  panelTitle.value = '📋 Danh sách hàng hóa';
  isPanelLoading.value = true;
  
  try {
    const res = await fetch(`http://127.0.0.1:8000/api/chi-tiet-lo-hang?ma_lo_hang=${lh.ma_lo_hang}`);
    const data = await res.json();
    if (data.success) {
      selectedItems.value = data.data;
    } else {
      selectedItems.value = [];
    }
  } catch (error) {
    console.error("Lỗi lấy chi tiết hàng hóa");
    selectedItems.value = [];
  } finally {
    isPanelLoading.value = false;
  }
};

const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

watch([searchQuery, searchItemQuery, filterTrangThai, filterKhachHang, filterIncoterms, filterUser, filterHangHoa, pageSize], () => {
  currentPage.value = 1;
});

const fetchReferences = async () => {
  try {
    // 1. Lấy danh mục khách hàng
    const resRef = await fetch('http://127.0.0.1:8000/api/lo-hang/references');
    const dataRef = await resRef.json();
    if (dataRef.success) {
      listKhachHang.value = dataRef.khach_hang;
    }

    // 2. Lấy đầy đủ thông tin Booking (bao gồm cảng, hãng tàu, etd, eta...)
    const resBk = await fetch('http://127.0.0.1:8000/api/bookings');
    const dataBk = await resBk.json();
    if (dataBk.success) {
      listBooking.value = dataBk.data;
    }

    // 3. Lấy danh mục hàng hóa (phục vụ bộ lọc)
    const resHH = await fetch('http://127.0.0.1:8000/api/chi-tiet-lo-hang/references');
    const dataHH = await resHH.json();
    if (dataHH.success) listHangHoa.value = dataHH.hang_hoa;
  } catch (error) {
    console.error("Lỗi lấy dữ liệu tham chiếu");
  }
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lo-hang');
    const data = await res.json();
    if (data.success) listLoHang.value = data.data;
  } catch (error) { console.error("Lỗi lấy dữ liệu Lô hàng!"); }
  finally { isLoading.value = false; }
};

const handleDelete = async (id) => {
  if (!confirm("Bạn có chắc muốn xóa lô hàng này?")) return;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lo-hang/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ma_lo_hang: id, nguoi_sua_cuoi: user ? (user.id || user.ma_tai_khoan) : null })
    });
    const data = await res.json();
    if (data.success) { alert("Đã xóa lô hàng!"); fetchData(); }
    else { alert("Lỗi: " + data.message); }
  } catch (e) { alert("Lỗi kết nối!"); }
};

onMounted(() => { fetchData(); fetchReferences(); });
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.badge-warning { background-color: #f39c12; color: white; }
.btn-pagination {
  padding: 5px 12px; background: white; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; transition: 0.2s;
}
.btn-pagination:hover:not(:disabled) { background: #f0f0f0; border-color: #3498db; color: #3498db; }
.btn-pagination:disabled { cursor: not-allowed; opacity: 0.5; }

.view-btn {
  background: #ebf5fb; border: 1px solid #3498db; color: #3498db;
  border-radius: 4px; cursor: pointer; padding: 2px 6px; font-size: 12px;
}
.view-btn:hover { background: #3498db; color: white; }

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
.panel-header h4 { margin: 0; color: #2c3e50; }
.close-panel { background: none; border: none; cursor: pointer; font-size: 16px; color: #95a5a6; }

.panel-body { padding: 15px; flex: 1; overflow-y: auto; }
.info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
.info-row span { color: #7f8c8d; }

.mini-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.mini-table th { text-align: left; padding: 8px; background: #f1f3f5; border-bottom: 2px solid #dee2e6; }
.mini-table td { padding: 8px; border-bottom: 1px solid #eee; }

.row-selected { background-color: #f0f7ff !important; }

.action-btn.text-success { color: #27ae60; }
.action-btn.text-warning { color: #f39c12; }
</style>