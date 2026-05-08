<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Danh Mục Đơn Vị Tính</h3>
    
    <div class="toolbar" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; justify-content:space-between;">
      <div class="search-group" style="display:flex; flex-wrap:wrap; gap:12px; flex:1; min-width:0;">
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.ten_don_vi_tinh" placeholder="Tìm theo tên đơn vị tính...">
        </div>
        <div class="search-box" style="flex:0 0 auto; min-width:140px; display:flex; align-items:flex-end;">
          <button type="button" @click="clearFilters()" style="width:100%; background-color:#e74c3c; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer; font-weight:500;">Xóa bộ lọc</button>
        </div>
      </div>
      <button class="btn btn-success" @click="router.push('/danh-muc/don-vi-tinh/add')">+ TẠO ĐƠN VỊ TÍNH MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu...
    </div>

    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th>Mã Đơn Vị</th>
            <th>Tên Đơn Vị Tính</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="d in filteredData" :key="d.ma_don_vi_tinh">
            <td class="fw-bold">{{ d.ma_don_vi_tinh }}</td>
            <td class="fw-bold">{{ d.ten_don_vi_tinh }}</td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="router.push('/danh-muc/don-vi-tinh/edit/' + d.ma_don_vi_tinh)" title="Sửa">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(d.ma_don_vi_tinh)" title="Xóa">🗑️</button>
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
  ten_don_vi_tinh: ''
});

const filteredData = computed(() => {
  return listData.value.filter(item => {
    const tenMatch = !searchFilters.value.ten_don_vi_tinh || item.ten_don_vi_tinh.toLowerCase().includes(searchFilters.value.ten_don_vi_tinh.toLowerCase());

    return tenMatch;
  });
});

const fetchData = async () => {
  isLoading.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/don-vi-tinh');
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
    ten_don_vi_tinh: ''
  };
};

const handleDelete = async (ma_don_vi_tinh) => {
  if (confirm('Bạn có chắc chắn muốn xóa đơn vị tính này không?')) {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/don-vi-tinh/delete', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ma_don_vi_tinh: ma_don_vi_tinh })
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
