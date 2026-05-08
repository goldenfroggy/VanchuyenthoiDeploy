<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý người dùng</h3>
    
    <div class="toolbar">
      <div class="search-box">
        <input type="text" v-model="searchQuery" placeholder="Tìm kiếm tên, mã tài khoản...">
      </div>
      <button class="btn btn-success" @click="goToAdd">+ TẠO TÀI KHOẢN MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu...
    </div>

    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th>Mã TK</th>
            <th>Họ và Tên</th>
            <th>Email</th>
            <th>Chức vụ (Quyền)</th>
            <th>Ngày sinh</th>
            <th>Trạng thái</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="acc in filteredAccounts" :key="acc.ma_tai_khoan">
            <td class="fw-bold">{{ acc.ma_tai_khoan }}</td>
            <td class="fw-bold">{{ acc.ho_ten }}</td>
            <td>{{ acc.email }}</td>
            <td>{{ acc.ten_quyen || 'Chưa phân quyền' }}</td>
            <td>{{ formatDate(acc.ngay_sinh) }}</td>
            <td>
              <span class="badge" :class="acc.trang_thai === 'Hoạt động' ? 'badge-active' : 'badge-locked'">
                {{ acc.trang_thai }}
              </span>
            </td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="goToEdit(acc.ma_tai_khoan)" title="Sửa">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(acc.ma_tai_khoan)" title="Xóa">🗑️</button>
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
const listAccounts = ref([]);
const isLoading = ref(true);
const searchQuery = ref('');

const formatDate = (dateString) => {
  if (!dateString) return "Chưa cập nhật";
  const date = new Date(dateString);
  return date.toLocaleDateString('vi-VN');
};

const filteredAccounts = computed(() => {
  if (!searchQuery.value) return listAccounts.value;
  const search = searchQuery.value.toLowerCase();
  
  return listAccounts.value.filter(acc => {
    return (
      acc.ho_ten.toLowerCase().includes(search) || 
      acc.ma_tai_khoan.toString().includes(search) || 
      acc.email.toLowerCase().includes(search) || 
      (acc.ten_quyen && acc.ten_quyen.toLowerCase().includes(search))
    );
  });
});

const fetchAccounts = async () => {
  isLoading.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/accounts');
    const data = await response.json();
    if (data.success) {
      listAccounts.value = data.data;
    }
  } catch (error) {
    alert("Không thể kết nối API lấy danh sách!");
  } finally {
    isLoading.value = false;
  }
};

const goToAdd = () => {
  router.push('/he-thong/tai-khoan/add');
};

const goToEdit = (id) => {
  router.push(`/he-thong/tai-khoan/edit/${id}`);
};

// ĐÃ SỬA: Trỏ về API Laravel
const handleDelete = async (ma_tai_khoan) => {
  if (ma_tai_khoan === 1) return alert("Không được xóa tài khoản Admin hệ thống!");
  
  if (confirm('Bạn có chắc chắn muốn xóa nhân viên này không?')) {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/accounts/delete', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ma_tai_khoan: ma_tai_khoan })
      });
      const data = await response.json();
      if (data.success) {
        alert("Đã xóa thành công!");
        fetchAccounts();
      } else {
        alert("Lỗi từ server: " + data.message);
      }
    } catch (error) {
      alert("Lỗi khi xóa kết nối!");
    }
  }
};

onMounted(fetchAccounts);
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
/* Style bổ sung cho danh sách checkbox quyền */
.checkbox-group {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  background: #f8f9fa;
  padding: 12px;
  border-radius: 6px;
  border: 1px solid #ddd;
}
.checkbox-group label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: normal;
  cursor: pointer;
  margin-bottom: 0;
  font-size: 14px;
}
.checkbox-group input[type="checkbox"] {
  width: auto;
  margin: 0;
}
</style>
