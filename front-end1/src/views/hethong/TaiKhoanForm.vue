<template>
  <div class="tai-khoan-form-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h3 style="margin: 0; color: #2c3e50;">
        {{ formData.ma_tai_khoan ? '👤 Cập nhật tài khoản #' + formData.ma_tai_khoan : '👤 Thêm tài khoản mới' }}
      </h3>
    </div>

    <div class="table-card" style="padding: 30px; max-width: 600px; margin: 0 auto;">
      <form @submit.prevent="saveAccount">
        <div class="form-group">
          <label>Họ và Tên *</label>
          <input v-model="formData.ho_ten" required placeholder="Nhập tên nhân viên...">
        </div>
        <div class="form-group">
          <label>Email *</label>
          <input type="email" v-model="formData.email" required placeholder="email@sincere.com">
        </div>
        <div class="form-group">
          <label>Ngày sinh</label>
          <input type="date" v-model="formData.ngay_sinh">
        </div>
        <div class="form-group">
          <label>Mật khẩu {{ formData.ma_tai_khoan ? '(Để trống nếu không đổi)' : '*' }}</label>
          <input type="password" v-model="formData.mat_khau" :required="!formData.ma_tai_khoan">
        </div>
        <div class="form-group">
          <label>Quyền hệ thống</label>
          <div class="checkbox-group">
            <label><input type="checkbox" v-model="formData.ma_quyen" :value="1"> Chứng từ</label>
            <label><input type="checkbox" v-model="formData.ma_quyen" :value="2"> Kế toán</label>
            <label><input type="checkbox" v-model="formData.ma_quyen" :value="3"> Giao nhận</label>
            <label><input type="checkbox" v-model="formData.ma_quyen" :value="4"> Khai báo Hải quan</label>
          </div>
        </div>
        <div class="form-group">
          <label>Trạng thái</label>
          <select v-model="formData.trang_thai">
            <option value="Hoạt động">Hoạt động</option>
            <option value="Tạm khóa">Tạm khóa</option>
          </select>
        </div>

        <div class="modal-actions" style="border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px;">
          <button type="button" class="btn-cancel" @click="handleCancel">Quay lại</button>
          <button type="submit" class="btn-save" :disabled="isSaving">
             {{ isSaving ? 'Đang lưu...' : 'Lưu dữ liệu 💾' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const isSaving = ref(false);

const formData = ref({
  ma_tai_khoan: null,
  ho_ten: '',
  email: '',
  ngay_sinh: '', 
  mat_khau: '',
  ma_quyen: [],
  trang_thai: 'Hoạt động'
});

const fetchData = async (id) => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/accounts');
    const data = await response.json();
    if (data.success) {
      const acc = data.data.find(x => String(x.ma_tai_khoan) === String(id));
      if (acc) {
        formData.value = { 
          ...acc, 
          ma_quyen: acc.ds_ma_quyen ? acc.ds_ma_quyen.split(',').map(Number) : [],
          mat_khau: '' 
        };
      }
    }
  } catch (error) {
    alert("Lỗi khi tải thông tin tài khoản!");
  }
};

const saveAccount = async () => {
  isSaving.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/accounts/save', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'Accept': 'application/json' 
      },
      body: JSON.stringify(formData.value)
    });
    const data = await response.json();
    if (data.success) {
      alert(data.message);
      router.push('/he-thong/tai-khoan');
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (error) {
    alert("Lỗi kết nối máy chủ khi lưu!");
  } finally {
    isSaving.value = false;
  }
};

const handleCancel = () => {
  router.push('/he-thong/tai-khoan');
};

onMounted(() => {
  const id = route.params.id;
  if (id) {
    fetchData(id);
  }
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.tai-khoan-form-container {
  padding: 10px;
}
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
}
</style>
