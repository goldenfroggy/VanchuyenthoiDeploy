<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản Lý Biên Bản Giao Nhận (BBGN)</h3>

    <div class="toolbar" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input type="text" v-model="searchQuery" placeholder="🔍 Tìm theo Số Booking, Tên lô hàng..." style="width: 100%; padding: 10px 15px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px;">
      </div>
      <button @click="fetchData()" class="btn-refresh" style="padding: 10px 20px; border-radius: 6px;">🔄 Làm mới</button>
      <button class="btn btn-success" @click="openModal()" style="border-radius: 6px; padding: 10px 20px; background: #2ecc71; color: white; border: none; cursor: pointer; font-weight: bold;">
        ➕ THÊM MỚI BIÊN BẢN
      </button>
    </div>

    <div class="table-container" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
          <tr>
            <th style="padding: 12px 15px; width: 100px;">Mã Phiếu</th>
            <th style="padding: 12px 15px;">Thuộc Lô Hàng</th>
            <th style="padding: 12px 15px;">Số Vận Đơn</th>
            <th style="padding: 12px 15px;">Số Container</th>
            <th style="padding: 12px 15px;">Đơn Vị Vận Tải</th>
            <th style="padding: 12px 15px;">Ngày Lập</th>
            <th style="padding: 12px 15px; text-align: center; width: 150px;">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading"><td colspan="7" style="text-align: center; padding: 20px;">Đang tải dữ liệu...</td></tr>
          <tr v-else-if="filteredList.length === 0"><td colspan="7" style="text-align: center; padding: 20px;">Không có dữ liệu!</td></tr>
          <tr v-for="item in filteredList" :key="item.ma_phieu" style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px 15px; font-weight: bold; color: #2980b9;">BB-{{ item.ma_phieu }}</td>
            <td style="padding: 12px 15px;">
              {{ item.ten_lo_hang || 'N/A' }}
              <button v-if="item.ten_lo_hang" @click="viewDetail('lo_hang', item)" class="btn-eye" title="Xem Lô hàng">👁️</button>
            </td>
            <td style="padding: 12px 15px; font-weight: bold;">{{ item.so_van_don || '---' }}</td>
            <td style="padding: 12px 15px;">{{ item.so_cont || 'N/A' }}</td>
            <td style="padding: 12px 15px; color: #e67e22; font-weight: bold;">{{ item.ten_hang_van_tai || 'Chưa điều xe' }}</td>
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
        <table v-if="detailPanel.type === 'lo_hang'" class="detail-table">
          <tbody>
            <tr><td>Số Booking:</td><td><strong>{{ detailPanel.data.so_booking }}</strong></td></tr>
            <tr><td>Tên Lô hàng:</td><td><strong>{{ detailPanel.data.ten_lo_hang }}</strong></td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content" style="max-width: 500px; padding: 20px; background: white; border-radius: 8px; width: 100%;">
        <h3 style="margin-top: 0;">{{ formData.ma_phieu ? 'Cập Nhật' : 'Thêm Mới' }} Biên Bản Giao Nhận</h3>
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

          <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Hãng Vận Tải (Nhà Xe) *</label>
            <select v-model="formData.ma_hang_van_tai" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background-color: #fff;">
              <option value="" disabled selected>-- Vui lòng chọn Hãng vận tải --</option>
              <option v-for="hang in listHangVanTai" :key="hang.ma_hang_van_tai" :value="hang.ma_hang_van_tai">{{ hang.ten_hang_van_tai }}</option>
            </select>
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Ngày lập *</label>
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

const listBBGN = ref([]); 
const listLoHang = ref([]);
const listHangVanTai = ref([]); 
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);
const searchQuery = ref(''); 

const detailPanel = ref({ show: false, type: '', title: '', data: {} });
const searchLoHangInput = ref('');
const isDropdownOpen = ref(false);

const formData = ref({ ma_phieu: null, ma_lo_hang: null, ma_hang_van_tai: '', ngay_phat_hanh: '' });

const formatDateTime = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleString('vi-VN');
};

const filteredList = computed(() => {
  return listBBGN.value.filter(item => {
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
  if (type === 'lo_hang') detailPanel.value.title = '📦 Thông tin Lô hàng';
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lenh-giao-hang');
    const data = await res.json();
    if (data.success) {
      listBBGN.value = data.data_bbgn; // Chỉ hứng data BBGN
      listLoHang.value = data.lo_hang;
      listHangVanTai.value = data.hang_van_tai;
    }
  } catch (error) { console.error("Lỗi!"); } finally { isLoading.value = false; }
};

const openModal = (item = null) => {
  if (item) {
    formData.value = { ma_phieu: item.ma_phieu, ma_lo_hang: item.ma_lo_hang, ma_hang_van_tai: item.ma_hang_van_tai || '', ngay_phat_hanh: item.ngay_phat_hanh ? new Date(item.ngay_phat_hanh).toISOString().slice(0, 16) : '' };
    const selectedLo = listLoHang.value.find(l => l.ma_lo_hang === item.ma_lo_hang);
    searchLoHangInput.value = selectedLo ? `[${selectedLo.so_booking}] - ${selectedLo.ten_lo_hang}` : '';
  } else {
    formData.value = { ma_phieu: null, ma_lo_hang: null, ma_hang_van_tai: '', ngay_phat_hanh: '' }; searchLoHangInput.value = '';
  }
  isDropdownOpen.value = false; isModalOpen.value = true;
};

const saveData = async () => {
  if (!formData.value.ma_lo_hang) { alert("Vui lòng chọn Lô hàng!"); return; }
  if (!formData.value.ma_hang_van_tai) { alert("Vui lòng chọn Hãng vận tải!"); return; }
  
  isSaving.value = true;
  try {
    const payload = { ...formData.value, loai: 'BBGN' }; // Gắn cứng loại BBGN
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
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ ma_phieu: id, loai: 'BBGN' })
    });
    if ((await res.json()).success) fetchData();
  } catch (e) { alert("Lỗi!"); }
};

const downloadPDF = async (id) => {
  try {
    const url = `http://127.0.0.1:8000/api/lenh-giao-hang/export-pdf?id=${id}&loai=BBGN`;
    const response = await fetch(url);
    if (!response.ok) throw new Error('Network error');
    const blob = await response.blob();
    const downloadUrl = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = downloadUrl; a.download = `BBGN_${id}.pdf`;
    document.body.appendChild(a); a.click(); a.remove(); window.URL.revokeObjectURL(downloadUrl);
  } catch (error) { alert("Lỗi xuất PDF!"); }
};

onMounted(fetchData);
</script>

<style scoped>
/* Copy y nguyên phần CSS chung vào đây */
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