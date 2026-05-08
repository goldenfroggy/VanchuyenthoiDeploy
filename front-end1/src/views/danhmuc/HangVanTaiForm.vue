<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">
      {{ formData.ma_hang_van_tai ? 'Cập Nhật Hãng Vận Tải' : 'Thêm Hãng Vận Tải Mới' }}
    </h3>

    <div class="table-card" style="padding: 20px;">
      <div v-if="isLoadingData" style="text-align: center; padding: 20px; color: #3498db;">
        Đang tải thông tin...
      </div>
      <form v-else @submit.prevent="saveData">
        <div class="form-group">
          <label>Tên Hãng Vận Tải</label>
          <input v-model="formData.ten_hang_van_tai" required placeholder="Nhập tên hãng vận tải...">
        </div>
        <div class="form-group">
          <label>Tuyến Thường Xuyên</label>
          <input v-model="formData.tuyen_thuong_xuyen" required placeholder="Nhập tuyến thường xuyên...">
        </div>
        <div class="form-group">
          <label>Các Loại Xe</label>
          <input v-model="formData.cac_loai_xe" required placeholder="Nhập các loại xe...">
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
  ma_hang_van_tai: null,
  ten_hang_van_tai: '',
  tuyen_thuong_xuyen: '',
  cac_loai_xe: '',
  ghi_chu: ''
});

const fetchDetail = async (id) => {
  isLoadingData.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/hang-van-tai');
    const data = await response.json();
    if (data.success) {
      const found = data.data.find(item => String(item.ma_hang_van_tai) === String(id));
      if (found) {
        formData.value = { 
          ...found,
          tuyen_thuong_xuyen: typeof found.tuyen_thuong_xuyen === 'string' ? found.tuyen_thuong_xuyen : (found.tuyen_thuong_xuyen ? JSON.stringify(found.tuyen_thuong_xuyen) : ''),
          cac_loai_xe: typeof found.cac_loai_xe === 'string' ? found.cac_loai_xe : (found.cac_loai_xe ? JSON.stringify(found.cac_loai_xe) : '')
        };
      } else {
        alert("Không tìm thấy thông tin hãng vận tải!");
        router.push('/danh-muc/hang-van-tai');
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
    const response = await fetch('http://127.0.0.1:8000/api/hang-van-tai/save', {
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
      router.push('/danh-muc/hang-van-tai');
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
