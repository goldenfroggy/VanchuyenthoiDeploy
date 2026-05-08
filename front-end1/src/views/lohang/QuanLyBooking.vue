<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Booking Note</h3>
    
    <div class="toolbar" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="🔍 Tìm số Booking, Tên tàu, Số chuyến..."
          style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc;"
        >
      </div>

      <select 
        v-model="filterHangTau" 
        @mouseenter="loadReferences()" 
        style="padding: 10px; border-radius: 4px; border: 1px solid #ccc; width: 180px;"
      >
        <option value="ALL">🚢 Tất cả Hãng tàu</option>
        <option v-for="ht in listHangTau" :key="ht.ma_hang_tau" :value="ht.ma_hang_tau">
          {{ ht.ten_hang_tau }}
        </option>
      </select>

      <select v-model="filterCangDi" style="padding: 10px; border-radius: 4px; border: 1px solid #ccc; width: 180px;">
        <option :value="null">🛳️ Cảng Đi (Tất cả)</option>
        <option v-for="c in listCangBien" :key="c.ma_cang" :value="c.ma_cang">{{ c.ten_cang }}</option>
      </select>

      <select v-model="filterCangDen" style="padding: 10px; border-radius: 4px; border: 1px solid #ccc; width: 180px;">
        <option :value="null">⛴️ Cảng Đến (Tất cả)</option>
        <option v-for="c in listCangBien" :key="c.ma_cang" :value="c.ma_cang">{{ c.ten_cang }}</option>
      </select>

      <button 
        @click="resetFilters" 
        style="padding: 10px 15px; border: 1px solid #ccc; border-radius: 4px; background: #fff; cursor: pointer;"
        title="Xóa bộ lọc"
      >
        🧹 Xóa lọc
      </button>

      <button class="btn btn-success" @click="router.push('/lo-hang/booking/add')" style="padding: 10px 20px;">+ TẠO BOOKING NOTE MỚI</button>
    </div>

    <!-- Bộ lọc thời gian nâng cao -->
    <div class="filter-dates" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #e1e4e8; font-size: 13px;">
      <div style="display: flex; flex-direction: column; gap: 5px;">
        <label>⏰ Cut-off (Từ - Đến)</label>
        <div style="display: flex; gap: 5px;">
          <input type="date" v-model="dateFilters.cutoffStart" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
          <input type="date" v-model="dateFilters.cutoffEnd" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
        </div>
      </div>
      <div style="display: flex; flex-direction: column; gap: 5px;">
        <label>📅 ETD (Từ - Đến)</label>
        <div style="display: flex; gap: 5px;">
          <input type="date" v-model="dateFilters.etdStart" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
          <input type="date" v-model="dateFilters.etdEnd" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
        </div>
      </div>
      <div style="display: flex; flex-direction: column; gap: 5px;">
        <label>📅 ETA (Từ - Đến)</label>
        <div style="display: flex; gap: 5px;">
          <input type="date" v-model="dateFilters.etaStart" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
          <input type="date" v-model="dateFilters.etaEnd" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
        </div>
      </div>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu Booking Note...
    </div>

    <div v-else>
      <!-- Pagination Controls -->
      <div v-if="filteredBookings.length > 0" class="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding: 10px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e1e4e8;">
        <div style="display: flex; align-items: center; gap: 10px;">
          <span>Hiển thị</span>
          <select v-model="pageSize" style="padding: 5px 8px; border-radius: 5px; border: 1px solid #ccc;">
            <option v-for="size in pageSizes" :key="size" :value="size">{{ size }}</option>
          </select>
          <span>mục</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
          <button @click="currentPage--" :disabled="currentPage === 1" class="btn-pagination">◀</button>
          <span style="font-weight: bold;">Trang {{ currentPage }} / {{ totalPages }}</span>
          <button @click="currentPage++" :disabled="currentPage === totalPages" class="btn-pagination">▶</button>
        </div>
      </div>

      <div class="table-card">
      <table>
        <thead>
          <tr>
            <th style="width: 50px; text-align: center;">STT</th>
            <th>Số Booking</th>
            <th>Tên Tàu / Chuyến</th>
            <th>Hãng Tàu</th>
            <th>Cảng Đi (POL)</th>
            <th>Cảng Đến (POD)</th>
            <th>Cut-off (Cắt máng)</th>
            <th>ETD (Dự kiến đi)</th>
            <th>ETA (Dự kiến đến)</th>
            <th>Người sửa cuối</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(bk, index) in paginatedBookings" :key="bk.ma_booking">
            <td style="text-align: center; color: #7f8c8d;">{{ (currentPage - 1) * pageSize + index + 1 }}</td>
            <td class="fw-bold" style="color: #2980b9;">{{ bk.so_booking }}</td>
            <td>
              <strong>{{ bk.ten_con_tau || 'N/A' }}</strong><br>
              <span style="font-size: 12px; color: #7f8c8d;">{{ bk.so_chuyen || 'N/A' }}</span>
            </td>
            <td>{{ bk.ten_hang_tau || 'Chưa rõ' }}</td>
            <td class="fw-bold">{{ bk.ten_cang_di || '---' }}</td>
            <td class="fw-bold">{{ bk.ten_cang_den || '---' }}</td>
            <td>
              <span class="badge badge-warning" style="white-space: nowrap;">{{ formatDateTime(bk.gio_cat_mang) }}</span>
            </td>
            <td>
              <span class="badge" style="background-color: #3498db; color: white; white-space: nowrap;">{{ formatDateTime(bk.etd) }}</span>
            </td>
            <td>
              <span class="badge" style="background-color: #2ecc71; color: white; white-space: nowrap;">{{ formatDateTime(bk.eta) }}</span>
            </td>
            <td>{{ bk.nguoi_sua_doi || 'N/A' }}</td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="router.push('/lo-hang/booking/edit/' + bk.ma_booking)" title="Cập nhật">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(bk.ma_booking)" title="Xóa">🗑️</button>
            </td>
          </tr>
          <tr v-if="filteredBookings.length === 0">
            <td colspan="11" style="text-align: center; padding: 20px; color: #7f8c8d;">
              Không tìm thấy Booking nào!
            </td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const listBookings = ref([]);
const listCangBien = ref([]);
const listHangTau = ref([]);
const isLoading = ref(true);

const searchQuery = ref('');
const filterHangTau = ref('ALL');
const filterCangDi = ref(null);
const filterCangDen = ref(null);

const dateFilters = ref({
  etdStart: '', etdEnd: '',
  etaStart: '', etaEnd: '',
  cutoffStart: '', cutoffEnd: ''
});

const currentPage = ref(1);
const pageSize = ref(10);
const pageSizes = [10, 20, 50];

// Hàm format ngày giờ đẹp (VD: 15:30 12/05/2024)
const formatDateTime = (dateString) => {
  if (!dateString) return "--";
  const d = new Date(dateString);
  return `${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')} ${d.toLocaleDateString('vi-VN')}`;
};

// Lọc dữ liệu
const filteredBookings = computed(() => {
  return listBookings.value.filter(bk => {
    const search = searchQuery.value.toLowerCase();
    const matchSearch = !search || 
      bk.so_booking.toLowerCase().includes(search) || 
      (bk.ten_con_tau && bk.ten_con_tau.toLowerCase().includes(search)) ||
      (bk.so_chuyen && bk.so_chuyen.toLowerCase().includes(search));
      
    const matchHangTau = filterHangTau.value === 'ALL' || bk.ma_hang_tau === filterHangTau.value;
    const matchCangDi = !filterCangDi.value || bk.ma_cang_di === filterCangDi.value;
    const matchCangDen = !filterCangDen.value || bk.ma_cang_den === filterCangDen.value;

    // Lọc thời gian: Trước/Sau/Trong khoảng
    const checkRange = (val, start, end) => {
      if (!val) return true;
      const dateVal = new Date(val).setHours(0,0,0,0);
      if (start && dateVal < new Date(start).setHours(0,0,0,0)) return false;
      if (end && dateVal > new Date(end).setHours(0,0,0,0)) return false;
      return true;
    };

    const matchETD = checkRange(bk.etd, dateFilters.value.etdStart, dateFilters.value.etdEnd);
    const matchETA = checkRange(bk.eta, dateFilters.value.etaStart, dateFilters.value.etaEnd);
    const matchCutoff = checkRange(bk.gio_cat_mang, dateFilters.value.cutoffStart, dateFilters.value.cutoffEnd);

    return matchSearch && matchHangTau && matchCangDi && matchCangDen && matchETD && matchETA && matchCutoff;
  });
});

const paginatedBookings = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredBookings.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredBookings.value.length / pageSize.value) || 1;
});

const resetFilters = () => {
  searchQuery.value = '';
  filterHangTau.value = 'ALL';
  filterCangDi.value = null;
  filterCangDen.value = null;
  dateFilters.value = {
    etdStart: '', etdEnd: '',
    etaStart: '', etaEnd: '',
    cutoffStart: '', cutoffEnd: ''
  };
  currentPage.value = 1;
};

watch([searchQuery, filterHangTau, filterCangDi, filterCangDen, dateFilters, pageSize], () => {
  currentPage.value = 1;
});

const loadReferences = async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/api/bookings/references');
    const data = await res.json();
    if (data.success) {
      listCangBien.value = data.cang_bien;
      listHangTau.value = data.hang_tau;
    }
  } catch (error) {
    console.error("Lỗi lấy dữ liệu Cảng/Hãng tàu");
  }
};

const fetchBookings = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/bookings');
    const data = await res.json();
    if (data.success) listBookings.value = data.data;
  } catch (error) {
    console.error("Lỗi mạng!");
  } finally {
    isLoading.value = false;
  }
};

const handleDelete = async (id) => {
  if (!confirm("Hủy Booking này?")) return;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  
  try {
    const res = await fetch('http://127.0.0.1:8000/api/bookings/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        ma_booking: id,
        nguoi_sua_cuoi: user ? (user.id || user.ma_tai_khoan) : null 
      })
    });
    const data = await res.json();
    if (data.success) fetchBookings();
  } catch (e) {
    alert("Lỗi kết nối!");
  }
};

onMounted(() => {
  loadReferences(); // Gọi 1 lần lấy data Cảng và Hãng Tàu
  fetchBookings();
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.badge-warning { background-color: #f39c12; color: white; }
.btn-pagination {
  padding: 5px 12px; background: white; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;
}
.btn-pagination:disabled { opacity: 0.5; cursor: not-allowed; }
</style>