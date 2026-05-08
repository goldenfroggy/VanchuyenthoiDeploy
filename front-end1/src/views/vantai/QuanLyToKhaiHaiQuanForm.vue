<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">
      {{ formData.ma_to_khai_hai_quan ? 'Cập Nhật Tờ Khai' : 'Thêm Tờ Khai Mới' }}
    </h3>

    <div class="table-card" style="padding: 20px;">
      <div v-if="isLoadingData" style="text-align: center; padding: 20px; color: #3498db;">
        Đang tải thông tin...
      </div>
      <form v-else @submit.prevent="saveToKhai" style="display: flex; gap: 25px; align-items: flex-start;">
        <div style="flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <div class="form-group">
            <label>Lô Hàng Liên Kết *</label>
            <div style="display: flex; gap: 8px; align-items: stretch;">
              <div style="flex: 1; position: relative; min-height: 44px;">
                <input 
                  type="text" 
                  :value="selectedLoHang ? selectedLoHang.ten_lo_hang : 'Chưa chọn lô hàng'" 
                  readonly 
                  style="background: #f9f9f9; cursor: default; width: 100%; height: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;"
                >
                <button 
                  v-if="formData.ma_lo_hang" 
                  type="button" 
                  @click="formData.ma_lo_hang = null; showLoHangPanel = false" 
                  style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); border: none; background: none; cursor: pointer; color: #e74c3c; font-weight: bold;"
                >✖</button>
              </div>
              <div style="display: flex; flex-direction: column; gap: 4px; width: 85px;">
                <button v-if="formData.ma_lo_hang" type="button" @click="showLoHangPanel = !showLoHangPanel" class="view-btn" style="padding: 2px 10px; font-size: 11px; flex: 1; min-height: 20px; width: 100%;">
                  {{ showLoHangPanel ? '✖ Đóng' : '👁️ Xem' }}
                </button>
                <button type="button" class="btn-picker" @click="isLoHangPickerOpen = true" style="padding: 2px 10px; background: #2ecc71; color: white; border: none; border-radius: 6px; cursor: pointer; white-space: nowrap; font-size: 11px; flex: 1; min-height: 20px; width: 100%; display: flex; align-items: center; justify-content: center;">
                  🔍 Chọn
                </button>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Ngày Thông Quan</label>
            <input type="datetime-local" v-model="formData.ngay_thong_quan" style="height: 44px; box-sizing: border-box;">
          </div>

          <div class="form-group">
            <label>Phân Luồng</label>
            <select v-model="formData.phan_luong" style="height: 44px; box-sizing: border-box;">
              <option v-for="pl in listPhanLuong" :key="pl" :value="pl">{{ pl }}</option>
            </select>
          </div>

          <div class="form-group">
            <label>Kết Quả</label>
            <select v-model="formData.ket_qua_thong_quan" style="height: 44px; box-sizing: border-box;">
              <option v-for="kq in listKetQua" :key="kq" :value="kq">{{ kq }}</option>
            </select>
          </div>

          <div class="form-group" style="grid-column: span 2;">
            <label>Ghi chú (Tùy chọn)</label>
            <textarea v-model="formData.ghi_chu" rows="2" style="width:100%; border:1px solid #ddd; border-radius:6px; padding:10px;"></textarea>
          </div>

          <div style="grid-column: span 2; display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;">
            <button type="button" class="btn-cancel" @click="router.back()" style="padding: 10px 25px;">Hủy</button>
            <button type="submit" class="btn-save" :disabled="isSaving" style="padding: 10px 25px;">
               {{ isSaving ? 'Đang lưu...' : 'Lưu Tờ Khai' }}
            </button>
          </div>
        </div>

        <!-- Side Panel hiển thị thông tin lô hàng đã chọn -->
        <div v-if="showLoHangPanel && selectedLoHang" class="side-panel-shipment">
          <h4 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #2980b9;">📦 Thông tin Lô hàng</h4>
          <div class="info-row"><span>Mã lô hàng:</span> <strong>#{{ selectedLoHang.ma_lo_hang }}</strong></div>
          <div class="info-row"><span>Tên lô hàng:</span> <strong>{{ selectedLoHang.ten_lo_hang }}</strong></div>
          <div class="info-row"><span>Khách hàng:</span> <strong>{{ selectedLoHang.ten_khach_hang || 'N/A' }}</strong></div>
          <div class="info-row"><span>Incoterms:</span> <strong>{{ selectedLoHang.dieu_kien_thuong_mai || 'N/A' }}</strong></div>
          <div class="info-row"><span>Trạng thái:</span> <strong style="color: #27ae60;">{{ selectedLoHang.trang_thai_lo_hang }}</strong></div>
          <div class="info-row"><span>Booking:</span> <strong>{{ selectedLoHang.so_booking || 'N/A' }}</strong></div>
          <hr>
          <div style="font-size: 13px; color: #7f8c8d; margin-bottom: 5px;">Nguồn gốc / Ghi chú:</div>
          <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 13px;">{{ selectedLoHang.nguon_goc || '(Trống)' }}</div>
        </div>
      </form>
    </div>

    <!-- MODAL CHỌN LÔ HÀNG -->
    <div v-if="isLoHangPickerOpen" class="modal-overlay">
      <div class="modal-content" style="max-width: 1000px; width: 95%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
          <h3 style="margin: 0; color: #2980b9;">📦 Danh sách Lô hàng</h3>
          <button @click="isLoHangPickerOpen = false" class="close-panel-btn" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>

        <!-- Ô tìm kiếm lô hàng trong Picker -->
        <div style="margin-bottom: 15px;">
          <input 
            type="text" 
            v-model="shipmentSearchQuery" 
            placeholder="🔍 Tìm nhanh theo tên lô hàng, số booking hoặc tên khách hàng..." 
            style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; font-size: 14px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.05); border-color: #3498db;"
          >
        </div>

        <div style="display: flex; gap: 20px; align-items: flex-start;">
          <!-- Bảng danh sách bên trái -->
          <div style="flex: 1; max-height: 500px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px;">
            <table class="picker-table" style="width: 100%; border-collapse: collapse;">
              <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">
                <tr>
                  <th style="padding: 12px; text-align: left;">Tên Lô Hàng</th>
                  <th style="text-align: left;">Khách Hàng</th>
                  <th style="text-align: center;">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="lh in filteredPickerLoHang" :key="lh.ma_lo_hang" :style="previewLoHang?.ma_lo_hang === lh.ma_lo_hang ? 'background: #eef7ff' : ''">
                  <td style="padding: 12px; font-weight: bold; color: #2c3e50;">{{ lh.ten_lo_hang }}</td>
                  <td>{{ lh.ten_khach_hang || 'N/A' }}</td>
                  <td style="width: 150px; padding: 10px;">
                    <div style="display: flex; flex-direction: column; gap: 5px; width: 120px; margin: 0 auto;">
                      <button type="button" @click="previewLoHang = lh" class="view-btn" style="width: 100%; font-size: 12px; padding: 6px 0;">👁️ Xem trước</button>
                      <button type="button" @click="selectLoHang(lh)" class="btn-save" style="width: 100%; font-size: 12px; height: auto; padding: 6px 0; display: flex; align-items: center; justify-content: center; border-radius: 6px; cursor: pointer;">✅ Chọn</button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredPickerLoHang.length === 0">
                  <td colspan="3" style="text-align: center; padding: 30px; color: #95a5a6;">Không tìm thấy lô hàng nào phù hợp...</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Panel xem chi tiết bên phải -->
          <div v-if="previewLoHang" class="side-panel-shipment" style="position: static; width: 350px; border: 1px solid #3498db;">
            <h4 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #2980b9;">📋 Chi tiết: {{ previewLoHang.ten_lo_hang }}</h4>
            <div class="info-row"><span>Mã lô hàng:</span> <strong>#{{ previewLoHang.ma_lo_hang }}</strong></div>
            <div class="info-row"><span>Khách hàng:</span> <strong>{{ previewLoHang.ten_khach_hang || 'N/A' }}</strong></div>
            <div class="info-row"><span>Booking:</span> <strong>{{ previewLoHang.so_booking || 'N/A' }}</strong></div>
            <div class="info-row"><span>Incoterms:</span> <strong>{{ previewLoHang.dieu_kien_thuong_mai || 'N/A' }}</strong></div>
            <div class="info-row"><span>Trạng thái:</span> <strong style="color: #27ae60;">{{ previewLoHang.trang_thai_lo_hang }}</strong></div>
            <hr>
            <div style="font-size: 13px; color: #7f8c8d; margin-bottom: 5px;">Nguồn gốc:</div>
            <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 13px;">{{ previewLoHang.nguon_goc || '(Trống)' }}</div>
          </div>
          <div v-else style="width: 350px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border: 1px dashed #ccc; border-radius: 8px; color: #7f8c8d; font-style: italic;">
             Nhấn "Xem trước" để kiểm tra thông tin
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const isSaving = ref(false);
const isLoadingData = ref(false);

const listLoHang = ref([]);
const listPhanLuong = ['Luồng Xanh', 'Luồng Vàng', 'Luồng Đỏ'];
const listKetQua = ['Chờ xử lý', 'Đã thông quan', 'Từ chối'];

// State cho chức năng Picker và Preview
const isLoHangPickerOpen = ref(false);
const previewLoHang = ref(null);
const showLoHangPanel = ref(false);
const shipmentSearchQuery = ref('');

const formData = ref({
  ma_to_khai_hai_quan: null,
  ngay_thong_quan: '',
  phan_luong: 'Luồng Xanh',
  ket_qua_thong_quan: 'Chờ xử lý',
  ma_lo_hang: '',
  ghi_chu: '',
  nguoi_sua_cuoi: null
});

const selectedLoHang = computed(() => {
  return listLoHang.value.find(lh => lh.ma_lo_hang === formData.value.ma_lo_hang);
});

const filteredPickerLoHang = computed(() => {
  const q = shipmentSearchQuery.value.toLowerCase().trim();
  if (!q) return listLoHang.value;
  return listLoHang.value.filter(lh => 
    (lh.ten_lo_hang && lh.ten_lo_hang.toLowerCase().includes(q)) ||
    (lh.so_booking && lh.so_booking.toLowerCase().includes(q)) ||
    (lh.ten_khach_hang && lh.ten_khach_hang.toLowerCase().includes(q))
  );
});

const selectLoHang = (lh) => {
  formData.value.ma_lo_hang = lh.ma_lo_hang;
  isLoHangPickerOpen.value = false;
  previewLoHang.value = null;
  showLoHangPanel.value = true;
  shipmentSearchQuery.value = '';
};

const fetchReferences = async (ma_to_khai = null) => {
  try {
    let url = 'http://127.0.0.1:8000/api/to-khai-hai-quan/references';
    if (ma_to_khai) {
      url += `?ma_to_khai_hai_quan=${ma_to_khai}`;
    }
    
    // Gọi đồng thời API references (để lấy danh sách ID hợp lệ) và API lo-hang (để lấy chi tiết đầy đủ)
    const [refRes, allRes] = await Promise.all([
      fetch(url),
      fetch('http://127.0.0.1:8000/api/lo-hang')
    ]);
    
    const refData = await refRes.json();
    const allData = await allRes.json();

    if (refData.success && allData.success) {
      const validIds = refData.lo_hang.map(lh => lh.ma_lo_hang);
      // Lọc danh sách lô hàng đầy đủ thông tin dựa trên các ID hợp lệ từ references
      listLoHang.value = allData.data.filter(lh => 
        validIds.includes(lh.ma_lo_hang) &&
        lh.trang_thai_lo_hang !== 'Hoàn tất' && lh.trang_thai_lo_hang !== 'Hủy'
      );
    }
  } catch (error) {
    console.error('Fetch refs error:', error);
  }
};

const fetchDetail = async (id) => {
  isLoadingData.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/to-khai-hai-quan');
    const data = await response.json();
    if (data.success) {
      const found = data.data.find(item => String(item.ma_to_khai_hai_quan) === String(id));
      if (found) {
        formData.value = { 
          ...found,
          ngay_thong_quan: found.ngay_thong_quan ? new Date(found.ngay_thong_quan).toISOString().slice(0, 16) : ''
        };
        showLoHangPanel.value = true;
      } else {
        alert("Không tìm thấy thông tin tờ khai!");
        router.push('/van-tai/to-khai-hai-quan');
      }
    }
  } catch (error) {
    console.error('Fetch detail error:', error);
  } finally {
    isLoadingData.value = false;
  }
};

const saveToKhai = async () => {
  if (!formData.value.ma_lo_hang) {
    alert("Vui lòng chọn lô hàng liên kết!");
    return;
  }

  isSaving.value = true;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  formData.value.nguoi_sua_cuoi = user ? (user.id || user.ma_tai_khoan) : null;
  
  try {
    const res = await fetch('http://127.0.0.1:8000/api/to-khai-hai-quan/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData.value)
    });
    const data = await res.json();
    if (data.success) {
      alert(data.message);
      router.push('/van-tai/to-khai-hai-quan');
    } else { alert("Lỗi: " + data.message); }
  } catch (e) { alert("Lỗi kết nối máy chủ!"); }
  finally { isSaving.value = false; }
};

onMounted(async () => {
  const id = route.params.id;
  await fetchReferences(id);
  if (id) {
    fetchDetail(id);
  }
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.view-btn {
  background: #ebf5fb; border: 1px solid #3498db; color: #3498db;
  border-radius: 6px; cursor: pointer; transition: 0.2s; font-weight: bold;
  display: flex; align-items: center; justify-content: center;
}
.view-btn:hover { background: #3498db; color: white; }

.side-panel-shipment {
  width: 350px; background: #fff; border: 1px solid #ddd; border-radius: 8px;
  padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); height: fit-content;
  position: sticky; top: 0; animation: slideIn 0.3s ease;
}

.info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
.info-row span { color: #7f8c8d; }
.info-row strong { color: #2c3e50; text-align: right; }

@keyframes slideIn { from { transform: translateX(20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

.modal-overlay {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;
}

.modal-content {
  background: white; padding: 25px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.picker-table th, .picker-table td {
  padding: 12px;
  border-bottom: 1px solid #eee;
}

.picker-table tr:hover {
  background-color: #fcfcfc;
}

.btn-picker {
  transition: background 0.2s;
}
.btn-picker:hover {
  background: #27ae60 !important;
}
</style>