<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">
      {{ formData.ma_hang_hoa ? 'Cập Nhật Hàng Hóa' : 'Thêm Hàng Hóa Mới' }}
    </h3>

    <div class="table-card" style="padding: 20px;">
      <div v-if="isLoadingData" style="text-align: center; padding: 20px; color: #3498db;">
        Đang tải thông tin...
      </div>
      <form v-else @submit.prevent="saveData">
        <div class="form-group">
          <label>Tên Hàng Hóa</label>
          <input v-model="formData.ten_hang_hoa" required placeholder="Nhập tên hàng hóa...">
        </div>
        <div class="form-group">
          <label>HS Code</label>
          <input v-model="formData.hs_code" placeholder="Nhập HS Code...">
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
  ma_hang_hoa: null,
  ten_hang_hoa: '',
  hs_code: ''
});

const fetchDetail = async (id) => {
  isLoadingData.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/hang-hoa');
    const data = await response.json();
    if (data.success) {
      const found = data.data.find(item => String(item.ma_hang_hoa) === String(id));
      if (found) {
        formData.value = { ...found };
      } else {
        alert("Không tìm thấy thông tin hàng hóa!");
        router.push('/danh-muc/hang-hoa');
      }
    }
  } catch (error) {
    console.error('Fetch detail error:', error);
  } finally {
    isLoadingData.value = false;
  }
};

const saveData = async () => {
  isSaving.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/hang-hoa/save', {
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
      router.push('/danh-muc/hang-hoa');
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
.table-card { 
  background: white; 
  border-radius: 8px; 
  box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
}
</style>
