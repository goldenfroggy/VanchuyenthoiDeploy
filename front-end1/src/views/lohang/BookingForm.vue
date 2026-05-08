<template>
  <div class="booking-form-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h3 style="margin: 0; color: #2c3e50;">
        {{ formData.ma_booking ? '🚢 Cập nhật Booking Note #' + formData.so_booking : '🚢 Tạo Booking Note mới' }}
      </h3>
    </div>

    <div class="table-card" style="padding: 25px;">
      <form @submit.prevent="handleSave">
        <div style="display: flex; gap: 15px;">
          <div class="form-group" style="flex: 1;">
            <label>Số Booking*</label>
            <input v-model="formData.so_booking" required placeholder="Nhập mã Booking Note từ hãng tàu...">
          </div>
          <div class="form-group" style="flex: 1;">
            <label>Hãng Tàu *</label>
            <select v-model="formData.ma_hang_tau" required>
              <option :value="null">-- Chọn Hãng tàu --</option>
              <option v-for="ht in listHangTau" :key="ht.ma_hang_tau" :value="ht.ma_hang_tau">
                {{ ht.ten_hang_tau }}
              </option>
            </select>
          </div>
        </div>

        <div style="display: flex; gap: 15px;">
          <div class="form-group" style="flex: 2;">
            <label>Tên Con Tàu *</label>
            <input v-model="formData.ten_con_tau" placeholder="VD: EVER GIVEN" required>
          </div>
          <div class="form-group" style="flex: 1;">
            <label>Số Chuyến (Voyage) *</label>
            <input v-model="formData.so_chuyen" placeholder="VD: 045E" required>
          </div>
        </div>

        <div style="display: flex; gap: 15px;">
          <div class="form-group" style="flex: 1;">
            <label>Cảng Đi (POL) *</label>
            <select v-model="formData.ma_cang_di" required>
              <option :value="null">-- Chọn Cảng Đi --</option>
              <option v-for="cang in listCangBien" :key="cang.ma_cang" :value="cang.ma_cang">
                {{ cang.ten_cang }}
              </option>
            </select>
          </div>
          <div class="form-group" style="flex: 1;">
            <label>Cảng Đến (POD) *</label>
            <select v-model="formData.ma_cang_den" required>
              <option :value="null">-- Chọn Cảng Đến --</option>
              <option v-for="cang in listCangBien" :key="cang.ma_cang" :value="cang.ma_cang">
                {{ cang.ten_cang }}
              </option>
            </select>
          </div>
        </div>

        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
          <div class="form-group" style="flex: 1 1 30%;">
            <label>Giờ Cắt Máng (Cut-off) *</label>
            <input type="datetime-local" v-model="formData.gio_cat_mang" required>
          </div>
          <div class="form-group" style="flex: 1 1 30%;">
            <label>ETD (Dự kiến đi) *</label>
            <input type="datetime-local" v-model="formData.etd" required>
          </div>
          <div class="form-group" style="flex: 1 1 30%;">
            <label>ETA (Dự kiến đến) *</label>
            <input type="datetime-local" v-model="formData.eta" required>
          </div>
        </div>

        <div class="modal-actions" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
          <button type="button" class="btn-cancel" @click="handleCancel">Hủy bỏ</button>
          <button type="submit" class="btn-save" :disabled="isSaving">
            {{ isSaving ? 'Đang lưu...' : 'Lưu dữ liệu Booking Note 💾' }}
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

const listCangBien = ref([]);
const listHangTau = ref([]);
const isSaving = ref(false);

const formData = ref({
  ma_booking: null,
  so_booking: '',
  ten_con_tau: '',
  so_chuyen: '',
  etd: '',
  eta: '',
  gio_cat_mang: '',
  ma_cang_di: null,
  ma_cang_den: null,
  ma_hang_tau: null,
  nguoi_sua_cuoi: null
});

const formatForInput = (dateString) => {
  if (!dateString || dateString.startsWith('0000') || dateString.startsWith('1970')) return '';
  try {
    return new Date(dateString).toISOString().slice(0, 16);
  } catch (e) {
    return '';
  }
};

const loadReferences = async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/api/bookings/references');
    const data = await res.json();
    if (data.success) {
      listCangBien.value = data.cang_bien;
      listHangTau.value = data.hang_tau;
    }
  } catch (error) {
    console.error("Lỗi lấy dữ liệu tham chiếu");
  }
};

const fetchBookingData = async (id) => {
  try {
    const res = await fetch('http://127.0.0.1:8000/api/bookings');
    const data = await res.json();
    if (data.success) {
      const found = data.data.find(x => String(x.ma_booking) === String(id));
      if (found) {
        formData.value = {
          ...found,
          etd: formatForInput(found.etd),
          eta: formatForInput(found.eta),
          gio_cat_mang: formatForInput(found.gio_cat_mang)
        };
      }
    }
  } catch (error) {
    console.error("Lỗi lấy dữ liệu Booking Note!");
  }
};

const handleSave = async () => {
  if (!formData.value.so_booking) {
    alert("Vui lòng nhập Số Booking!");
    return;
  }
  if (!formData.value.ma_hang_tau) {
    alert("Vui lòng chọn Hãng Tàu!");
    return;
  }
  if (!formData.value.ma_cang_di) {
    alert("Vui lòng chọn Cảng Đi!");
    return;
  }
  if (!formData.value.ma_cang_den) {
    alert("Vui lòng chọn Cảng Đến!");
    return;
  }
  if (formData.value.ma_cang_di == formData.value.ma_cang_den) {
    alert("Vui lòng chọn Cảng Đi và Cảng Đến khác nhau!");
    return;
  }

  const cutOff = formData.value.gio_cat_mang ? new Date(formData.value.gio_cat_mang) : null;
  const etd = formData.value.etd ? new Date(formData.value.etd) : null;
  const eta = formData.value.eta ? new Date(formData.value.eta) : null;

  if (cutOff && etd && cutOff >= etd) {
    alert("Lỗi: Giờ Cắt Máng phải trước ETD (Dự kiến đi)!");
    return;
  }
  if (etd && eta && etd >= eta) {
    alert("Lỗi: ETD (Dự kiến đi) phải trước ETA (Dự kiến đến)!");
    return;
  }

  isSaving.value = true;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  formData.value.nguoi_sua_cuoi = user ? (user.id || user.ma_tai_khoan) : null;

  try {
    const res = await fetch('http://127.0.0.1:8000/api/bookings/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData.value)
    });
    const data = await res.json();
    if (data.success) {
      alert(data.message);
      router.push('/lo-hang/booking');
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (e) {
    alert("Lỗi server!");
  } finally {
    isSaving.value = false;
  }
};

const handleCancel = () => {
  if (confirm("Dữ liệu chưa lưu sẽ bị mất. Bạn có muốn quay lại không?")) {
    router.push('/lo-hang/booking');
  }
};

onMounted(async () => {
  await loadReferences();
  const id = route.params.id;
  if (id) {
    await fetchBookingData(id);
  }
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.booking-form-container {
  padding: 20px;
  /* max-width: 1000px; */
  margin: 0 auto;
}
</style>
