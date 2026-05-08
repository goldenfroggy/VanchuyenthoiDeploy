<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">
      {{ formData.ma_don_vi_tinh ? 'Cập Nhật Đơn Vị Tính' : 'Thêm Đơn Vị Tính Mới' }}
    </h3>

    <div class="table-card" style="padding: 20px;">
      <div v-if="isLoadingData" style="text-align: center; padding: 20px; color: #3498db;">
        Đang tải thông tin...
      </div>
      <form v-else @submit.prevent="saveData">
        <div class="form-group">
          <label>Tên Đơn Vị Tính</label>
          <input v-model="formData.ten_don_vi_tinh" required placeholder="Nhập tên đơn vị tính">
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
  ma_don_vi_tinh: null,
  ten_don_vi_tinh: ''
});

const fetchDetail = async (id) => {
  isLoadingData.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/don-vi-tinh');
    const data = await response.json();
    if (data.success) {
      const found = data.data.find(item => String(item.ma_don_vi_tinh) === String(id));
      if (found) {
        formData.value = { ...found };
      } else {
        alert("Không tìm thấy thông tin đơn vị tính!");
        router.push('/danh-muc/don-vi-tinh');
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
    const response = await fetch('http://127.0.0.1:8000/api/don-vi-tinh/save', {
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
      router.push('/danh-muc/don-vi-tinh');
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
