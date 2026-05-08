<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Danh Mục Kho Cảng</h3>
    
    <div class="toolbar" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; justify-content:space-between;">
      <div class="search-group" style="display:flex; flex-wrap:wrap; gap:12px; flex:1; min-width:0;">
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.ten_cang" placeholder="Tìm theo tên kho cảng...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.dia_chi" placeholder="Tìm theo địa chỉ...">
        </div>
        <div class="search-box" style="flex:0 0 auto; min-width:140px; display:flex; align-items:flex-end;">
          <button type="button" @click="clearFilters()" style="width:100%; background-color:#e74c3c; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer; font-weight:500;">Xóa bộ lọc</button>
        </div>
      </div>
      <button class="btn btn-success" @click="router.push('/danh-muc/kho-cang/add')">+ TẠO KHO CẢNG MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu...
    </div>

    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th>Mã Kho Cảng</th>
            <th>Tên Kho Cảng</th>
            <th>Địa chỉ</th>
            <th>Ghi chú</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in filteredData" :key="c.ma_cang">
            <td class="fw-bold">{{ c.ma_cang }}</td>
            <td class="fw-bold">{{ c.ten_cang }}</td>
            <td class="fw-bold">{{ c.dia_chi  || 'Chưa cập nhật' }}</td>
            <td>{{ c.ghi_chu || 'Không có' }}</td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="router.push('/danh-muc/kho-cang/edit/' + c.ma_cang)" title="Sửa">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(c.ma_cang)" title="Xóa">🗑️</button>
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
const listData = ref([]);
const isLoading = ref(true);
const searchFilters = ref({
  ten_cang: '',
  dia_chi: '' 
});

const filteredData = computed(() => {
  return listData.value.filter(item => {
    const tenMatch = !searchFilters.value.ten_cang || item.ten_cang.toLowerCase().includes(searchFilters.value.ten_cang.toLowerCase());
    const diaChiMatch = !searchFilters.value.dia_chi || (item.dia_chi && item.dia_chi.toLowerCase().includes(searchFilters.value.dia_chi.toLowerCase()));
    return tenMatch && diaChiMatch ;
  });
});

const fetchData = async () => {
  isLoading.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/cang-bien');
    const data = await response.json();
    if (data.success) {
      listData.value = data.data;
    }
  } catch (error) {
    alert("Không thể kết nối API lấy danh sách!");
  } finally {
    isLoading.value = false;
  }
};

const clearFilters = () => {
  searchFilters.value = {
    ten_cang: '',
    dia_chi: ''
  };
};

const handleDelete = async (ma_cang) => {
  if (confirm('Bạn có chắc chắn muốn xóa kho cảng này không?')) {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/cang-bien/delete', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ma_cang: ma_cang })
      });
      const data = await response.json();
      if (data.success) {
        alert("Đã xóa thành công!");
        fetchData();
      } else {
        alert("Lỗi từ server: " + data.message);
      }
    } catch (error) {
      alert("Lỗi khi xóa kết nối!");
    }
  }
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
