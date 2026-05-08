<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản Lý Trạng Thái Thanh Toán</h3>

    <div class="dashboard-cards">
      <div class="card card-chuathanhtoan">
        <div class="card-title">🔴 Tổng Công Nợ (Chưa TT)</div>
        <div class="card-value">{{ formatCurrency(dashboardStats.tong_chua_thanh_toan) }}</div>
      </div>
      <div class="card card-dathanhtoan">
        <div class="card-title">🟢 Tổng Đã Thanh Toán</div>
        <div class="card-value">{{ formatCurrency(dashboardStats.tong_da_thanh_toan) }}</div>
      </div>
    </div>

    <div class="toolbar" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
      <div class="search-box" style="flex: 1; min-width: 200px;">
        <input type="text" v-model="searchQuery" placeholder="🔍 Tìm theo Mã CP, Tên chi phí, Lô hàng..." style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
      </div>
      
      <select v-model="filterLoaiGiaoDich" style="padding: 10px; border-radius: 6px; border: 1px solid #ccc; background: white;">
        <option value="">Tất cả Loại GD</option>
        <option value="THU">Khoản Thu</option>
        <option value="CHI">Khoản Chi</option>
      </select>

      <select v-model="filterStatus" style="padding: 10px; border-radius: 6px; border: 1px solid #ccc; background: white;">
        <option value="">Tất cả trạng thái</option>
        <option value="Chưa thanh toán">🔴 Chưa thanh toán</option>
        <option value="Thanh toán một phần">🟡 Thanh toán một phần</option>
        <option value="Đã thanh toán">🟢 Đã thanh toán</option>
      </select>

      <button @click="fetchData()" class="btn-refresh" style="padding: 10px 20px; border-radius: 6px;">🔄 Làm mới</button>
      <button @click="exportExcel" class="btn-excel" style="border-radius: 6px; padding: 10px 20px; background: #27ae60; color: white; border: none; cursor: pointer; font-weight: bold;">
        📊 Xuất Excel
      </button>
    </div>

    <div class="table-container" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
          <tr>
            <th style="padding: 12px 15px;">Mã CP</th>
            <th style="padding: 12px 15px;">Tên Chi Phí</th>
            <th style="padding: 12px 15px;">Thuộc Lô Hàng</th>
            <th style="padding: 12px 15px; text-align: center;">Loại GD</th>
            <th style="padding: 12px 15px; text-align: right;">Tổng Tiền</th>
            <th style="padding: 12px 15px; text-align: center;">Trạng Thái</th>
            <th style="padding: 12px 15px;">Ngày TT</th>
            <th style="padding: 12px 15px;">Người cập nhật</th>
            <th style="padding: 12px 15px; text-align: center;">Thao Tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td colspan="9" style="text-align: center; padding: 20px;">Đang tải dữ liệu...</td>
          </tr>
          <tr v-else-if="filteredList.length === 0">
            <td colspan="9" style="text-align: center; padding: 20px;">Không có dữ liệu!</td>
          </tr>
          <tr v-for="item in filteredList" :key="item.ma_chi_phi" style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px 15px; font-weight: bold; color: #7f8c8d;">CP-{{ item.ma_chi_phi }}</td>
            <td style="padding: 12px 15px; font-weight: bold;">{{ item.ten_chi_phi }}</td>
            
            <td style="padding: 12px 15px;">
              {{ item.ten_lo_hang || 'N/A' }}
              <button v-if="item.ten_lo_hang" @click="viewDetail(item)" class="btn-eye" title="Xem chi tiết Lô hàng">👁️</button>
            </td>
            
            <td style="padding: 12px 15px; text-align: center;">
              <span :class="item.loai_giao_dich === 'THU' ? 'text-success' : 'text-danger'" style="font-weight: bold;">
                {{ item.loai_giao_dich === 'THU' ? 'THU 📥' : 'CHI 📤' }}
              </span>
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
              <button 
                @click="openPaymentModal(item)" 
                class="btn-action"
                title="Cập nhật Trạng thái Thanh toán"
              >
                Cập nhật
              </button>
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

    <div v-if="isPaymentModalOpen" class="modal-overlay">
      <div class="modal-content" style="max-width: 450px; padding: 25px; background: white; border-radius: 8px; width: 100%;">
        <h3 style="margin-top: 0; color: #2c3e50; text-align: center; border-bottom: 2px solid #eee; padding-bottom: 10px;">
          Cập Nhật Thanh Toán
        </h3>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
          <p style="margin: 0 0 5px 0;"><strong>Chi phí:</strong> {{ paymentData.ten_chi_phi }} (CP-{{ paymentData.ma_chi_phi }})</p>
          <p style="margin: 0 0 5px 0;"><strong>Thuộc Lô:</strong> {{ paymentData.ten_lo_hang || 'N/A' }}</p>
          <p style="margin: 0; font-size: 16px;"><strong>Tổng tiền:</strong> <span style="color: #e74c3c;">{{ formatCurrency(paymentData.tong_tien) }}</span></p>
        </div>

        <form @submit.prevent="submitPayment">
          <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Trạng thái cập nhật *</label>
            <select v-model="paymentData.trang_thai_thanh_toan" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
              <option value="Chưa thanh toán">🔴 Chưa thanh toán</option>
              <option value="Thanh toán một phần">🟡 Thanh toán một phần</option>
              <option value="Đã thanh toán">🟢 Đã thanh toán</option>
            </select>
          </div>

          <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Ngày thực tế giao dịch</label>
            <input 
              type="date" 
              v-model="paymentData.ngay_thanh_toan" 
              :required="paymentData.trang_thai_thanh_toan !== 'Chưa thanh toán'"
              :disabled="paymentData.trang_thai_thanh_toan === 'Chưa thanh toán'"
              style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"
              :style="{ backgroundColor: paymentData.trang_thai_thanh_toan === 'Chưa thanh toán' ? '#f0f0f0' : 'white' }"
            >
            <small v-if="paymentData.trang_thai_thanh_toan === 'Chưa thanh toán'" style="color: #7f8c8d; display: block; margin-top: 5px;">
              * Không áp dụng ngày khi chưa thanh toán
            </small>
          </div>

          <div style="text-align: right; display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" @click="isPaymentModalOpen = false" style="padding: 10px 15px; cursor: pointer; border: 1px solid #ccc; background: #fff; border-radius: 4px;">Hủy bỏ</button>
            <button type="submit" :disabled="isSaving" style="padding: 10px 15px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
              {{ isSaving ? 'Đang lưu...' : 'Lưu Thay Đổi' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const listChiPhi = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);

const searchQuery = ref(''); 
const filterStatus = ref('');
const filterLoaiGiaoDich = ref('');

const detailPanel = ref({ show: false, data: {} });

const isPaymentModalOpen = ref(false);
const paymentData = ref({
  ma_chi_phi: null,
  ten_chi_phi: '',
  ten_lo_hang: '',
  tong_tien: 0,
  trang_thai_thanh_toan: '',
  ngay_thanh_toan: ''
});

// Hàm định dạng tiền tệ
const formatCurrency = (val) => {
  if (!val) return '0 ₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
};

// Hàm định dạng ngày
const formatDate = (dateStr) => {
  if (!dateStr) return '---';
  return new Date(dateStr).toLocaleDateString('vi-VN');
};

// Hàm lấy class cho Badge Trạng Thái
const getStatusClass = (status) => {
  if (status === 'Đã thanh toán') return 'badge-success';
  if (status === 'Thanh toán một phần') return 'badge-warning';
  return 'badge-danger';
};

// Tính toán Thống kê trên màn hình
const dashboardStats = computed(() => {
  let tong_chua_thanh_toan = 0;
  let tong_da_thanh_toan = 0;

  listChiPhi.value.forEach(item => {
    if (item.trang_thai_thanh_toan === 'Đã thanh toán') {
      tong_da_thanh_toan += Number(item.tong_tien);
    } else {
      tong_chua_thanh_toan += Number(item.tong_tien);
    }
  });

  return { tong_chua_thanh_toan, tong_da_thanh_toan };
});

// Hàm lọc dữ liệu hiển thị trên bảng
const filteredList = computed(() => {
  return listChiPhi.value.filter(item => {
    const search = searchQuery.value.toLowerCase();
    const matchSearch = !search || 
      (item.ten_chi_phi && item.ten_chi_phi.toLowerCase().includes(search)) || 
      (item.ten_lo_hang && item.ten_lo_hang.toLowerCase().includes(search)) ||
      (`cp-${item.ma_chi_phi}`).includes(search);

    const matchLoaiGiaoDich = !filterLoaiGiaoDich.value || item.loai_giao_dich === filterLoaiGiaoDich.value;
    const matchStatus = !filterStatus.value || item.trang_thai_thanh_toan === filterStatus.value;

    return matchSearch && matchLoaiGiaoDich && matchStatus;
  });
});

// GỌI VÀO API BACKEND MỚI
const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/trang-thai-thanh-toan');
    const data = await res.json();
    if (data.success) {
      listChiPhi.value = data.data;
    }
  } catch (error) { 
    console.error("Lỗi lấy dữ liệu!", error); 
  } finally { 
    isLoading.value = false; 
  }
};

const viewDetail = (item) => {
  detailPanel.value.data = item;
  detailPanel.value.show = true;
};

const openPaymentModal = (item) => {
  paymentData.value = { 
    ma_chi_phi: item.ma_chi_phi,
    ten_chi_phi: item.ten_chi_phi,
    ten_lo_hang: item.ten_lo_hang,
    tong_tien: item.tong_tien,
    trang_thai_thanh_toan: item.trang_thai_thanh_toan,
    ngay_thanh_toan: item.ngay_thanh_toan ? item.ngay_thanh_toan : new Date().toISOString().split('T')[0]
  };
  isPaymentModalOpen.value = true;
};

// GỬI LỆNH UPDATE VÀO API BACKEND MỚI
const submitPayment = async () => {
  let finalDate = paymentData.value.ngay_thanh_toan;
  if (paymentData.value.trang_thai_thanh_toan === 'Chưa thanh toán') {
    finalDate = null;
  }

  const currentUser = JSON.parse(localStorage.getItem('sincere_user'));
  const userId = currentUser ? currentUser.ma_tai_khoan : null;

  isSaving.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/trang-thai-thanh-toan/update-status', {
      method: 'POST', 
      headers: { 'Content-Type': 'application/json' }, 
      body: JSON.stringify({ 
        ma_chi_phi: paymentData.value.ma_chi_phi, 
        trang_thai_thanh_toan: paymentData.value.trang_thai_thanh_toan, 
        ngay_thanh_toan: finalDate,
        nguoi_sua_cuoi: userId 
      })
    });
    const data = await res.json();
    if (data.success) {
      isPaymentModalOpen.value = false;
      fetchData(); 
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (e) { 
    alert("Lỗi kết nối Server!"); 
  } finally {
    isSaving.value = false;
  }
};

// Hàm xuất dữ liệu ra Excel
const exportExcel = () => {
  if (filteredList.value.length === 0) {
    alert("Không có dữ liệu để xuất!");
    return;
  }
  
  let csvContent = "data:text/csv;charset=utf-8,\uFEFF"; 
  csvContent += "Mã Chi Phí,Tên Chi Phí,Lô Hàng,Loại Giao Dịch,Tổng Tiền,Trạng Thái,Ngày Thanh Toán,Người Cập Nhật\r\n"; 

  filteredList.value.forEach(row => {
    let tr = [
      `CP-${row.ma_chi_phi}`,
      `"${row.ten_chi_phi || ''}"`,
      `"${row.ten_lo_hang || ''}"`,
      row.loai_giao_dich,
      row.tong_tien,
      row.trang_thai_thanh_toan,
      row.ngay_thanh_toan ? formatDate(row.ngay_thanh_toan) : '',
      `"${row.nguoi_cap_nhat || ''}"`
    ];
    csvContent += tr.join(",") + "\r\n";
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", "Lich_Su_Thanh_Toan.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(fetchData);
</script>

<style scoped>
/* CSS Dashboard Cards */
.dashboard-cards { display: flex; gap: 20px; margin-bottom: 25px; }
.card { flex: 1; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); color: white; }
.card-title { font-size: 15px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; opacity: 0.9; }
.card-value { font-size: 26px; font-weight: bold; }
.card-chuathanhtoan { background: linear-gradient(135deg, #e74c3c, #c0392b); }
.card-dathanhtoan { background: linear-gradient(135deg, #2ecc71, #27ae60); }

/* Text Colors */
.text-success { color: #27ae60; }
.text-danger { color: #e74c3c; }

/* Badges */
.badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; display: inline-block;}
.badge-success { background: #d4efdf; color: #27ae60; }
.badge-danger { background: #fadbd8; color: #c0392b; }
.badge-warning { background: #fcf3cf; color: #d35400; }

/* Button Action */
.btn-action { background: #f1f2f6; border: 1px solid #dcdde1; border-radius: 4px; cursor: pointer; padding: 6px 12px; font-size: 13px; font-weight: bold; color: #34495e; transition: all 0.2s;}
.btn-action:hover { background: #3498db; color: white; border-color: #3498db;}

/* Modal & Side Panel (Tái sử dụng) */
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