<template>
  <div class="profile-card">
    <div class="profile-header">
      <div class="avatar-large">{{ initials }}</div>
      <h2>{{ userData.ho_ten }}</h2>
      <p>{{ userData.ten_quyen || 'Thành viên' }}</p>
    </div>

    <div class="profile-body">
      <div class="info-row">
        <span class="info-label">Mã tài khoản:</span>
        <span class="info-value">#{{ userData.ma_tai_khoan }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Họ và Tên:</span>
        <span class="info-value">{{ userData.ho_ten }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Ngày sinh:</span>
        <span class="info-value">{{ formatDate(userData.ngay_sinh) }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Email liên hệ:</span>
        <span class="info-value">{{ userData.email || 'Chưa cập nhật' }}</span>
      </div>

      <div class="profile-actions">
        <button class="btn btn-edit-profile" @click="openEditModal">
          📝 CHỈNH SỬA HỒ SƠ CÁ NHÂN
        </button>
        <button class="btn btn-change-password" @click="openPassModal">
          🔐 ĐỔI MẬT KHẨU TÀI KHOẢN
        </button>
      </div>
    </div>

    <div v-if="isEditModalOpen" class="modal-overlay">
      <div class="modal-content">
        <h3>Chỉnh sửa hồ sơ</h3>
        <form @submit.prevent="saveProfile">
          <div class="form-group">
            <label>Họ và Tên</label>
            <input v-model="editData.ho_ten" required placeholder="Nhập họ tên...">
          </div>
          <div class="form-group">
            <label>Ngày sinh</label>
            <input type="date" v-model="editData.ngay_sinh">
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" v-model="editData.email" required placeholder="email@example.com">
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isEditModalOpen = false">Hủy</button>
            <button type="submit" class="btn-save" :disabled="isSaving">
              {{ isSaving ? 'Đang lưu...' : 'Lưu thay đổi' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="isPassModalOpen" class="modal-overlay">
      <div class="modal-content">
        <h3>Đổi mật khẩu</h3>
        <form @submit.prevent="handleChangePassword">
          <div class="form-group">
            <label>Mật khẩu hiện tại</label>
            <input type="password" v-model="passData.current_pass" required placeholder="******">
          </div>
          <div class="form-group">
            <label>Mật khẩu mới</label>
            <input type="password" v-model="passData.new_pass" required placeholder="Tối thiểu 6 ký tự">
          </div>
          <div class="form-group">
            <label>Xác nhận mật khẩu mới</label>
            <input type="password" v-model="passData.confirm_pass" required placeholder="Nhập lại mật khẩu mới">
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isPassModalOpen = false">Hủy</button>
            <button type="submit" class="btn-save" :disabled="isSaving">
              {{ isSaving ? 'Đang xử lý...' : 'Cập nhật mật khẩu' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

// --- BIẾN DỮ LIỆU ---
const userData = ref({}); 
const editData = ref({}); 
const initials = ref('U');
const isSaving = ref(false);

// --- BIẾN TRẠNG THÁI MODAL ---
const isEditModalOpen = ref(false);
const isPassModalOpen = ref(false);

// --- DỮ LIỆU FORM ĐỔI PASS ---
const passData = ref({
  current_pass: '',
  new_pass: '',
  confirm_pass: ''
});

// --- CÁC HÀM HỖ TRỢ ---
const formatDate = (dateString) => {
  if (!dateString) return "Chưa cập nhật";
  const date = new Date(dateString);
  return date.toLocaleDateString('vi-VN');
};

const fetchUserProfile = async () => {
  const storedUser = JSON.parse(localStorage.getItem('sincere_user'));
  const userId = storedUser?.ma_tai_khoan;
  if (!userId) return;

  try {
    const response = await fetch(`http://127.0.0.1:8000/api/user-info?ma_tai_khoan=${userId}`);
    const result = await response.json();
    if (result.success) {
      userData.value = result.data;
      initials.value = userData.value.ho_ten ? userData.value.ho_ten.charAt(0).toUpperCase() : 'U';
    }
  } catch (error) {
    console.error("Lỗi lấy dữ liệu:", error);
  }
};

onMounted(fetchUserProfile);

// --- XỬ LÝ CHỈNH SỬA HỒ SƠ ---
const openEditModal = () => {
  editData.value = { ...userData.value };
  isEditModalOpen.value = true;
};

const saveProfile = async () => {
  isSaving.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/profile/update', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(editData.value)
    });
    const data = await response.json();
    if (data.success) {
      alert("Đã cập nhật hồ sơ thành công! 🎉");
      isEditModalOpen.value = false;
      localStorage.setItem('sincere_user', JSON.stringify(editData.value));
      fetchUserProfile();
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (error) {
    alert("Không thể kết nối đến máy chủ!");
  } finally {
    isSaving.value = false;
  }
};

// --- XỬ LÝ ĐỔI MẬT KHẨU ---
const openPassModal = () => {
  passData.value = { current_pass: '', new_pass: '', confirm_pass: '' };
  isPassModalOpen.value = true;
};

const handleChangePassword = async () => {
  if (passData.value.new_pass !== passData.value.confirm_pass) {
    return alert("Mật khẩu mới không khớp!");
  }
  if (passData.value.new_pass.length < 6) {
    return alert("Mật khẩu phải từ 6 ký tự!");
  }

  isSaving.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/profile/change-password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        ma_tai_khoan: userData.value.ma_tai_khoan,
        current_pass: passData.value.current_pass,
        new_pass: passData.value.new_pass
      })
    });
    const data = await response.json();
    if (data.success) {
      alert("Đổi mật khẩu thành công!");
      isPassModalOpen.value = false;
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (error) {
    alert("Lỗi kết nối máy chủ!");
  } finally {
    isSaving.value = false;
  }
};
</script>

<style scoped src="../../assets/hosocanhan.css"></style>