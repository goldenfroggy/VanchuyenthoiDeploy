<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản Lý Thông Báo Hàng Đến (Arrival Notice)</h3>

    <div class="toolbar" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input type="text" v-model="searchQuery" placeholder="🔍 Tìm theo Số Booking, Tên lô hàng..." style="width: 100%; padding: 10px 15px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px;">
      </div>
      <button @click="fetchData()" class="btn-refresh" style="padding: 10px 20px; border-radius: 6px;">🔄 Làm mới</button>
      <button class="btn btn-success" @click="openModal()" style="border-radius: 6px; padding: 10px 20px; background: #2ecc71; color: white; border: none; cursor: pointer; font-weight: bold;">
        ➕ THÊM MỚI THÔNG BÁO
      </button>
    </div>

    <div class="table-container" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
          <tr>
            <th style="padding: 12px 15px; width: 100px;">Mã Phiếu</th>
            <th style="padding: 12px 15px;">Thuộc Lô Hàng</th>
            <th style="padding: 12px 15px;">Số Booking</th>
            <th style="padding: 12px 15px;">Khách Hàng</th>
            <th style="padding: 12px 15px;">Tên Tàu</th>
            <th style="padding: 12px 15px;">Số Vận Đơn</th>
            <th style="padding: 12px 15px;">Ngày Phát Hành</th>
            <th style="padding: 12px 15px; text-align: center; width: 150px;">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading"><td colspan="8" style="text-align: center; padding: 20px;">Đang tải dữ liệu...</td></tr>
          <tr v-else-if="filteredList.length === 0"><td colspan="8" style="text-align: center; padding: 20px;">Không có dữ liệu!</td></tr>
          <tr v-for="item in filteredList" :key="item.ma_phieu" style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px 15px; font-weight: bold; color: #2980b9;">TB-{{ item.ma_phieu }}</td>
            <td style="padding: 12px 15px;">
              {{ item.ten_lo_hang || 'N/A' }}
              <button v-if="item.ten_lo_hang" @click="viewDetail('lo_hang', item)" class="btn-eye" title="Xem Lô hàng">👁️</button>
            </td>
            <td style="padding: 12px 15px;">
              {{ item.so_booking || 'N/A' }}
              <button v-if="item.so_booking" @click="viewDetail('booking', item)" class="btn-eye" title="Xem Booking">👁️</button>
            </td>
            <td style="padding: 12px 15px;">{{ item.ten_khach_hang || 'N/A' }}</td>
            <td style="padding: 12px 15px;">{{ item.ten_con_tau || 'N/A' }}</td>
            <td style="padding: 12px 15px; font-weight: bold;">
              {{ item.so_van_don || '---' }}
              <button v-if="item.so_van_don" @click="viewDetail('van_don', item)" class="btn-eye" title="Xem Vận đơn">👁️</button>
            </td>
            <td style="padding: 12px 15px;">{{ formatDateTime(item.ngay_phat_hanh) }}</td>
            <td style="padding: 12px 15px; text-align: center;">
              <button @click="downloadPDF(item.ma_phieu)" style="margin-right: 10px; cursor: pointer; border: none; background: none; font-size: 16px;" title="Xuất PDF">📄</button>
              <button @click="openModal(item)" style="margin-right: 10px; cursor: pointer; border: none; background: none; font-size: 16px;" title="Sửa">✏️</button>
              <button @click="handleDelete(item.ma_phieu)" style="cursor: pointer; border: none; background: none; font-size: 16px;" title="Xóa">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="detailPanel.show" class="side-panel">
      <div class="panel-header">
        <h4>{{ detailPanel.title }}</h4>
        <button @click="detailPanel.show = false" class="close-btn">✖</button>
      </div>
     <div class="panel-body">
        <table v-if="detailPanel.type === 'booking'" class="detail-table">
          <tbody>
            <tr><td>Số Booking:</td><td><strong>{{ detailPanel.data.so_booking }}</strong></td></tr>
            <tr><td>Tên tàu:</td><td><strong>{{ detailPanel.data.ten_con_tau || '---' }}</strong></td></tr>
            <tr><td>Ngày đi (ETD):</td><td><strong>{{ formatDateTime(detailPanel.data.etd) }}</strong></td></tr>
            <tr><td>Ngày đến (ETA):</td><td><strong>{{ formatDateTime(detailPanel.data.eta) }}</strong></td></tr>
          </tbody>
        </table>
        <table v-if="detailPanel.type === 'lo_hang'" class="detail-table">
          <tbody>
            <tr><td>Tên Lô hàng:</td><td><strong>{{ detailPanel.data.ten_lo_hang }}</strong></td></tr>
            <tr><td>Khách hàng:</td><td><strong>{{ detailPanel.data.ten_khach_hang || '---' }}</strong></td></tr>
          </tbody>
        </table>
        <table v-if="detailPanel.type === 'van_don'" class="detail-table">
          <tbody>
            <tr><td>Số HBL:</td><td><strong>{{ detailPanel.data.so_van_don || '---' }}</strong></td></tr>
            <tr><td>Số Container:</td><td><strong>{{ detailPanel.data.so_cont || '---' }}</strong></td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content" style="max-width: 500px; padding: 20px; background: white; border-radius: 8px; width: 100%;">
        <h3 style="margin-top: 0;">{{ formData.ma_phieu ? 'Cập Nhật' : 'Thêm Mới' }} Thông Báo Hàng Đến</h3>
        <form @submit.prevent="saveData">
          <div style="margin-bottom: 15px; position: relative;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Thuộc Lô hàng *</label>
            <div v-if="isDropdownOpen" @click="isDropdownOpen = false" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9;"></div>
            <div style="position: relative; z-index: 10;">
              <input type="text" v-model="searchLoHangInput" @focus="isDropdownOpen = true" @input="isDropdownOpen = true" placeholder="🔍 Gõ số booking hoặc tên lô hàng..." required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
              <div v-if="isDropdownOpen" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ccc; border-radius: 4px; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-top: 2px;">
                <div v-for="lo in filteredLoHangOptions" :key="lo.ma_lo_hang" @click="selectLoHang(lo)" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">
                  <strong style="color: #2980b9;">[{{ lo.so_booking }}]</strong> - {{ lo.ten_lo_hang }}
                </div>
              </div>
            </div>
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Ngày phát hành *</label>
            <input type="datetime-local" v-model="formData.ngay_phat_hanh" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
          </div>

          <div style="text-align: right;">
            <button type="button" @click="isModalOpen = false" style="padding: 8px 15px; margin-right: 10px; cursor: pointer; border: 1px solid #ccc; background: #fff; border-radius: 4px;">Hủy</button>
            <button type="submit" :disabled="isSaving" style="padding: 8px 15px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">{{ isSaving ? 'Đang lưu...' : 'Lưu lại' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';

const listAN = ref([]);
const listLoHang = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);
const searchQuery = ref(''); 

const detailPanel = ref({ show: false, type: '', title: '', data: {} });
const searchLoHangInput = ref('');
const isDropdownOpen = ref(false);

const formData = ref({ ma_phieu: null, ma_lo_hang: null, ngay_phat_hanh: '' });

const formatDateTime = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleString('vi-VN');
};

const filteredList = computed(() => {
  return listAN.value.filter(item => {
    const search = searchQuery.value.toLowerCase();
    return !search || (item.ten_lo_hang && item.ten_lo_hang.toLowerCase().includes(search)) || (item.so_booking && item.so_booking.toLowerCase().includes(search));
  });
});

const filteredLoHangOptions = computed(() => {
  if (!searchLoHangInput.value) return listLoHang.value;
  const term = searchLoHangInput.value.toLowerCase();
  return listLoHang.value.filter(lo => (lo.ten_lo_hang && lo.ten_lo_hang.toLowerCase().includes(term)) || (lo.so_booking && lo.so_booking.toLowerCase().includes(term)));
});

const selectLoHang = (lo) => {
  formData.value.ma_lo_hang = lo.ma_lo_hang; 
  searchLoHangInput.value = `[${lo.so_booking}] - ${lo.ten_lo_hang}`; 
  isDropdownOpen.value = false; 
};

watch(searchLoHangInput, (newVal) => { if (newVal === '') formData.value.ma_lo_hang = null; });

const viewDetail = (type, item) => {
  detailPanel.value.type = type; detailPanel.value.data = item; detailPanel.value.show = true;
  if (type === 'booking') detailPanel.value.title = '🚢 Thông tin Booking';
  if (type === 'lo_hang') detailPanel.value.title = '📦 Thông tin Lô hàng';
  if (type === 'van_don') detailPanel.value.title = '📑 Thông tin Vận đơn';
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lenh-giao-hang');
    const data = await res.json();
    if (data.success) {
      listAN.value = data.data_an; // Chỉ hứng cục data_an
      listLoHang.value = data.lo_hang;
    }
  } catch (error) { console.error("Lỗi!"); } finally { isLoading.value = false; }
};

const openModal = (item = null) => {
  if (item) {
    formData.value = { ma_phieu: item.ma_phieu, ma_lo_hang: item.ma_lo_hang, ngay_phat_hanh: item.ngay_phat_hanh ? new Date(item.ngay_phat_hanh).toISOString().slice(0, 16) : '' };
    const selectedLo = listLoHang.value.find(l => l.ma_lo_hang === item.ma_lo_hang);
    searchLoHangInput.value = selectedLo ? `[${selectedLo.so_booking}] - ${selectedLo.ten_lo_hang}` : '';
  } else {
    formData.value = { ma_phieu: null, ma_lo_hang: null, ngay_phat_hanh: '' }; searchLoHangInput.value = '';
  }
  isDropdownOpen.value = false; isModalOpen.value = true;
};

const saveData = async () => {
  if (!formData.value.ma_lo_hang) { alert("Vui lòng chọn Lô hàng hợp lệ!"); return; }
  isSaving.value = true;
  try {
    const payload = { ...formData.value, loai: 'AN' }; // Gắn chết cứng loại là AN
    const res = await fetch('http://127.0.0.1:8000/api/lenh-giao-hang/save', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
    });
    if ((await res.json()).success) { isModalOpen.value = false; fetchData(); }
  } catch (e) { alert("Lỗi!"); } finally { isSaving.value = false; }
};

const handleDelete = async (id) => {
  if (!confirm(`Hủy vĩnh viễn phiếu này?`)) return;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lenh-giao-hang/delete', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ ma_phieu: id, loai: 'AN' })
    });
    if ((await res.json()).success) fetchData();
  } catch (e) { alert("Lỗi!"); }
};

const downloadPDF = async (id) => {
  try {
    const url = `http://127.0.0.1:8000/api/lenh-giao-hang/export-pdf?id=${id}&loai=AN`;
    const response = await fetch(url);
    if (!response.ok) throw new Error('Network response was not ok');
    const blob = await response.blob();
    const downloadUrl = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = downloadUrl; a.download = `AN_${id}.pdf`;
    document.body.appendChild(a); a.click(); a.remove(); window.URL.revokeObjectURL(downloadUrl);
  } catch (error) { alert("Lỗi xuất PDF!"); }
};

onMounted(fetchData);
</script>

<style scoped>
/* Copy y nguyên phần CSS cũ vào đây, bỏ qua đoạn này cho gọn nhé */
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.btn-eye { background: #f0f4f8; border: 1px solid #dcdde1; border-radius: 4px; cursor: pointer; padding: 3px 6px; font-size: 12px; margin-left: 8px; }
.btn-eye:hover { background: #3498db; border-color: #3498db; }
.side-panel { position: fixed; top: 90px; right: 20px; width: 350px; background: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); border: 1px solid #eee; z-index: 100; animation: slideIn 0.3s ease-out; }
.panel-header { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #f1f2f6; background: #fafbfc; border-radius: 8px 8px 0 0; }
.panel-header h4 { margin: 0; color: #2c3e50; font-size: 16px; }
.close-btn { background: none; border: none; font-size: 16px; cursor: pointer; color: #7f8c8d; }
.panel-body { padding: 15px; }
.detail-table { width: 100%; border-collapse: collapse; }
.detail-table tr { border-bottom: 1px dashed #ecf0f1; }
.detail-table td { padding: 10px 0; color: #7f8c8d; font-size: 13px; width: 40%;}
.detail-table strong { font-size: 13px; color: #2c3e50; display: table-cell; padding-left: 10px;}
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>