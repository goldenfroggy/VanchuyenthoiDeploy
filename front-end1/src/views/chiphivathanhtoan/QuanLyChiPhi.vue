<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản Lý Chi Phí phát sinh</h3>

    <div class="dashboard-cards">
      <div class="card card-thu">
        <div class="card-title">💰 Tổng Thu Dự Kiến</div>
        <div class="card-value">{{ formatCurrency(dashboardStats.tong_thu) }}</div>
      </div>
      <div class="card card-chi">
        <div class="card-title">💸 Tổng Chi Dự Kiến</div>
        <div class="card-value">{{ formatCurrency(dashboardStats.tong_chi) }}</div>
      </div>
      <div class="card card-ton">
        <div class="card-title">⚠️ Đang Tồn Đọng</div>
        <div class="card-value">{{ formatCurrency(dashboardStats.ton_dong) }}</div>
      </div>
    </div>

    <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #ccc;">
      <button @click="currentTab = 'THU'" :style="tabStyle(currentTab === 'THU')">
        📥 KHOẢN THU (Khách hàng)
      </button>
      <button @click="currentTab = 'CHI'" :style="tabStyle(currentTab === 'CHI')">
        📤 KHOẢN CHI (Hãng tàu, Cảng...)
      </button>
    </div>

    <div class="toolbar" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
      <div class="search-box" style="flex: 1; min-width: 200px;">
        <input type="text" v-model="searchQuery" placeholder="🔍 Tìm theo Tên chi phí, Lô hàng..." style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
      </div>
      
      <select v-model="filterStatus" style="padding: 10px; border-radius: 6px; border: 1px solid #ccc; background: white;">
        <option value="">Tất cả trạng thái</option>
        <option value="Chưa thanh toán">🔴 Chưa thanh toán</option>
        <option value="Thanh toán một phần">🟡 Thanh toán một phần</option>
        <option value="Đã thanh toán">🟢 Đã thanh toán</option>
      </select>

      <button @click="fetchData()" class="btn-refresh" style="padding: 10px 20px; border-radius: 6px;">🔄 Làm mới</button>
      <button class="btn-success" @click="openModal()" style="border-radius: 6px; padding: 10px 20px; background: #2ecc71; color: white; border: none; cursor: pointer; font-weight: bold;">
        ➕ THÊM CHI PHÍ
      </button>
    </div>

    <div class="table-container" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
          <tr>
            <th style="padding: 12px 15px;">Mã CP</th>
            <th style="padding: 12px 15px;">Tên Chi Phí</th>
            <th style="padding: 12px 15px;">Thuộc Lô Hàng</th>
            <th style="padding: 12px 15px; text-align: right;">Tổng Tiền</th>
            <th style="padding: 12px 15px; text-align: center;">Trạng Thái</th>
            <th style="padding: 12px 15px;">Ngày TT</th>
            <th style="padding: 12px 15px;">Người cập nhật</th>
            <th style="padding: 12px 15px; text-align: center;">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td colspan="8" style="text-align: center; padding: 20px;">Đang tải dữ liệu...</td>
          </tr>
          <tr v-else-if="filteredList.length === 0">
            <td colspan="8" style="text-align: center; padding: 20px;">Không có dữ liệu!</td>
          </tr>
          <tr v-for="item in filteredList" :key="item.ma_chi_phi" style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px 15px; font-weight: bold; color: #7f8c8d;">CP-{{ item.ma_chi_phi }}</td>
            <td style="padding: 12px 15px; font-weight: bold;">{{ item.ten_chi_phi }}</td>
            
            <td style="padding: 12px 15px;">
              {{ item.ten_lo_hang || 'N/A' }}
              <button v-if="item.ten_lo_hang" @click="viewDetail(item)" class="btn-eye" title="Xem chi tiết Lô hàng">👁️</button>
            </td>
            
            <td style="padding: 12px 15px; text-align: right; font-weight: bold; color: #2c3e50;">
              {{ formatCurrency(item.tong_tien) }}
            </td>
            
            <td style="padding: 12px 15px; text-align: center;">
              <span :class="'badge ' + getStatusClass(item.trang_thai_thanh_toan)">
                {{ item.trang_thai_thanh_toan || 'Chưa xác định' }}
              </span>
            </td>
            
            <td style="padding: 12px 15px; font-weight: bold;">
                {{ item.ngay_thanh_toan ? formatDate(item.ngay_thanh_toan) : '---' }}
            </td>

            <td style="padding: 12px 15px; color: #7f8c8d; font-size: 13px;">
            👤 {{ item.nguoi_cap_nhat || '---' }}
            </td>
            
            <td style="padding: 12px 15px; text-align: center;">
              <button @click="openModal(item)" style="margin-right: 10px; cursor: pointer; border: none; background: none; font-size: 16px;" title="Sửa">✏️</button>
              <button @click="handleDelete(item.ma_chi_phi)" style="cursor: pointer; border: none; background: none; font-size: 16px;" title="Xóa">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="detailPanel.show" class="side-panel">
      <div class="panel-header">
        <h4>📦 Thông tin Lô hàng</h4>
        <button @click="detailPanel.show = false" class="close-btn">✖</button>
      </div>
      <div class="panel-body">
        <table class="detail-table">
          <tbody>
            <tr><td>Tên Lô hàng:</td><td><strong>{{ detailPanel.data.ten_lo_hang }}</strong></td></tr>
            <tr><td>Số Booking:</td><td><strong>{{ detailPanel.data.so_booking || '---' }}</strong></td></tr>
            <tr><td>Khách hàng:</td><td><strong>{{ detailPanel.data.ten_khach_hang || '---' }}</strong></td></tr>
            <tr><td>Điều kiện TM:</td><td><strong>{{ detailPanel.data.dieu_kien_thuong_mai || '---' }}</strong></td></tr>
            <tr><td>Trạng thái lô:</td><td><strong style="color: #f39c12;">{{ detailPanel.data.trang_thai_lo_hang || '---' }}</strong></td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content" style="max-width: 500px; padding: 20px; background: white; border-radius: 8px; width: 100%;">
        <h3 style="margin-top: 0;">{{ formData.ma_chi_phi ? 'Cập Nhật' : 'Ghi Nhận' }} Chi Phí Phát Sinh</h3>
        
        <form @submit.prevent="saveData">
          <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Loại giao dịch *</label>
            <div style="display: flex; gap: 20px;">
              <label><input type="radio" v-model="formData.loai_giao_dich" value="THU" required> Khoản Thu</label>
              <label><input type="radio" v-model="formData.loai_giao_dich" value="CHI" required> Khoản Chi</label>
            </div>
          </div>

          <div style="margin-bottom: 15px; position: relative;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Thuộc Lô hàng *</label>
            <div v-if="isDropdownOpen" @click="isDropdownOpen = false" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9;"></div>
            <div style="position: relative; z-index: 10;">
              <input type="text" v-model="searchLoHangInput" @focus="isDropdownOpen = true" @input="isDropdownOpen = true" placeholder="🔍 Gõ số booking hoặc tên lô hàng..." required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
              
              <div v-if="isDropdownOpen" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ccc; border-radius: 4px; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-top: 2px;">
                <div v-if="filteredLoHangOptions.length === 0" style="padding: 10px; text-align: center; color: #999;">Không tìm thấy kết quả</div>
                <div v-for="lo in filteredLoHangOptions" :key="lo.ma_lo_hang" @click="selectLoHang(lo)" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">
                  <strong style="color: #2980b9;">[{{ lo.so_booking }}]</strong> - {{ lo.ten_lo_hang }}
                </div>
              </div>
            </div>
          </div>

          <div style="display: flex; gap: 15px; margin-bottom: 25px;">
            <div style="flex: 1;">
              <label style="display: block; margin-bottom: 5px; font-weight: bold;">Tên chi phí *</label>
              <input type="text" v-model="formData.ten_chi_phi" required placeholder="VD: Phí THC, Lưu bãi..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>
            <div style="flex: 1;">
              <label style="display: block; margin-bottom: 5px; font-weight: bold;">Tổng tiền (VNĐ) *</label>
              <input type="number" v-model="formData.tong_tien" required min="0" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>
          </div>

          <div style="text-align: right;">
            <button type="button" @click="isModalOpen = false" style="padding: 8px 15px; margin-right: 10px; cursor: pointer; border: 1px solid #ccc; background: #fff; border-radius: 4px;">Hủy</button>
            <button type="submit" :disabled="isSaving" style="padding: 8px 15px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">
              {{ isSaving ? 'Đang lưu...' : 'Lưu lại' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';

const currentTab = ref('THU'); 
const listChiPhi = ref([]);
const listLoHang = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);

const searchQuery = ref(''); 
const filterStatus = ref('');

const dashboardStats = ref({ tong_thu: 0, tong_chi: 0, ton_dong: 0 });

const detailPanel = ref({ show: false, data: {} });

const searchLoHangInput = ref('');
const isDropdownOpen = ref(false);

const formData = ref({
  ma_chi_phi: null,
  ma_lo_hang: null,
  ten_chi_phi: '',
  tong_tien: 0,
  trang_thai_thanh_toan: 'Chưa thanh toán', // Luôn gán cứng mặc định khi tạo mới
  ngay_thanh_toan: null,
  loai_giao_dich: 'THU'
});

// Format Tiền tệ
const formatCurrency = (val) => {
  if (!val) return '0 ₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
};

// Format Ngày
const formatDate = (dateStr) => {
  if (!dateStr) return '---';
  return new Date(dateStr).toLocaleDateString('vi-VN');
};

const tabStyle = (isActive) => ({
  padding: '10px 20px', border: 'none', background: 'transparent', fontWeight: 'bold', fontSize: '16px', cursor: 'pointer',
  borderBottom: isActive ? '3px solid #3498db' : '3px solid transparent',
  color: isActive ? '#3498db' : '#7f8c8d'
});

// Lấy class CSS cho Badge Trạng Thái
const getStatusClass = (status) => {
  if (status === 'Đã thanh toán') return 'badge-success';
  if (status === 'Thanh toán một phần') return 'badge-warning';
  return 'badge-danger';
};

// Logic lọc dữ liệu
const filteredList = computed(() => {
  return listChiPhi.value.filter(item => {
    // 1. Lọc theo Tab (THU / CHI)
    const matchTab = item.loai_giao_dich === currentTab.value;
    
    // 2. Lọc theo Ô tìm kiếm
    const search = searchQuery.value.toLowerCase();
    const matchSearch = !search || 
      (item.ten_chi_phi && item.ten_chi_phi.toLowerCase().includes(search)) || 
      (item.ten_lo_hang && item.ten_lo_hang.toLowerCase().includes(search));

    // 3. Lọc theo Combo Box Trạng Thái
    const matchStatus = !filterStatus.value || item.trang_thai_thanh_toan === filterStatus.value;

    return matchTab && matchSearch && matchStatus;
  });
});

const filteredLoHangOptions = computed(() => {
  if (!searchLoHangInput.value) return listLoHang.value;
  const term = searchLoHangInput.value.toLowerCase();
  return listLoHang.value.filter(lo => 
    (lo.ten_lo_hang && lo.ten_lo_hang.toLowerCase().includes(term)) || 
    (lo.so_booking && lo.so_booking.toLowerCase().includes(term))
  );
});

const selectLoHang = (lo) => {
  formData.value.ma_lo_hang = lo.ma_lo_hang; 
  searchLoHangInput.value = `[${lo.so_booking}] - ${lo.ten_lo_hang}`; 
  isDropdownOpen.value = false; 
};

watch(searchLoHangInput, (newVal) => { if (newVal === '') formData.value.ma_lo_hang = null; });

// Mở Side Panel Xem Lô Hàng
const viewDetail = (item) => {
  detailPanel.value.data = item;
  detailPanel.value.show = true;
};

// Gọi API lấy dữ liệu
const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/chi-phi');
    const data = await res.json();
    if (data.success) {
      listChiPhi.value = data.data;
      listLoHang.value = data.lo_hang;
      dashboardStats.value = data.thong_ke;
    }
  } catch (error) { console.error("Lỗi lấy dữ liệu!"); }
  finally { isLoading.value = false; }
};

// Modal Logic
const openModal = (item = null) => {
  if (item) {
    formData.value = { ...item };
    // Tìm lô hàng để map lên text combobox
    const selectedLo = listLoHang.value.find(l => l.ma_lo_hang === item.ma_lo_hang);
    searchLoHangInput.value = selectedLo ? `[${selectedLo.so_booking}] - ${selectedLo.ten_lo_hang}` : '';
  } else {
    // Khi thêm mới, mặc định là Chưa thanh toán
    formData.value = { ma_chi_phi: null, ma_lo_hang: null, ten_chi_phi: '', tong_tien: 0, trang_thai_thanh_toan: 'Chưa thanh toán', ngay_thanh_toan: null, loai_giao_dich: currentTab.value };
    searchLoHangInput.value = '';
  }
  isDropdownOpen.value = false;
  isModalOpen.value = true;
};

// Lưu Dữ Liệu
const saveData = async () => {
  if (!formData.value.ma_lo_hang) { alert("Vui lòng chọn Lô hàng!"); return; }
  
  // Đảm bảo nếu chưa thanh toán thì ngày = null
  if (formData.value.trang_thai_thanh_toan === 'Chưa thanh toán') {
      formData.value.ngay_thanh_toan = null;
  }

  // Lấy ID người dùng đang đăng nhập
  const currentUser = JSON.parse(localStorage.getItem('sincere_user'));
  const userId = currentUser ? currentUser.ma_tai_khoan : null;

  isSaving.value = true;
  try {
    const payload = { ...formData.value, nguoi_sua_cuoi: userId }; 
    
    const res = await fetch('http://127.0.0.1:8000/api/chi-phi/save', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) { isModalOpen.value = false; fetchData(); } else { alert(data.message); }
  } catch (e) { alert("Lỗi Server!"); }
  finally { isSaving.value = false; }
};

// Xóa Dữ Liệu
const handleDelete = async (id) => {
  if (!confirm(`Bạn có chắc muốn xóa khoản chi phí này?`)) return;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/chi-phi/delete', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ ma_chi_phi: id })
    });
    if ((await res.json()).success) fetchData();
  } catch (e) { alert("Lỗi!"); }
};

// Nút thao tác nhanh Đã thanh toán (Nghiệp vụ 5.2)
const markAsPaid = async (id) => {
  if (!confirm("Xác nhận khoản tiền này ĐÃ ĐƯỢC THANH TOÁN?")) return;
  
  const currentUser = JSON.parse(localStorage.getItem('sincere_user'));
  const userId = currentUser ? currentUser.ma_tai_khoan : null;

  try {
    const today = new Date().toISOString().split('T')[0];
    const res = await fetch('http://127.0.0.1:8000/api/chi-phi/update-status', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, 
      body: JSON.stringify({ 
        ma_chi_phi: id, 
        trang_thai_thanh_toan: 'Đã thanh toán', 
        ngay_thanh_toan: today,
        nguoi_sua_cuoi: userId // Gắn tên kế toán vừa ấn nút thu tiền
      })
    });
    if ((await res.json()).success) fetchData();
  } catch (e) { alert("Lỗi!"); }
};

onMounted(fetchData);
</script>

<style scoped>
/* CSS CHO DASHBOARD CARDS */
.dashboard-cards { display: flex; gap: 20px; margin-bottom: 25px; }
.card { flex: 1; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); color: white; }
.card-title { font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; opacity: 0.9; }
.card-value { font-size: 24px; font-weight: bold; }
.card-thu { background: linear-gradient(135deg, #2ecc71, #27ae60); }
.card-chi { background: linear-gradient(135deg, #e74c3c, #c0392b); }
.card-ton { background: linear-gradient(135deg, #f1c40f, #f39c12); }

/* BADGES TRẠNG THÁI */
.badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
.badge-success { background: #d4efdf; color: #27ae60; }
.badge-danger { background: #fadbd8; color: #c0392b; }
.badge-warning { background: #fcf3cf; color: #d35400; }

/* CSS Modal & Panel Tái Sử Dụng */
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.btn-eye { background: #f0f4f8; border: 1px solid #dcdde1; border-radius: 4px; cursor: pointer; padding: 3px 6px; font-size: 12px; margin-left: 8px; }
.side-panel { position: fixed; top: 90px; right: 20px; width: 350px; background: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); border: 1px solid #eee; z-index: 100; animation: slideIn 0.3s ease-out; }
.panel-header { display: flex; justify-content: space-between; padding: 15px; border-bottom: 1px solid #f1f2f6; background: #fafbfc; border-radius: 8px 8px 0 0; }
.panel-header h4 { margin: 0; color: #2c3e50; font-size: 16px; }
.close-btn { background: none; border: none; font-size: 16px; cursor: pointer; color: #7f8c8d; }
.panel-body { padding: 15px; }
.detail-table { width: 100%; border-collapse: collapse; }
.detail-table tr { border-bottom: 1px dashed #ecf0f1; }
.detail-table td { padding: 10px 0; color: #7f8c8d; font-size: 13px; width: 40%;}
.detail-table strong { font-size: 13px; color: #2c3e50; padding-left: 10px;}
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>