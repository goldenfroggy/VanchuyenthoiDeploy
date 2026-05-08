<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Danh Mục Khách Hàng</h3>
    
    <div class="toolbar" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; justify-content:space-between;">
      <div class="search-group" style="display:flex; flex-wrap:wrap; gap:12px; flex:1; min-width:0;">
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.ten_khach_hang" placeholder="Tìm theo tên khách hàng...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.dia_chi" placeholder="Tìm theo địa chỉ...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.so_dien_thoai" placeholder="Tìm theo điện thoại...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.so_fax" placeholder="Tìm theo fax...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <select v-model="searchFilters.nguoi_sua_cuoi">
            <option value="">Tất cả người sửa cuối</option>
            <option v-for="acc in accountOptions" :key="acc.ma_tai_khoan" :value="acc.ma_tai_khoan">
              {{ acc.ho_ten }}
            </option>
          </select>
        </div>
        <div class="search-box" style="flex:0 0 auto; min-width:140px; display:flex; align-items:flex-end;">
          <button type="button" @click="clearFilters()" style="width:100%; background-color:#e74c3c; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer; font-weight:500;">Xóa bộ lọc</button>
        </div>
      </div>
      <button class="btn btn-success" @click="router.push('/danh-muc/khach-hang/add')">+ TẠO KHÁCH HÀNG MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu...
    </div>

    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th>Mã KH</th>
            <th>Tên Khách Hàng</th>
            <th>Địa chỉ</th>
            <th>Điện thoại</th>
            <th>Fax</th>
            <th>Ghi chú</th>
            <th>Người sửa cuối</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="kh in filteredCustomers" :key="kh.ma_khach_hang">
            <td class="fw-bold">{{ kh.ma_khach_hang }}</td>
            <td class="fw-bold">{{ kh.ten_khach_hang }}</td>
            <td>{{ kh.dia_chi || 'Chưa cập nhật' }}</td>
            <td>{{ kh.so_dien_thoai || 'Chưa cập nhật' }}</td>
            <td>{{ kh.so_fax || 'Chưa cập nhật' }}</td>
            <td>{{ kh.ghi_chu || 'Không có' }}</td>
            <td>{{ kh.nguoi_sua_doi || 'N/A' }}</td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="router.push('/danh-muc/khach-hang/edit/' + kh.ma_khach_hang)" title="Sửa">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(kh.ma_khach_hang)" title="Xóa">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const listCustomers = ref([]);
const accountOptions = ref([]);
const isLoading = ref(true);
const searchFilters = ref({
  ten_khach_hang: '',
  dia_chi: '',
  so_dien_thoai: '',
  so_fax: '',
  nguoi_sua_cuoi: ''
});

const filteredCustomers = computed(() => {
  return listCustomers.value.filter(kh => {
    const tenMatch = !searchFilters.value.ten_khach_hang || kh.ten_khach_hang.toLowerCase().includes(searchFilters.value.ten_khach_hang.toLowerCase());
    const diaChiMatch = !searchFilters.value.dia_chi || (kh.dia_chi && kh.dia_chi.toLowerCase().includes(searchFilters.value.dia_chi.toLowerCase()));
    const dienthoaiMatch = !searchFilters.value.so_dien_thoai || (kh.so_dien_thoai && kh.so_dien_thoai.includes(searchFilters.value.so_dien_thoai));
    const faxMatch = !searchFilters.value.so_fax || (kh.so_fax && kh.so_fax.includes(searchFilters.value.so_fax));
    const nguoiSuaMatch = !searchFilters.value.nguoi_sua_cuoi || String(kh.nguoi_sua_cuoi) === String(searchFilters.value.nguoi_sua_cuoi);

    return tenMatch && diaChiMatch && dienthoaiMatch && faxMatch && nguoiSuaMatch;
  });
});

const fetchCustomers = async () => {
  isLoading.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/khach-hang');
    const data = await response.json();
    if (data.success) {
      listCustomers.value = data.data;
    }
  } catch (error) {
    console.error('Fetch error:', error);
    alert("Không thể kết nối API lấy danh sách!");
  } finally {
    isLoading.value = false;
  }
};

const fetchAccounts = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/accounts');
    const data = await response.json();
    if (data.success) {
      accountOptions.value = data.data;
    }
  } catch (error) {
    console.error('Fetch accounts error:', error);
  }
};

const clearFilters = () => {
  searchFilters.value = {
    ten_khach_hang: '',
    dia_chi: '',
    so_dien_thoai: '',
    so_fax: '',
    nguoi_sua_cuoi: ''
  };
};

const handleDelete = async (ma_khach_hang) => {
  if (confirm('Bạn có chắc chắn muốn xóa khách hàng này không?')) {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/khach-hang/delete', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ma_khach_hang: ma_khach_hang })
      });
      const data = await response.json();
      if (data.success) {
        alert("Đã xóa thành công!");
        fetchCustomers();
      } else {
        alert("Lỗi từ server: " + data.message);
      }
    } catch (error) {
      alert("Lỗi khi xóa kết nối!");
    }
  }
};

onMounted(() => {
  fetchCustomers();
  fetchAccounts();
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
