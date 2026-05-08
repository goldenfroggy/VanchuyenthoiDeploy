<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Danh Mục Hàng Hóa</h3>
    
    <div class="toolbar" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; justify-content:space-between;">
      <div class="search-group" style="display:flex; flex-wrap:wrap; gap:12px; flex:1; min-width:0;">
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.ten_hang_hoa" placeholder="Tìm theo tên hàng hóa...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.hs_code" placeholder="Tìm theo HS Code...">
        </div>
        <div class="search-box" style="flex:0 0 auto; min-width:140px; display:flex; align-items:flex-end;">
          <button type="button" @click="clearFilters()" style="width:100%; background-color:#e74c3c; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer; font-weight:500;">Xóa bộ lọc</button>
        </div>
      </div>
      <button class="btn btn-success" @click="router.push('/danh-muc/hang-hoa/add')">+ TẠO HÀNG HÓA MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu...
    </div>

    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th>Mã Hàng</th>
            <th>Tên Hàng Hóa</th>
            <th>HS Code</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="h in filteredData" :key="h.ma_hang_hoa">
            <td class="fw-bold">{{ h.ma_hang_hoa }}</td>
            <td class="fw-bold">{{ h.ten_hang_hoa }}</td>
            <td>{{ h.hs_code || '-' }}</td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="router.push('/danh-muc/hang-hoa/edit/' + h.ma_hang_hoa)" title="Sửa">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(h.ma_hang_hoa)" title="Xóa">🗑️</button>
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
  ten_hang_hoa: '',
  hs_code: ''
});

const filteredData = computed(() => {
  return listData.value.filter(item => {
    const tenMatch = !searchFilters.value.ten_hang_hoa || item.ten_hang_hoa.toLowerCase().includes(searchFilters.value.ten_hang_hoa.toLowerCase());
    const codeMatch = !searchFilters.value.hs_code || (item.hs_code && item.hs_code.includes(searchFilters.value.hs_code));

    return tenMatch && codeMatch;
  });
});

const fetchData = async () => {
  isLoading.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/hang-hoa');
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
    ten_hang_hoa: '',
    hs_code: '' 
  };
};

const handleDelete = async (ma_hang_hoa) => {
  if (confirm('Bạn có chắc chắn muốn xóa hàng hóa này không?')) {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/hang-hoa/delete', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ma_hang_hoa: ma_hang_hoa })
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
