<template>
  <div class="lo-hang-form-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h3 style="margin: 0; color: #2c3e50;">
        {{ formData.ma_lo_hang ? '📦 Cập nhật Lô Hàng #' + formData.ma_lo_hang : '📦 Tạo Lô Hàng mới' }}
      </h3>
      <button @click="handleCancel" class="btn-cancel" type="button">✖ Đóng</button>
    </div>

    <!-- Tab Navigation -->
    <div class="tabs">
      <button :class="{ active: activeTab === 'info' }" @click="activeTab = 'info'">📄 1. Thông tin chung</button>
      <button :class="{ active: activeTab === 'details' }" @click="activeTab = 'details'">📋 2. Chi tiết hàng hóa ({{ listDetails.length }})</button>
    </div>

    <div class="table-card" style="padding: 25px; min-height: 400px;">
      <!-- Tab 1: Thông tin Lô hàng -->
      <div v-show="activeTab === 'info'">
        <div style="display: flex; gap: 25px; align-items: flex-start;">
          <!-- FORM NHẬP LIỆU -->
          <form @submit.prevent="handleSaveStep1" style="flex: 1;">
            <div class="form-group">
              <label>Tên Lô Hàng *</label>
              <input v-model="formData.ten_lo_hang" required placeholder="VD: Lô hàng linh kiện điện tử Samsung tháng 4">
            </div>
            <div style="display: flex; gap: 20px;">
              <div class="form-group" style="flex: 1;">
                <label>Khách Hàng *</label>
                <select v-model="formData.ma_khach_hang" required>
                  <option :value="null">-- Chọn khách hàng --</option>
                  <option v-for="kh in listKhachHang" :key="kh.ma_khach_hang" :value="kh.ma_khach_hang">{{ kh.ten_khach_hang }}</option>
                </select>
              </div>
              <div class="form-group" style="flex: 1;">
                <label>Điều kiện thương mại (Incoterms)</label>
                <select v-model="formData.dieu_kien_thuong_mai">
                  <option v-for="dk in ['FOB', 'CIF', 'EXW', 'DAP', 'DDP', 'CFR']" :key="dk" :value="dk">{{ dk }}</option>
                </select>
              </div>
            </div>
            <div style="display: flex; gap: 20px;">
              <div class="form-group" style="flex: 1;">
                <label>Booking liên kết</label>
                <div style="display: flex; gap: 5px;">
                  <div style="flex: 1; position: relative;">
                    <input 
                      type="text" 
                      :value="selectedBooking ? selectedBooking.so_booking : 'Chưa chọn booking'" 
                      readonly 
                      style="background: #f9f9f9; cursor: default;"
                    >
                    <button 
                      v-if="formData.ma_booking" 
                      type="button" 
                      @click="formData.ma_booking = null; showBookingPanel = false" 
                      style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: none; cursor: pointer; color: #e74c3c; font-weight: bold;"
                    >✖</button>
                  </div>
                  <button type="button" class="btn-success" @click="isBookingPickerOpen = true" style="padding: 0 15px; height: 40px; white-space: nowrap;">
                    🔍 Chọn Booking
                  </button>
                  <button v-if="formData.ma_booking" type="button" @click="showBookingPanel = !showBookingPanel" class="view-btn" style="padding: 0 12px; height: 40px;">
                    {{ showBookingPanel ? '✖ Đóng' : '👁️ Xem' }}
                  </button>
                </div>
              </div>
              <div class="form-group" style="flex: 1;">
                <label>Trạng thái</label>
                <select v-model="formData.trang_thai_lo_hang">
                  <option v-for="tt in ['Mới tạo', 'Đang chờ xử lý', 'Đang vận chuyển', 'Đã thông quan', 'Hoàn tất', 'Hủy']" :key="tt" :value="tt">{{ tt }}</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label>Nguồn gốc</label>
              <textarea v-model="formData.nguon_goc" rows="3" style="width: 100%; border: 1px solid #ccc; border-radius: 4px; padding: 10px;"></textarea>
            </div>
            <div style="text-align: right; margin-top: 20px;">
              <button type="submit" class="btn-save">
                {{ isSaving ? 'Đang lưu...' : (formData.ma_lo_hang ? 'Cập nhật & Tiếp tục ➔' : 'Khởi tạo lô hàng & Tiếp tục ➔') }}
              </button>
            </div>
          </form>

          <!-- KHUNG CHI TIẾT BOOKING (HIỂN THỊ Ở KHU VỰC BÊN PHẢI TAB 1) -->
          <div v-if="showBookingPanel && selectedBooking" class="side-panel-booking">
            <h4 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #2980b9;">📦 Thông tin Booking</h4>
            <div class="info-row"><span>Số Booking:</span> <strong>{{ selectedBooking.so_booking }}</strong></div>
            <div class="info-row"><span>Hãng tàu:</span> <strong>{{ selectedBooking.ten_hang_tau || 'N/A' }}</strong></div>
            <div class="info-row"><span>Tên tàu:</span> <strong>{{ selectedBooking.ten_con_tau }}</strong></div>
            <div class="info-row"><span>Số chuyến:</span> <strong>{{ selectedBooking.so_chuyen }}</strong></div>
            <div class="info-row"><span>Giờ cắt máng:</span> <strong class="text-danger">{{ formatDateTime(selectedBooking.gio_cat_mang) }}</strong></div>
            <hr>
            <div class="info-row"><span>Cảng đi (POL):</span> <strong>{{ selectedBooking.ten_cang_di || '---' }}</strong></div>
            <div class="info-row"><span>Cảng đến (POD):</span> <strong>{{ selectedBooking.ten_cang_den || '---' }}</strong></div>
            <div class="info-row"><span>Ngày đi (ETD):</span> <strong class="text-primary">{{ formatDateTime(selectedBooking.etd) }}</strong></div>
            <div class="info-row"><span>Ngày đến (ETA):</span> <strong class="text-success">{{ formatDateTime(selectedBooking.eta) }}</strong></div>
          </div>
        </div>
      </div>

      <!-- Tab 2: Chi tiết hàng hóa-->
      <div v-show="activeTab === 'details'">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
          <h4 style="margin: 0; color: #34495e;">📋 Danh sách hàng hóa trong lô</h4>
          <button v-if="!isDetailFormVisible" type="button" class="btn-success" @click="addDetail">➕ Thêm chi tiết hàng</button>
        </div>
        
        <!-- Inline Form: Hiển thị trên đầu danh sách-->
        <div v-if="isDetailFormVisible" class="inline-form-box" style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 2px solid #3498db; margin-bottom: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
          <h4 style="margin-top: 0; color: #2980b9; margin-bottom: 15px;">
            {{ editingIndex > -1 ? '✏️ Đang chỉnh sửa hàng hóa' : '➕ Thêm hàng hóa mới vào lô' }}
          </h4>
          
          <form @submit.prevent="saveDetailItem">
            <div class="form-group">
              <label>Tên hàng / Mô tả chi tiết *</label>
              <input v-model="detailFormData.ten_hang" required placeholder="VD: Máy giặt Toshiba AW-DUK1150HV">
            </div>

            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
              <div class="form-group" style="flex: 2; min-width: 200px;">
                <label>Loại hàng hóa (Danh mục) *</label>
                <select v-model="detailFormData.ma_hang_hoa">
                  <option :value="null">-- Chọn loại hàng --</option>
                  <option v-for="h in listHangHoa" :key="h.ma_hang_hoa" :value="h.ma_hang_hoa">{{ h.ten_hang_hoa }}</option>
                </select>
              </div>
              <div class="form-group" style="flex: 1; min-width: 150px;">
                <label>Đơn vị tính *</label>
                <select v-model="detailFormData.ma_don_vi_tinh" required>
                  <option :value="null">-- Chọn ĐVT --</option>
                  <option v-for="dvt in listDonViTinh" :key="dvt.ma_don_vi_tinh" :value="dvt.ma_don_vi_tinh">{{ dvt.ten_don_vi_tinh }}</option>
                </select>
              </div>
            </div>

            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
              <div class="form-group" style="flex: 1;">
                <label>Số lượng *</label>
                <input type="number" v-model="detailFormData.so_luong" required min="1">
              </div>
              <div class="form-group" style="flex: 1;">
                <label>Số kiện *</label>
                <input type="number" v-model="detailFormData.so_kien" required min="1">
              </div>
              <div class="form-group" style="flex: 1;">
                <label>Giá cả *</label>
                <input type="number" v-model="detailFormData.gia_ca" required min="0" step="1">
              </div>
              <div class="form-group" style="flex: 1;">
                <label>Trọng lượng (kg) *</label>
                <input type="number" v-model="detailFormData.trong_luong" required min="0" step="0.01">
              </div>
              <div class="form-group" style="flex: 1;">
                <label>Thể tích (CBM) *</label>
                <input type="number" v-model="detailFormData.the_tich" required min="0" step="0.0001">
              </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
              <button type="button" class="btn-cancel" @click="isDetailFormVisible = false" style="background: #e74c3c; color: white; border: none;">❌ Hủy bỏ</button>
              <button type="submit" class="btn-save" style="background: #27ae60; color: white; border: none;">
                {{ editingIndex > -1 ? '✅ Xác nhận cập nhật' : '✅ Xác nhận thêm' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Filter Toolbar -->
        <div class="filter-toolbar" style="display: flex; gap: 10px; margin-bottom: 15px; background: #fdfdfd; padding: 12px; border-radius: 8px; border: 1px solid #e1e4e8; align-items: center; flex-wrap: wrap;">
          <div style="flex: 2; min-width: 200px;">
            <input v-model="searchTenHang" placeholder="🔍 Tìm tên hàng / mô tả..." style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;">
          </div>
          
          <select v-model="filterHangHoa" style="flex: 1; min-width: 150px; padding: 8px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer;">
            <option :value="null">📂 Loại hàng (Tất cả)</option>
            <option v-for="h in listHangHoa" :key="h.ma_hang_hoa" :value="h.ma_hang_hoa">{{ h.ten_hang_hoa }}</option>
          </select>

          <select v-model="filterDVT" style="flex: 1; min-width: 120px; padding: 8px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer;">
            <option :value="null">📏 ĐVT (Tất cả)</option>
            <option v-for="dvt in listDonViTinh" :key="dvt.ma_don_vi_tinh" :value="dvt.ma_don_vi_tinh">{{ dvt.ten_don_vi_tinh }}</option>
          </select>

          <select v-model="filterUser" style="flex: 1; min-width: 150px; padding: 8px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer;">
            <option :value="null">👤 Người sửa (Tất cả)</option>
            <option v-for="user in uniqueUsers" :key="user" :value="user">{{ user }}</option>
          </select>
          
          <button @click="resetFilters" class="btn-cancel" style="padding: 0 15px; height: 36px; background: #95a5a6; color: white; border: none; font-size: 13px;">🧹 Xóa lọc</button>
        </div>

        <!-- Pagination Controls -->
        <div v-if="listDetails.length > 0" class="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e1e4e8;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <span>Hiển thị</span>
            <select v-model="pageSize" style="padding: 5px 8px; border-radius: 5px; border: 1px solid #ccc;">
              <option v-for="size in pageSizes" :key="size" :value="size">{{ size }}</option>
            </select>
            <span>mục mỗi trang</span>
          </div>
          <div style="display: flex; align-items: center; gap: 10px;">
            <button @click="prevPage" :disabled="currentPage === 1" class="btn-pagination">◀ Trước</button>
            <span style="font-weight: bold;">Trang {{ currentPage }} / {{ totalPages }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages" class="btn-pagination">Sau ▶</button>
          </div>
        </div>

        <div v-if="filteredDetailsPaginated.length === 0 && listDetails.length > 0" style="text-align: center; padding: 20px; color: #7f8c8d; background: #fefefe; border-radius: 8px; border: 1px dashed #ccc; margin-bottom: 15px;">
          Không tìm thấy chi tiết hàng hóa nào phù hợp với bộ lọc hiện tại.
        </div>
        <div v-else-if="listDetails.length === 0" style="text-align: center; padding: 30px; color: #e74c3c; font-weight: bold; background: #fefefe; border-radius: 8px; border: 1px dashed #ccc; margin-bottom: 15px;">
          ⚠️ Lô hàng chưa có chi tiết hàng hóa. Vui lòng thêm ít nhất 1 mục!
        </div>

        <table class="detail-table" style="width: 100%; border-collapse: collapse;">
          <thead style="background: #f1f3f5;">
            <tr>
              <th style="width: 50px; text-align: center;">STT</th>
              <th style="min-width: 200px;">Tên hàng</th>
              <th>Loại hàng</th>
              <th @click="sortBy('so_luong')" style="cursor: pointer; user-select: none;" title="Sắp xếp Số lượng">Số lượng {{ getSortIcon('so_luong') }}</th>
              <th @click="sortBy('so_kien')" style="cursor: pointer; user-select: none;" title="Sắp xếp Số kiện">Số kiện {{ getSortIcon('so_kien') }}</th>
              <th>ĐVT</th>
              <th @click="sortBy('the_tich')" style="cursor: pointer; user-select: none;" title="Sắp xếp Thể tích">Thể tích {{ getSortIcon('the_tich') }}</th>
              <th @click="sortBy('trong_luong')" style="cursor: pointer; user-select: none;" title="Sắp xếp Trọng lượng">Trọng lượng {{ getSortIcon('trong_luong') }}</th>
              <th @click="sortBy('gia_ca')" style="cursor: pointer; user-select: none;" title="Sắp xếp Giá cả">Giá cả {{ getSortIcon('gia_ca') }}</th>
              <th>Người sửa cuối</th>
              <th style="text-align: center;">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in filteredDetailsPaginated" :key="item.ma_chi_tiet_lo_hang || 'new-' + index">
              <td style="text-align: center; color: #7f8c8d;">{{ (currentPage - 1) * pageSize + index + 1 }}</td>
              <td class="fw-bold" style="color: #2c3e50;">{{ item.ten_hang }}</td>
              <td>{{ getHangHoaName(item.ma_hang_hoa) }}</td>
              <td style="text-align: center;">{{ item.so_luong }}</td>
              <td style="text-align: center;">{{ item.so_kien }}</td>
              <td>{{ getDonViTinhName(item.ma_don_vi_tinh) }}</td>
              <td style="text-align: center;">{{ item.the_tich }}</td>
              <td style="text-align: center;">{{ item.trong_luong }}</td>
              <td style="text-align: right;">{{ item.gia_ca }}</td>
              <td style="color: #2980b9;">{{ item.nguoi_sua_cuoi }}</td>
              <td style="text-align: center;">
                <button @click="handleEdit(item)" class="action-btn text-primary" title="Sửa">✏️</button>
                <button @click="deleteDetail(item)" class="action-btn text-danger" title="Xóa">🗑️</button>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="modal-actions" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
          <button class="btn-cancel" @click="activeTab = 'info'" type="button" style="background: #95a5a6; color: white; border: none;">⬅ Quay lại</button>
          <button class="btn-save" @click="handleSaveAll" :disabled="isSaving" style="margin-left: 10px;">
            {{ isSaving ? 'Đang xử lý...' : 'HOÀN TẤT & ĐÓNG 💾' }}
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL CHỌN BOOKING -->
    <div v-if="isBookingPickerOpen" class="modal-overlay">
      <div class="modal-content" style="max-width: 1000px; width: 95%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">
          <h3 style="margin: 0; color: #2980b9;">🚢 Danh sách Booking khả dụng</h3>
          <button @click="isBookingPickerOpen = false" class="close-panel" style="font-size: 24px;">&times;</button>
        </div>

        <div style="display: flex; gap: 20px; align-items: flex-start;">
          <!-- Bảng danh sách bên trái -->
          <div style="flex: 1; max-height: 500px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px;">
            <table class="detail-table" style="width: 100%; border-collapse: collapse;">
              <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">
                <tr>
                  <th style="padding: 12px;">Số Booking</th>
                  <th>Tàu / Chuyến</th>
                  <th style="text-align: center;">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="bk in listBooking" :key="bk.ma_booking" :style="previewBooking?.ma_booking === bk.ma_booking ? 'background: #eef7ff' : ''">
                  <td style="padding: 12px; font-weight: bold; color: #2980b9;">{{ bk.so_booking }}</td>
                  <td>{{ bk.ten_con_tau }} / {{ bk.so_chuyen }}</td>
                  <td style="text-align: center; width: 180px;">
                    <button type="button" @click="previewBooking = bk" class="view-btn" style="margin-right: 5px;">👁️ Xem trước</button>
                    <button type="button" @click="selectBooking(bk)" class="btn-save" style="font-size: 13px; height: auto;">✅ Chọn</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Panel xem chi tiết bên phải (Chỉ hiện khi nhấn Xem trước) -->
          <div v-if="previewBooking" class="side-panel-booking" style="position: static; width: 350px; border: 1px solid #3498db;">
            <h4 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #2980b9;">📋 Chi tiết: {{ previewBooking.so_booking }}</h4>
            <div class="info-row"><span>Số Booking:</span> <strong>{{ previewBooking.so_booking }}</strong></div>
            <div class="info-row"><span>Hãng tàu:</span> <strong>{{ previewBooking.ten_hang_tau || 'N/A' }}</strong></div>
            <div class="info-row"><span>Tên tàu:</span> <strong>{{ previewBooking.ten_con_tau }}</strong></div>
            <div class="info-row"><span>Số chuyến:</span> <strong>{{ previewBooking.so_chuyen }}</strong></div>
            <div class="info-row"><span>Giờ cắt máng:</span> <strong class="text-danger">{{ formatDateTime(previewBooking.gio_cat_mang) }}</strong></div>
            <hr>
            <div class="info-row"><span>Cảng đi (POL):</span> <strong>{{ previewBooking.ten_cang_di || '---' }}</strong></div>
            <div class="info-row"><span>Cảng đến (POD):</span> <strong>{{ previewBooking.ten_cang_den || '---' }}</strong></div>
            <div class="info-row"><span>Ngày đi (ETD):</span> <strong class="text-primary">{{ formatDateTime(previewBooking.etd) }}</strong></div>
            <div class="info-row"><span>Ngày đến (ETA):</span> <strong class="text-success">{{ formatDateTime(previewBooking.eta) }}</strong></div>
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
import { ref, onMounted, nextTick, computed, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const activeTab = ref('info');
const isSaving = ref(false);
const isSavingDetail = ref(false);
const isDetailFormVisible = ref(false);
const editingIndex = ref(-1);

const listKhachHang = ref([]);
const listBooking = ref([]);
const listDetails = ref([]); // Đây là mảng đệm (buffer) để hiển thị và sửa local
const listDeletedDetails = ref([]); // Lưu ID của các chi tiết bị xóa để gửi lên server khi ấn Hoàn tất
const listHangHoa = ref([]);
const listDonViTinh = ref([]);

const isBookingPickerOpen = ref(false);
const previewBooking = ref(null);
const showBookingPanel = ref(false);

const formatDateTime = (dateString) => {
  if (!dateString || dateString.startsWith('1970') || dateString.startsWith('0000')) return "--";
  const d = new Date(dateString);
  return `${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')} ${d.toLocaleDateString('vi-VN')}`;
};

const selectedBooking = computed(() => {
  return listBooking.value.find(b => b.ma_booking === formData.value.ma_booking);
});

// Pagination State
const currentPage = ref(1);
const pageSize = ref(10);

// Filter & Sort State
const searchTenHang = ref('');
const filterHangHoa = ref(null);
const filterDVT = ref(null);
const filterUser = ref(null);
const sortConfig = ref({ key: null, direction: 'asc' });

const formData = ref({
  ma_lo_hang: null, ten_lo_hang: '', dieu_kien_thuong_mai: 'FOB',
  trang_thai_lo_hang: 'Mới tạo', nguon_goc: '',
  ma_booking: null, ma_khach_hang: null, nguoi_sua_cuoi: null
});

const detailFormData = ref({
  ma_chi_tiet_lo_hang: null, ten_hang: '', so_luong: 1, so_kien: 0,
  the_tich: 0, trong_luong: 0, gia_ca: 0, ma_hang_hoa: null,
  ma_lo_hang: null, ma_don_vi_tinh: null, nguoi_sua_cuoi: null
});

// Ánh xạ tên từ ID để hiển thị bảng
const getHangHoaName = (id) => listHangHoa.value.find(x => x.ma_hang_hoa === id)?.ten_hang_hoa || '---';
const getDonViTinhName = (id) => listDonViTinh.value.find(x => x.ma_don_vi_tinh === id)?.ten_don_vi_tinh || '---';

// Logic Lọc và Sắp xếp
const filteredAndSortedDetails = computed(() => {
  let result = [...listDetails.value];
  
  // 1. Lọc theo tên
  if (searchTenHang.value) {
    const q = searchTenHang.value.toLowerCase();
    result = result.filter(item => item.ten_hang?.toLowerCase().includes(q));
  }
  
  // 2. Lọc theo loại hàng
  if (filterHangHoa.value) {
    result = result.filter(item => item.ma_hang_hoa === filterHangHoa.value);
  }
  
  // 3. Lọc theo DVT
  if (filterDVT.value) {
    result = result.filter(item => item.ma_don_vi_tinh === filterDVT.value);
  }
  
  // 4. Lọc theo Người sửa
  if (filterUser.value) {
    result = result.filter(item => item.nguoi_sua_cuoi === filterUser.value);
  }

  // 5. Sắp xếp
  if (sortConfig.value.key) {
    const { key, direction } = sortConfig.value;
    result.sort((a, b) => {
      let vA = a[key] ?? 0;
      let vB = b[key] ?? 0;
      // Chuyển đổi sang số nếu là chuỗi chứa số để so sánh chính xác
      if (typeof vA === 'string' && !isNaN(vA)) vA = parseFloat(vA);
      if (typeof vB === 'string' && !isNaN(vB)) vB = parseFloat(vB);
      
      if (vA < vB) return direction === 'asc' ? -1 : 1;
      if (vA > vB) return direction === 'asc' ? 1 : -1;
      return 0;
    });
  }
  return result;
});

// Computed property for paginated details
const filteredDetailsPaginated = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredAndSortedDetails.value.slice(start, end);
});

// Computed property for total pages
const totalPages = computed(() => {
  return Math.ceil(filteredAndSortedDetails.value.length / pageSize.value);
});

const pageSizes = [10, 20, 50];

// Lấy danh sách người sửa duy nhất cho combo box
const uniqueUsers = computed(() => {
  const users = listDetails.value.map(item => item.nguoi_sua_cuoi).filter(Boolean);
  return [...new Set(users)];
});

const resetFilters = () => {
  searchTenHang.value = '';
  filterHangHoa.value = null;
  filterDVT.value = null;
  filterUser.value = null;
  currentPage.value = 1;
};

const addDetail = () => {
  editingIndex.value = -1;
  detailFormData.value = {
    ma_chi_tiet_lo_hang: null, ten_hang: '', so_luong: 1, so_kien: 1,
    the_tich: 0, trong_luong: 0, gia_ca: 0, ma_hang_hoa: null,
    ma_lo_hang: formData.value.ma_lo_hang, ma_don_vi_tinh: null
  };
  currentPage.value = 1; 
  isDetailFormVisible.value = true;
};

const handleEdit = (item) => {
  const index = listDetails.value.findIndex(x => x === item);
  editingIndex.value = index;
  detailFormData.value = { ...listDetails.value[index] };
  isDetailFormVisible.value = true;
  currentPage.value = Math.ceil((index + 1) / pageSize.value); 
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const selectBooking = (bk) => {
  formData.value.ma_booking = bk.ma_booking;
  isBookingPickerOpen.value = false;
  previewBooking.value = null;
};

const sortBy = (key) => {
  if (sortConfig.value.key === key) {
    sortConfig.value.direction = sortConfig.value.direction === 'asc' ? 'desc' : 'asc';
  } else {
    sortConfig.value.key = key;
    sortConfig.value.direction = 'asc';
  }
};

const getSortIcon = (key) => {
  if (sortConfig.value.key !== key) return '↕️';
  return sortConfig.value.direction === 'asc' ? '🔼' : '🔽';
};

// Chỉ lưu vào mảng local, chưa gửi API
const saveDetailItem = () => {
  // Validation logic trước khi xác nhận thêm/sửa
  const d = detailFormData.value;
  if (!d.ten_hang || d.ten_hang.trim() === '') {
    alert("Vui lòng nhập tên hàng hoặc mô tả chi tiết!");
    return;
  }
  if (!d.ma_hang_hoa) {
    alert("Vui lòng chọn loại hàng hóa (danh mục)!");
    return;
  }
  if (!d.ma_don_vi_tinh) {
    alert("Vui lòng chọn đơn vị tính!");
    return;
  }
  if (d.so_luong === null || d.so_luong <= 0) {
    alert("Số lượng hàng hóa phải lớn hơn 0!");
    return;
  }
  if (d.so_kien === null || d.so_kien <= 0) {
    alert("Số kiện phải phải lớn hơn 0!");
    return;
  }
  if (d.trong_luong === null || d.trong_luong <= 0) {
    alert("Trọng lượng phải lớn hơn 0!");
    return;
  }
  if (d.the_tich === null || d.the_tich <= 0) {
    alert("Thể tích phải lớn hơn 0!");
    return;
  }
  if (d.gia_ca === null || d.gia_ca < 0) {
    alert("Giá cả không hợp lệ (không được để trống hoặc nhỏ hơn 0)!");
    return;
  }

  if (editingIndex.value > -1) {
    listDetails.value[editingIndex.value] = { ...detailFormData.value };
  } else {
    listDetails.value.push({ ...detailFormData.value });
  }
  // currentPage.value = 1;
  isDetailFormVisible.value = false;
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

watch([searchTenHang, filterHangHoa, filterDVT, filterUser, pageSize], () => {
  currentPage.value = 1;
});

const fetchDetailReferences = async () => {
  if (listHangHoa.value.length > 0 && listDonViTinh.value.length > 0) {
    return;
  }
  const res = await fetch('http://127.0.0.1:8000/api/chi-tiet-lo-hang/references');
  const data = await res.json();
  if (data.success) {
    listHangHoa.value = data.hang_hoa;
    listDonViTinh.value = data.don_vi_tinh;
  }
};

const deleteDetail = (item) => {
  if (item.ma_chi_tiet_lo_hang) {
    listDeletedDetails.value.push(item.ma_chi_tiet_lo_hang);
  }
  listDetails.value.splice(listDetails.value.indexOf(item), 1); // Remove by item reference
};

const fetchReferences = async () => {
  const id = route.params.id || '';
  try {
    // 1. Lấy danh sách ID booking hợp lệ (trống hoặc của chính lô hàng này)
    const resRef = await fetch(`http://127.0.0.1:8000/api/lo-hang/references?ma_lo_hang=${id}`);
    const dataRef = await resRef.json();

    // 2. Lấy dữ liệu chi tiết của tất cả booking
    const resAllBk = await fetch('http://127.0.0.1:8000/api/bookings');
    const dataAllBk = await resAllBk.json();

    if (dataRef.success && dataAllBk.success) {
      listKhachHang.value = dataRef.khach_hang;
      // 3. Lọc lại danh sách chi tiết: chỉ giữ những booking ID được backend cho phép
      const validIds = dataRef.booking.map(b => b.ma_booking);
      listBooking.value = dataAllBk.data.filter(bk => validIds.includes(bk.ma_booking));
    }
  } catch (error) {
    console.error("Lỗi đồng bộ dữ liệu Booking:", error);
  }
};

const fetchDetails = async (ma_lo_hang) => {
  if (!ma_lo_hang) return;
  try {
    const resDetail = await fetch(`http://127.0.0.1:8000/api/chi-tiet-lo-hang?ma_lo_hang=${ma_lo_hang}`);
    const dataDetail = await resDetail.json();
    if (dataDetail.success) listDetails.value = dataDetail.data;
  } catch (error) { console.error(error); }
};

const fetchData = async (id) => {
  try {
    const res = await fetch(`http://127.0.0.1:8000/api/lo-hang`);
    const data = await res.json();
    if (data.success) {
      const found = data.data.find(x => String(x.ma_lo_hang) === String(id));
      if (found) {
        formData.value = { ...found };
        fetchDetails(found.ma_lo_hang);
      }
    }
  } catch (error) { console.error(error); }
};

const handleSaveStep1 = async () => {
  if (!formData.value.ma_khach_hang) {
    alert("Vui lòng chọn khách hàng!");
    return;
  }

  isSaving.value = true;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  formData.value.nguoi_sua_cuoi = user ? (user.id || user.ma_tai_khoan) : null;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lo-hang/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData.value)
    });
    const data = await res.json();
    if (data.success) {
      if (!formData.value.ma_lo_hang && data.ma_lo_hang) {
        formData.value.ma_lo_hang = data.ma_lo_hang;
      }
      activeTab.value = 'details';
    } else alert(data.message);
  } catch (e) { alert("Lỗi server!"); }
  finally { isSaving.value = false; }
};

const handleSaveAll = async () => {
  // 0. Kiểm tra ràng buộc dữ liệu (Validation) trước khi cho phép lưu toàn bộ
  if (!formData.value.ten_lo_hang || formData.value.ten_lo_hang.trim() === '') {
    alert("Vui lòng nhập tên lô hàng tại Tab 1!");
    activeTab.value = 'info'; // Tự động quay về Tab 1
    return;
  }
  if (!formData.value.ma_khach_hang) {
    alert("Vui lòng chọn khách hàng tại Tab 1!");
    activeTab.value = 'info'; // Tự động quay về Tab 1
    return;
  }
  if (listDetails.value.length === 0) {
    alert("Lô hàng phải có ít nhất một mặt hàng chi tiết!");
    return;
  }

  isSaving.value = true;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  const userId = user ? (user.id || user.ma_tai_khoan) : null;
  formData.value.nguoi_sua_cuoi = userId;

  try {
    // 1. Lưu thông tin Lô hàng (Tab 1) trước
    const resLH = await fetch('http://127.0.0.1:8000/api/lo-hang/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData.value)
    });
    const dataLH = await resLH.json();
    if (!dataLH.success) throw new Error(dataLH.message || "Không thể lưu thông tin lô hàng");

    // Lấy ID lô hàng: Ưu tiên mã có sẵn, nếu không lấy từ phản hồi server (trường hợp tạo mới)
    const maLoHang = formData.value.ma_lo_hang || dataLH.ma_lo_hang || dataLH.id;
    if (!maLoHang) throw new Error("Hệ thống không trả về mã lô hàng mới.");
    
    // Cập nhật lại vào formData để đồng bộ trạng thái
    formData.value.ma_lo_hang = maLoHang;

    // 2. Xóa các chi tiết hàng đã được đánh dấu xóa
    for (const ma_ct of listDeletedDetails.value) {
      await fetch('http://127.0.0.1:8000/api/chi-tiet-lo-hang/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ma_chi_tiet_lo_hang: ma_ct, nguoi_sua_cuoi: userId }) // Truyền userId khi xóa
      });
    }

    // 3. Gán mã lô hàng vào từng chi tiết và lưu
    for (let i = 0; i < listDetails.value.length; i++) {
      const item = listDetails.value[i];
      const detailPayload = {
        ...item,
        ma_lo_hang: maLoHang, 
        nguoi_sua_cuoi: userId 
      };
      
      const resCT = await fetch('http://127.0.0.1:8000/api/chi-tiet-lo-hang/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(detailPayload)
      });
      const dataCT = await resCT.json();
      if (!dataCT.success) throw new Error(dataCT.message);

      // Nếu là chi tiết mới, cập nhật ma_chi_tiet_lo_hang từ phản hồi của server
      if (!item.ma_chi_tiet_lo_hang && dataCT.ma_chi_tiet_lo_hang) {
        listDetails.value[i].ma_chi_tiet_lo_hang = dataCT.ma_chi_tiet_lo_hang;
      }
    }

    router.push('/lo-hang/thong-tin-lo-hang');
  } catch (e) {
    alert("Lỗi khi lưu dữ liệu: " + e.message);
  } finally { isSaving.value = false; }
};

const handleCancel = () => {
    if(confirm("Dữ liệu chưa lưu có thể bị mất. Bạn có muốn đóng không?")) {
        router.push('/lo-hang/thong-tin-lo-hang');
    }
}

onMounted(async () => {
  await fetchReferences();
  await fetchDetailReferences();
  const id = route.params.id;
  if (id) await fetchData(id);
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.tabs { display: flex; gap: 5px; margin-bottom: -1px; }
.tabs button {
  padding: 10px 20px; border: 1px solid #ddd; background: #f8f9fa;
  border-bottom: none; border-radius: 8px 8px 0 0; cursor: pointer;
  font-weight: bold; color: #7f8c8d; transition: 0.2s;
}
.tabs button.active { background: white; color: #3498db; border-top: 3px solid #3498db; }

.btn-cancel, .btn-save, .btn-success {
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 20px;
  font-size: 14px;
  box-sizing: border-box;
}

.detail-table th, .detail-table td { padding: 12px; border: 1px solid #eee; text-align: left; }
.action-btn {
  background: none; border: none; cursor: pointer; font-size: 16px; margin: 0 5px; transition: 0.2s;
}
.action-btn:hover { transform: scale(1.2); }

.modal-overlay {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;
}
.modal-content {
  background: white; padding: 25px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}
.form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #34495e; }
.form-group input, .form-group select {
  width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;
}

.view-btn {
  background: #ebf5fb; border: 1px solid #3498db; color: #3498db;
  border-radius: 4px; cursor: pointer; transition: 0.2s; font-weight: bold;
}
.view-btn:hover { background: #3498db; color: white; }

.side-panel-booking {
  width: 350px; background: #fff; border: 1px solid #ddd; border-radius: 8px;
  padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); height: fit-content;
  position: sticky; top: 10px; animation: slideIn 0.3s ease;
}
.info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
.info-row span { color: #7f8c8d; }
@keyframes slideIn { from { transform: translateX(20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>
