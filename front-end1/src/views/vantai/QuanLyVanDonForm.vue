<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">
      {{ formData.ma_van_don ? 'Cập Nhật Vận Đơn' : 'Thêm Vận Đơn Mới' }}
    </h3>

    <div class="table-card" style="padding: 20px;">
      <div v-if="isLoadingData" style="text-align: center; padding: 20px; color: #3498db;">
        Đang tải thông tin...
      </div>
      <form v-else @submit.prevent="saveVanDon" style="display: flex; gap: 25px; align-items: flex-start;">
        <div style="flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <div class="form-group">
            <label>Số Vận Đơn *</label>
            <input v-model="formData.so_van_don" required placeholder="VD: HPH2024001" style="height: 44px; box-sizing: border-box;">
          </div>
          <div class="form-group">
            <label>Loại Vận Đơn</label>
            <select v-model="formData.loai_van_don" style="height: 44px; box-sizing: border-box;">
              <option v-for="type in listLoaiVanDon" :key="type" :value="type">{{ type }}</option>
            </select>
          </div>

          <div class="form-group">
            <label>Số Vận Đơn Gốc</label>
            <input v-model="formData.so_van_don_goc" placeholder="Nếu có" style="height: 44px; box-sizing: border-box;">
          </div>
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
            <label>Ngày Phát Hành</label>
            <input type="datetime-local" v-model="formData.ngay_phat_hanh" style="height: 44px; box-sizing: border-box;">
          </div>
          <div class="form-group">
            <label>Điều Kiện Cước</label>
            <select v-model="formData.dieu_kien_cuoc" style="height: 44px; box-sizing: border-box;">
              <option value="Freight Prepaid">Freight Prepaid (Cước trả trước)</option>
              <option value="Freight Collect">Freight Collect (Cước trả sau)</option>
            </select>
          </div>

          <div class="form-group">
            <label>Cảng Đi (POL)</label>
            <div class="combobox-wrapper">
              <input 
                type="text" 
                v-model="polSearchText" 
                placeholder="Nhập tên cảng đi..." 
                @focus="showPolDropdown = true"
                class="combobox-input"
              >
              <ul v-if="showPolDropdown" class="combobox-list">
                <li v-for="c in filteredPol" :key="c.ma_cang" @click="selectCang(c, 'pol')">
                  {{ c.ten_cang }}
                </li>
                <li v-if="filteredPol.length === 0" class="no-result">Không tìm thấy cảng</li>
              </ul>
            </div>
          </div>
          <div class="form-group">
            <label>Cảng Đến (POD)</label>
            <div class="combobox-wrapper">
              <input 
                type="text" 
                v-model="podSearchText" 
                placeholder="Nhập tên cảng đến..." 
                @focus="showPodDropdown = true"
                class="combobox-input"
              >
              <ul v-if="showPodDropdown" class="combobox-list">
                <li v-for="c in filteredPod" :key="c.ma_cang" @click="selectCang(c, 'pod')">
                  {{ c.ten_cang }}
                </li>
                <li v-if="filteredPod.length === 0" class="no-result">Không tìm thấy cảng</li>
              </ul>
            </div>
          </div>

          <div class="form-group">
            <label>Người Gửi Hàng</label>
            <div class="combobox-wrapper">
              <input 
                type="text" 
                v-model="shipperSearchText" 
                placeholder="Tìm tên người gửi..." 
                @focus="showShipperDropdown = true"
                class="combobox-input"
              >
              <ul v-if="showShipperDropdown" class="combobox-list">
                <li v-for="kh in filteredShippers" :key="kh.ma_khach_hang" @click="selectKhachHang(kh, 'shipper')">
                  {{ kh.ten_khach_hang }}
                </li>
                <li v-if="filteredShippers.length === 0" class="no-result">Không tìm thấy khách hàng</li>
              </ul>
            </div>
          </div>
          <div class="form-group">
            <label>Người Nhận Hàng</label>
            <div class="combobox-wrapper">
              <input 
                type="text" 
                v-model="consigneeSearchText" 
                placeholder="Tìm tên người nhận..." 
                @focus="showConsigneeDropdown = true"
                class="combobox-input"
              >
              <ul v-if="showConsigneeDropdown" class="combobox-list">
                <li v-for="kh in filteredConsignees" :key="kh.ma_khach_hang" @click="selectKhachHang(kh, 'consignee')">
                  {{ kh.ten_khach_hang }}
                </li>
                <li v-if="filteredConsignees.length === 0" class="no-result">Không tìm thấy khách hàng</li>
              </ul>
            </div>
          </div>
          
          <div class="form-group">
            <label>Bên được thông báo (Notify Party)</label>
            <div class="combobox-wrapper" style="display: flex; gap: 5px;">
              <div style="flex: 1; position: relative;">
                <input 
                  type="text" 
                  v-model="notifySearchText" 
                  placeholder="Tìm hoặc chọn SAME AS..." 
                  @focus="showNotifyDropdown = true"
                  class="combobox-input"
                >
                <ul v-if="showNotifyDropdown" class="combobox-list">
                  <li @click="setNotifySameAs()" style="color: #2980b9; font-weight: bold;">SAME AS CONSIGNEE</li>
                  <li v-for="kh in filteredNotify" :key="kh.ma_khach_hang" @click="selectKhachHang(kh, 'notify')">
                    {{ kh.ten_khach_hang }}
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Số Container</label>
            <input v-model="formData.so_cont" placeholder="VD: TCNU1234567" style="height: 44px; box-sizing: border-box;">
          </div>

          <div class="form-group">
            <label>Số Chì (Seal)</label>
            <input v-model="formData.so_chi" placeholder="VD: S123456" style="height: 44px; box-sizing: border-box;">
          </div>
          <div class="form-group">
            <label>Phương Thức Đóng Cont</label>
            <select v-model="formData.phuong_thuc_dong_cont" style="height: 44px; box-sizing: border-box;">
              <option value="FCL">FCL (Nguyên cont)</option>
              <option value="LCL">LCL (Hàng lẻ)</option>
            </select>
          </div>

          <div style="grid-column: span 2; margin-top: 10px; display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" class="btn-cancel" @click="router.back()" style="padding: 10px 25px;">Hủy</button>
            <button type="submit" class="btn-save" :disabled="isSaving" style="padding: 10px 25px;">
               {{ isSaving ? 'Đang lưu...' : 'Lưu Vận Đơn' }}
            </button>
            <button v-if="formData.ma_van_don" type="button" @click="handleExportPdf(formData.ma_van_don, formData.so_van_don)" class="btn-cancel" style="background: #17a2b8; color: white; border: none; padding: 10px 25px;">
              Xuất PDF 🖨️
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
                      <button type="button" @click="selectLoHang(lh)" class="btn-save" style="width: 100%; font-size: 12px; height: auto; padding: 6px 0; display: flex; align-items: center; justify-content: center; border-radius: 6px;">✅ Chọn</button>
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

const listKhachHang = ref([]);
const listCangBien = ref([]);
const listLoHang = ref([]);
const listLoaiVanDon = ['Original B/L', 'Surrendered B/L', 'Seaway Bill'];

// State cho chức năng Picker và Preview
const isLoHangPickerOpen = ref(false);
const previewLoHang = ref(null);
const showLoHangPanel = ref(false);
const shipmentSearchQuery = ref('');

// State cho Combobox Tìm kiếm
const polSearchText = ref('');
const showPolDropdown = ref(false);
const podSearchText = ref('');
const showPodDropdown = ref(false);
const shipperSearchText = ref('');
const showShipperDropdown = ref(false);
const consigneeSearchText = ref('');
const showConsigneeDropdown = ref(false);
const notifySearchText = ref('');
const showNotifyDropdown = ref(false);

const formData = ref({
  ma_van_don: null, loai_van_don: 'Original B/L', ngay_phat_hanh: '',
  so_van_don_goc: '', so_van_don: '', so_cont: '', so_chi: '',
  phuong_thuc_dong_cont: 'FCL', dieu_kien_cuoc: 'Freight Prepaid',
  ma_nguoi_gui_hang: null, ma_nguoi_nhan_hang: null, ma_ben_duoc_thong_bao: null,
  ma_cang_di: null, ma_cang_den: null, ma_lo_hang: null, nguoi_sua_cuoi: null
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

// Computed filters cho Combobox
const filteredPol = computed(() => listCangBien.value.filter(c => c.ten_cang.toLowerCase().includes(polSearchText.value.toLowerCase())));
const filteredPod = computed(() => listCangBien.value.filter(c => c.ten_cang.toLowerCase().includes(podSearchText.value.toLowerCase())));
const filteredShippers = computed(() => listKhachHang.value.filter(kh => kh.ten_khach_hang.toLowerCase().includes(shipperSearchText.value.toLowerCase())));
const filteredConsignees = computed(() => listKhachHang.value.filter(kh => kh.ten_khach_hang.toLowerCase().includes(consigneeSearchText.value.toLowerCase())));
const filteredNotify = computed(() => listKhachHang.value.filter(kh => kh.ten_khach_hang.toLowerCase().includes(notifySearchText.value.toLowerCase())));

const selectLoHang = (lh) => {
  formData.value.ma_lo_hang = lh.ma_lo_hang;
  isLoHangPickerOpen.value = false;
  previewLoHang.value = null;
  showLoHangPanel.value = true;
  shipmentSearchQuery.value = '';
};

const selectCang = (cang, target) => {
  if (target === 'pol') {
    formData.value.ma_cang_di = cang.ma_cang;
    polSearchText.value = cang.ten_cang;
    showPolDropdown.value = false;
  } else {
    formData.value.ma_cang_den = cang.ma_cang;
    podSearchText.value = cang.ten_cang;
    showPodDropdown.value = false;
  }
};

const selectKhachHang = (kh, target) => {
  if (target === 'shipper') {
    formData.value.ma_nguoi_gui_hang = kh.ma_khach_hang;
    shipperSearchText.value = kh.ten_khach_hang;
    showShipperDropdown.value = false;
  } else if (target === 'consignee') {
    formData.value.ma_nguoi_nhan_hang = kh.ma_khach_hang;
    consigneeSearchText.value = kh.ten_khach_hang;
    showConsigneeDropdown.value = false;
  } else {
    formData.value.ma_ben_duoc_thong_bao = kh.ma_khach_hang;
    notifySearchText.value = kh.ten_khach_hang;
    showNotifyDropdown.value = false;
  }
};

const setNotifySameAs = () => {
  formData.value.ma_ben_duoc_thong_bao = 'SAME_AS_CONSIGNEE';
  notifySearchText.value = 'SAME AS CONSIGNEE';
  showNotifyDropdown.value = false;
};

const fetchReferences = async () => {
  try {
    // Gọi đồng thời API references (để lấy danh mục) và API lo-hang (để lấy chi tiết đầy đủ)
    const [resRef, allRes] = await Promise.all([
      fetch(`${import.meta.env.VITE_API_URL}/van-don/references`),
      fetch(`${import.meta.env.VITE_API_URL}/lo-hang`)
    ]);
    const dataRef = await resRef.json();
    const allData = await allRes.json();

    if (dataRef.success && allData.success) {
      listKhachHang.value = dataRef.khach_hang;
      listCangBien.value = dataRef.cang_bien;
      
      // Đóng các dropdown khi click ra ngoài
      window.addEventListener('click', (e) => {
        if (!e.target.closest('.combobox-wrapper')) {
          showPolDropdown.value = showPodDropdown.value = showShipperDropdown.value = showConsigneeDropdown.value = showNotifyDropdown.value = false;
        }
      });

      const validIds = dataRef.lo_hang.map(lh => lh.ma_lo_hang);
      // Lọc danh sách lô hàng đầy đủ thông tin dựa trên các ID hợp lệ từ references
      listLoHang.value = allData.data.filter(lh => 
        validIds.includes(lh.ma_lo_hang) &&
        lh.trang_thai_lo_hang !== 'Hoàn tất' && lh.trang_thai_lo_hang !== 'Hủy'
      );
    }
  } catch (error) { console.error("Lỗi lấy dữ liệu tham chiếu"); }
};

const fetchDetail = async (id) => {
  isLoadingData.value = true;
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/van-don`);
    const data = await res.json();
    if (data.success) {
      const found = data.data.find(item => String(item.ma_van_don) === String(id));
      if (found) {
        let benThongBao = found.ma_ben_duoc_thong_bao;
        // Nếu ID bên thông báo trùng với ID người nhận, hiển thị là SAME AS CONSIGNEE
        if (benThongBao && benThongBao === found.ma_nguoi_nhan_hang) {
          benThongBao = 'SAME_AS_CONSIGNEE';
        }

        formData.value = { 
          ...found,
          ma_ben_duoc_thong_bao: benThongBao,
          ngay_phat_hanh: found.ngay_phat_hanh ? new Date(found.ngay_phat_hanh).toISOString().slice(0, 16) : ''
        };
        // Cập nhật text hiển thị cho combobox
        polSearchText.value = found.ten_cang_di || '';
        podSearchText.value = found.ten_cang_den || '';
        shipperSearchText.value = listKhachHang.value.find(kh => kh.ma_khach_hang === found.ma_nguoi_gui_hang)?.ten_khach_hang || '';
        consigneeSearchText.value = listKhachHang.value.find(kh => kh.ma_khach_hang === found.ma_nguoi_nhan_hang)?.ten_khach_hang || '';
        notifySearchText.value = benThongBao === 'SAME_AS_CONSIGNEE' ? 'SAME AS CONSIGNEE' : (listKhachHang.value.find(kh => kh.ma_khach_hang === found.ma_ben_duoc_thong_bao)?.ten_khach_hang || '');
        
        showLoHangPanel.value = true;
      }
    }
  } catch (e) { console.error(e); }
  finally { isLoadingData.value = false; }
};

const saveVanDon = async () => {
  isSaving.value = true;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  
  const dataToSend = { ...formData.value };
  dataToSend.nguoi_sua_cuoi = user ? (user.id || user.ma_tai_khoan) : null;

  // Xử lý logic SAME AS CONSIGNEE trước khi gửi lên server
  if (dataToSend.ma_ben_duoc_thong_bao === 'SAME_AS_CONSIGNEE') {
    dataToSend.ma_ben_duoc_thong_bao = dataToSend.ma_nguoi_nhan_hang;
  }

  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/van-don/save`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dataToSend)
    });
    const data = await res.json();
    if (data.success) { alert(data.message); router.push('/van-tai/quan-ly-van-don'); }
    else { alert("Lỗi: " + data.message); }
  } catch (e) { alert("Lỗi server!"); }
  finally { isSaving.value = false; }
};

const handleExportPdf = async (id, soVanDon) => {
  try {
    const response = await fetch(`${import.meta.env.VITE_API_URL}/van-don/export-pdf/${id}`);
    if (!response.ok) {
      const errorData = await response.json(); // Cố gắng lấy thông báo lỗi từ response
      throw new Error(errorData.message || 'Lỗi khi tạo PDF');
    }
    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `VanDon_${soVanDon}.pdf`); // Tên file PDF khi tải về
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    alert("Lỗi khi xuất PDF: " + error.message);
    console.error("Lỗi khi xuất PDF:", error);
  }
};

onMounted(async () => {
  await fetchReferences();
  const id = route.params.id;
  if (id) fetchDetail(id);
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
/* CSS cho Combobox Tìm kiếm */
.combobox-wrapper { position: relative; width: 100%; }
.combobox-input {
  width: 100%; height: 44px; padding: 10px; border: 1px solid #ddd;
  border-radius: 6px; box-sizing: border-box; background: #fff;
  transition: border-color 0.2s;
}
.combobox-input:focus { border-color: #3498db; outline: none; }
.combobox-list {
  position: absolute; top: 100%; left: 0; right: 0; background: #fff;
  border: 1px solid #ddd; border-radius: 6px; margin: 2px 0 0 0; padding: 0;
  list-style: none; z-index: 1000; max-height: 200px; overflow-y: auto;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.combobox-list li {
  padding: 10px; cursor: pointer; transition: background 0.2s;
  font-size: 14px; color: #2c3e50; border-bottom: 1px solid #f9f9f9;
}
.combobox-list li:hover { background: #f0f7ff; color: #2980b9; }
.combobox-list li.no-result { color: #95a5a6; cursor: default; font-style: italic; }

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

.btn-picker:hover {
  background: #27ae60 !important;
}
</style>