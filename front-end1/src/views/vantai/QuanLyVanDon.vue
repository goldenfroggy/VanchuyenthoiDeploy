<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Vận đơn</h3>
    
    <div class="toolbar" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
      <!-- Nhóm tìm kiếm văn bản -->
      <div style="display: flex; gap: 10px; width: 100%; flex-wrap: wrap;">
        <div class="search-box" style="flex: 1; min-width: 200px;">
          <input type="text" v-model="searchSoVanDon" placeholder="🔍 Tìm theo số vận đơn..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc;">
        </div>
        <div class="search-box" style="flex: 1; min-width: 200px;">
          <input type="text" v-model="searchSoVanDonGoc" placeholder="🔍 Tìm theo số vận đơn gốc..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc;">
        </div>
        <div class="search-box" style="flex: 1; min-width: 200px;">
          <input type="text" v-model="searchTenLoHang" placeholder="🔍 Tìm theo tên lô hàng..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc;">
        </div>
      </div>

      <!-- Nhóm bộ lọc select và date -->
      <select v-model="filterLoaiVanDon" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc; width: 180px;">
        <option value="ALL">📄 Loại B/L (Tất cả)</option>
        <option v-for="type in listLoaiVanDon" :key="type" :value="type">{{ type }}</option>
      </select>

      <select v-model="filterCangDi" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc; width: 160px;">
        <option :value="null">⚓ Cảng đi (Tất cả)</option>
        <option v-for="c in listCangBien" :key="c.ma_cang" :value="c.ma_cang">{{ c.ten_cang }}</option>
      </select>

      <select v-model="filterCangDen" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc; width: 160px;">
        <option :value="null">⚓ Cảng đến (Tất cả)</option>
        <option v-for="c in listCangBien" :key="c.ma_cang" :value="c.ma_cang">{{ c.ten_cang }}</option>
      </select>

      <div style="display: flex; align-items: center; gap: 8px; background: #fff; padding: 0 10px; border: 1px solid #ccc; border-radius: 6px;">
        <label style="font-size: 13px; color: #666; white-space: nowrap;">Ngày phát hành:</label>
        <input type="date" v-model="filterNgayPH" style="padding: 7px; border: none; outline: none;">
      </div>

      <select v-model="filterUser" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc; width: 160px;">
        <option :value="null">👤 Người sửa (Tất cả)</option>
        <option v-for="user in uniqueUsers" :key="user" :value="user">{{ user }}</option>
      </select>

      <button 
        @click="resetFilters" 
        style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 6px; background: #fff; cursor: pointer; transition: 0.2s;"
        title="Xóa lọc"
      >
        🧹 Xóa lọc
      </button>

      <button class="btn btn-success" @click="router.push('/van-tai/quan-ly-van-don/add')" style="border-radius: 6px;">+ TẠO VẬN ĐƠN MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu Vận đơn...
    </div>

    <div v-else style="display: flex; gap: 20px; align-items: flex-start;">
      <!-- BÊN TRÁI: DANH SÁCH VẬN ĐƠN -->
      <div style="flex: 1; min-width: 0;">
        <!-- Kiểm soát phân trang -->
        <div v-if="listVanDon.length > 0" class="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e1e4e8;">
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
                <th>Mã</th>
                <th>Số Vận Đơn</th>
                <th>Số Vận Đơn Gốc</th>
                <th>Loại Vận Đơn</th>
                <th>Lô hàng</th>
                <th>Hành trình (POL ➔ POD)</th>
                <th>Ngày PH</th>
                <th>Cont / Seal</th>
                <th>Người sửa</th>
                <th style="text-align: center;">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(vd, index) in paginatedVanDon" :key="vd.ma_van_don" :class="{ 'row-selected': (selectedItem?.ma_van_don === vd.ma_van_don) }">
                <td style="text-align: center; color: #7f8c8d;">{{ (currentPage - 1) * pageSize + index + 1 }}</td>
                <td class="fw-bold">{{ vd.ma_van_don }}</td>
                <td class="fw-bold" style="color: #2980b9;">{{ vd.so_van_don }}</td>
                <td>{{ vd.so_van_don_goc || '---' }}</td>
                <td><span class="badge badge-active" style="white-space: nowrap;">{{ vd.loai_van_don }}</span></td>
                <td class="fw-bold">{{ vd.ten_lo_hang || '---' }}</td>
                <td><strong>{{ vd.ten_cang_di || 'N/A' }}</strong> ➔ <strong>{{ vd.ten_cang_den || 'N/A' }}</strong></td>
                <td>{{ formatDate(vd.ngay_phat_hanh) }}</td>
                <td>
                  <span style="font-size: 11px;">
                    C: {{ vd.so_cont || 'N/A' }}<br>S: {{ vd.so_chi || 'N/A' }}
                  </span>
                </td>
                <td>{{ vd.nguoi_sua_doi || 'N/A' }}</td>
                <td style="text-align: center;">
                  <div style="display: grid; grid-template-columns: repeat(2, 35px); gap: 5px; justify-content: center; margin: 0 auto; width: fit-content;">
                    <button class="action-btn-no-mg text-success" @click="showShipmentInfo(vd)" title="Xem thông tin lô hàng">📋</button>
                    <button class="action-btn-no-mg text-warning" @click="handlePrintPDF(vd.ma_van_don)" title="Xuất PDF">🖨️</button>
                    <button class="action-btn-no-mg text-primary" @click="router.push('/van-tai/quan-ly-van-don/edit/' + vd.ma_van_don)" title="Sửa">✏️</button>
                    <button class="action-btn-no-mg text-danger" @click="handleDelete(vd.ma_van_don)" title="Xóa">🗑️</button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredVanDon.length === 0">
                <td colspan="11" style="text-align: center; padding: 20px; color: #7f8c8d;">
                  Không tìm thấy vận đơn nào!
                </td>
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
const listVanDon = ref([]);
const listCangBien = ref([]);
const isLoading = ref(true);
const searchSoVanDon = ref('');
const searchSoVanDonGoc = ref('');
const searchTenLoHang = ref('');
const filterLoaiVanDon = ref('ALL');
const filterCangDi = ref(null);
const filterCangDen = ref(null);
const filterNgayPH = ref('');
const filterUser = ref(null);

// State cho Side Panel
const viewType = ref('none');
const panelTitle = ref('');
const selectedItem = ref(null);
const selectedShipment = ref(null);
const isPanelLoading = ref(false);

// Phân trang
const currentPage = ref(1);
const pageSize = ref(10);
const pageSizes = [10, 20, 50];

const listLoaiVanDon = ['Original B/L', 'Surrendered B/L', 'Seaway Bill'];

const formatDate = (dateString) => {
  if (!dateString) return "---";
  return new Date(dateString).toLocaleDateString('vi-VN');
};

const filteredVanDon = computed(() => {
  return listVanDon.value.filter(vd => {
    const matchSoVD = !searchSoVanDon.value || (vd.so_van_don && vd.so_van_don.toLowerCase().includes(searchSoVanDon.value.toLowerCase()));
    const matchSoVDGoc = !searchSoVanDonGoc.value || (vd.so_van_don_goc && vd.so_van_don_goc.toLowerCase().includes(searchSoVanDonGoc.value.toLowerCase()));
    const matchTenLH = !searchTenLoHang.value || (vd.ten_lo_hang && vd.ten_lo_hang.toLowerCase().includes(searchTenLoHang.value.toLowerCase()));
    const matchMa = !searchSoVanDon.value || (vd.ma_van_don && vd.ma_van_don.toString().includes(searchSoVanDon.value));

    const matchLoai = filterLoaiVanDon.value === 'ALL' || vd.loai_van_don === filterLoaiVanDon.value;
    const matchCangDi = !filterCangDi.value || vd.ma_cang_di === filterCangDi.value;
    const matchCangDen = !filterCangDen.value || vd.ma_cang_den === filterCangDen.value;
    const matchNgayPH = !filterNgayPH.value || (vd.ngay_phat_hanh && vd.ngay_phat_hanh.includes(filterNgayPH.value));
    const matchUser = !filterUser.value || (vd.nguoi_sua_cuoi === filterUser.value || vd.nguoi_sua_doi === filterUser.value);

    return (matchSoVD || matchMa) && matchSoVDGoc && matchTenLH && 
           matchLoai && matchCangDi && matchCangDen && matchNgayPH && matchUser;
  });
});

const paginatedVanDon = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredVanDon.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(filteredVanDon.value.length / pageSize.value) || 1);

const uniqueUsers = computed(() => {
  const users = listVanDon.value.map(vd => vd.nguoi_sua_doi).filter(Boolean);
  return [...new Set(users)];
});

const resetFilters = () => {
  searchSoVanDon.value = '';
  searchSoVanDonGoc.value = '';
  searchTenLoHang.value = '';
  filterLoaiVanDon.value = 'ALL';
  filterCangDi.value = null;
  filterCangDen.value = null;
  filterNgayPH.value = '';
  filterUser.value = null;
  currentPage.value = 1;
};

const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

const showShipmentInfo = async (vd) => {
  selectedItem.value = vd;
  viewType.value = 'shipment';
  panelTitle.value = '📦 Thông tin lô hàng: ' + vd.ten_lo_hang;
  isPanelLoading.value = true;
  try {
    const res = await fetch(`http://127.0.0.1:8000/api/lo-hang`);
    const data = await res.json();
    if (data.success) {
      selectedShipment.value = data.data.find(lh => lh.ma_lo_hang === vd.ma_lo_hang);
    } else {
      selectedShipment.value = null;
    }
  } catch (error) {
    selectedShipment.value = null;
  } finally {
    isPanelLoading.value = false;
  }
};

const handlePrintPDF = (id) => {
  window.open(`http://127.0.0.1:8000/api/van-don/export-pdf/${id}`, '_blank');
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/van-don');
    const data = await res.json();
    if (data.success) listVanDon.value = data.data;
  } catch (error) { console.error("Lỗi lấy dữ liệu Vận đơn!"); }
  finally { isLoading.value = false; }
};

const fetchReferences = async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/api/van-don/references');
    const data = await res.json();
    if (data.success) {
      listCangBien.value = data.cang_bien;
    }
  } catch (error) {
    console.error("Lỗi lấy danh mục!");
  }
};

const handleDelete = async (id) => {
  if (!confirm("Bạn có chắc muốn xóa vận đơn này?")) return;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  try {
    const res = await fetch('http://127.0.0.1:8000/api/van-don/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ma_van_don: id, nguoi_sua_cuoi: user ? (user.id || user.ma_tai_khoan) : null })
    });
    const data = await res.json();
    if (data.success) { alert("Đã xóa vận đơn!"); fetchData(); }
    else { alert("Lỗi xóa: " + data.message); }
  } catch (e) { alert("Lỗi kết nối!"); }
};

watch([searchSoVanDon, searchSoVanDonGoc, searchTenLoHang, filterLoaiVanDon, filterCangDi, filterCangDen, filterNgayPH, filterUser, pageSize], () => {
  currentPage.value = 1;
});

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
.mini-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.mini-table th { text-align: left; padding: 8px; background: #f1f3f5; border-bottom: 2px solid #dee2e6; }
.mini-table td { padding: 8px; border-bottom: 1px solid #eee; }

.info-list { display: flex; flex-direction: column; gap: 15px; }
.info-item { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #eee; padding-bottom: 8px; font-size: 14px; }
.info-item strong { color: #7f8c8d; font-weight: normal; }
.info-item span { color: #2c3e50; font-weight: 600; text-align: right; }

.row-selected { background-color: #f0f7ff !important; }
.action-btn.text-success { color: #27ae60; }

.action-btn-no-mg { background: none; border: none; cursor: pointer; font-size: 16px; transition: 0.2s; padding: 0; margin: 0; display: flex; align-items: center; justify-content: center; height: 35px; width: 35px; }
.action-btn-no-mg:hover { transform: scale(1.2); }
.text-warning { color: #f39c12; }
</style>