<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">
      {{ formData.ma_hang_tau ? 'Cập Nhật Hãng Tàu' : 'Thêm Hãng Tàu Mới' }}
    </h3>

    <div class="table-card" style="padding: 20px;">
      <div v-if="isLoadingData" style="text-align: center; padding: 20px; color: #3498db;">
        Đang tải thông tin...
      </div>
      <form v-else @submit.prevent="saveData">
        <div class="form-group">
          <label>Tên Hãng Tàu</label>
          <input v-model="formData.ten_hang_tau" required placeholder="Nhập tên hãng tàu...">
        </div>
        <div class="form-group">
          <label>Địa chỉ</label>
          <input v-model="formData.dia_chi" placeholder="Nhập địa chỉ...">
        </div>
        <div class="form-group">
          <label>Điện thoại</label>
          <input
            v-model="formData.so_dien_thoai"
            placeholder="0123456789"
            pattern="[0-9()+\-\.\s]{7,25}"
            title="Nhập số điện thoại hợp lệ, từ 7-25 ký tự"
            maxlength="25"
          >
        </div>
        <div class="form-group">
          <label>Fax</label>
          <input
            v-model="formData.so_fax"
            placeholder="Nhập Fax..."
            pattern="[0-9()+\-\.\s]{7,25}"
            title="Nhập số fax hợp lệ"
            maxlength="25"
          >
        </div>
        <div class="form-group">
          <label>Ghi chú</label>
          <textarea v-model="formData.ghi_chu" placeholder="Nhập ghi chú..." rows="4"></textarea>
        </div>
        <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
          <button type="button" class="btn-cancel" @click="router.back()" style="padding: 10px 25px;">Hủy</button>
          <button type="submit" class="btn-save" :disabled="isSaving" style="padding: 10px 25px;">
             {{ isSaving ? 'Đang lưu...' : 'Lưu lại' }}
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
const isLoadingData = ref(false);

const formData = ref({
  ma_hang_tau: null,
  ten_hang_tau: '',
  dia_chi: '',
  so_dien_thoai: '', 
  so_fax: '',
  ghi_chu: '',
  nguoi_sua_cuoi: null
});

const fetchDetail = async (id) => {
  isLoadingData.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/hang-tau');
    const data = await response.json();
    if (data.success) {
      const found = data.data.find(item => String(item.ma_hang_tau) === String(id));
      if (found) {
        formData.value = { ...found };
      } else {
        alert("Không tìm thấy thông tin hãng tàu!");
        router.push('/danh-muc/hang-tau');
      }
    }
  } catch (error) {
    console.error('Fetch detail error:', error);
  } finally {
    isLoadingData.value = false;
  }
};

const isValidContact = (value) => {
  if (!value) return true;
  const normalized = value.replace(/\D/g, '');
  const validChars = /^[0-9()+\-\.\s]+$/;
  return validChars.test(value) && normalized.length >= 7 && normalized.length <= 25;
};

const saveData = async () => {
  if (formData.value.so_dien_thoai && !isValidContact(formData.value.so_dien_thoai)) {
    alert('Số điện thoại không hợp lệ. Vui lòng nhập lại.');
    return;
  }
  if (formData.value.so_fax && !isValidContact(formData.value.so_fax)) {
    alert('Số fax không hợp lệ. Vui lòng nhập lại.');
    return;
  }

  const user = JSON.parse(localStorage.getItem('sincere_user'));
  formData.value.nguoi_sua_cuoi = user ? user.ma_tai_khoan : null;

  isSaving.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/hang-tau/save', {
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
      router.push('/danh-muc/hang-tau');
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (error) {
    console.error('Save error:', error);
    alert("Lỗi kết nối máy chủ khi lưu!");
  } finally {
    isSaving.value = false;
  }
};

onMounted(() => {
  const id = route.params.id;
  if (id) {
    fetchDetail(id);
  }
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.table-card { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
</style>
