<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Kho Chứng Từ Số Hóa</h3>
    
    <div class="toolbar" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
      
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="🔍 Tìm theo Số Booking, Tên Lô hàng..."
          style="width: 100%; padding: 10px 15px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px;"
        >
      </div>

      <select v-model="filterLoai" style="padding: 10px; border-radius: 6px; border: 1px solid #ccc; width: 200px; font-weight: bold;">
        <option value="ALL">📂 Tất cả loại chứng từ</option>
        <option value="SC">Hợp đồng (SC)</option>
        <option value="INV">Hóa đơn (INV)</option>
        <option value="PKL">Phiếu đóng gói (PKL)</option>
        <option value="CO">C/O (Xuất xứ)</option>
        <option value="BL">Vận đơn (B/L)</option>
        <option value="DO">Lệnh giao hàng (D/O)</option>
        <option value="Khác">Khác</option>
      </select>

      <select v-model="filterLoHang" style="padding: 10px; border-radius: 6px; border: 1px solid #ccc; width: 250px; font-weight: bold;">
        <option value="ALL">📦 Tất cả lô hàng</option>
        <option v-for="lo in listLoHang" :key="lo.ma_lo_hang" :value="lo.ma_lo_hang">
          [{{ lo.so_booking }}] - {{ lo.ten_lo_hang }}
        </option>
      </select>

      <button @click="fetchData()" class="btn-refresh">🔄 Làm mới</button>
      <button class="btn btn-success" @click="openModal()" style="border-radius: 20px; padding: 10px 20px; box-shadow: 0 4px 6px rgba(46, 204, 113, 0.3);">
        ☁️ TẢI LÊN CHỨNG TỪ
      </button>
    </div>

    <div v-if="isLoading" class="loading-text">Đang tải tài liệu...</div>
    
    <div v-else class="document-grid">
      <div class="doc-card" v-for="doc in filteredDocs" :key="doc.ma_chung_tu">
        <div class="doc-image-wrapper" @click="handleViewFile(doc.hinh_anh)">
          <template v-if="isImage(doc.hinh_anh)">
            <img :src="getImageUrl(doc.hinh_anh)" alt="Chứng từ" class="doc-image" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2965/2965335.png'">
          </template>
          <template v-else>
            <div class="pdf-placeholder" style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f8f9fa;">
              <img src="https://cdn-icons-png.flaticon.com/512/337/337946.png" style="width: 60px; margin-bottom: 10px;">
              <span style="font-size: 12px; color: #7f8c8d;">Tài liệu PDF</span>
            </div>
          </template>
          <div class="zoom-overlay">🔍 Xem chi tiết</div>
        </div>
        
        <div class="doc-info">
          <span class="badge-type">{{ doc.loai_chung_tu }}</span>
          <h4 class="doc-title" :title="doc.ten_lo_hang">Lô: {{ doc.ten_lo_hang || 'Chưa gắn' }}</h4>
          <p class="doc-subtitle" style="font-weight: bold; color: #34495e;">B/K: {{ doc.so_booking || 'N/A' }}</p>
        </div>
        
        <div class="doc-actions" style="display: flex; justify-content: space-between; align-items: center;">
          <button class="btn-icon text-warning" @click="openModal(doc)" title="Sửa chứng từ này">
            ✏️ Sửa
          </button>
          
          <button class="btn-icon text-primary download-btn" @click="downloadFile(doc.hinh_anh, `${doc.loai_chung_tu}_${doc.so_booking}`)" title="Tải xuống máy tính">
            ⬇️ Tải về
          </button>
          
          <button class="btn-icon text-danger" @click="handleDelete(doc.ma_chung_tu)" title="Xóa tài liệu">
            🗑️
          </button>
        </div>
      </div>
      
      <div v-if="filteredDocs.length === 0" style="grid-column: 1 / -1; text-align: center; color: #95a5a6; padding: 40px; background: #fff; border-radius: 8px; border: 1px dashed #ccc;">
        <h3 style="margin-bottom: 10px;">Không tìm thấy chứng từ nào!</h3>
        <p>Thử thay đổi từ khóa tìm kiếm hoặc tải lên chứng từ mới.</p>
      </div>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content" :style="{ maxWidth: showLoHangPanel && selectedLoHang ? '900px' : '500px', width: '95%', transition: 'max-width 0.3s' }">
        <h3>{{ formData.ma_chung_tu ? 'Cập nhật chứng từ' : 'Tải lên chứng từ mới' }}</h3>
        
        <div style="display: flex; gap: 20px; align-items: flex-start;">
          <form @submit.prevent="saveDoc" style="flex: 1;">
          <div class="form-group">
            <label>Loại chứng từ *</label>
            <select v-model="formData.loai_chung_tu" required>
              <option value="SC">Sales Contract (SC)</option>
              <option value="INV">Commercial Invoice (INV)</option>
              <option value="PKL">Packing List (PKL)</option>
              <option value="CO">Certificate of Origin (CO)</option>
              <option value="BL">Bill of Lading (BL)</option>
              <option value="DO">Delivery Order (DO)</option>
              <option value="Khác">Tài liệu Khác</option>
            </select>
          </div>

          <div class="form-group">
            <label>Thuộc Lô hàng *</label>
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

          <div class="form-group upload-area">
            <label>Chọn file chứng từ (Ảnh/PDF) {{ formData.ma_chung_tu ? '(Bỏ trống nếu giữ nguyên)' : '*' }}</label>
            <input type="file" @change="handleFileUpload" accept="image/*,.pdf" :required="!formData.ma_chung_tu" class="file-input">
            <div v-if="previewUrl" class="preview-container">
              <img v-if="isImage(previewUrl) || (fileToUpload && fileToUpload.type.includes('image'))" :src="previewUrl" class="preview-img">
              <div v-else class="pdf-preview-box" style="padding: 20px; text-align: center; background: #eee; border-radius: 8px;">
                <img src="https://cdn-icons-png.flaticon.com/512/337/337946.png" style="width: 40px;">
                <p style="margin-top: 5px; font-size: 13px;">File đã chọn: {{ fileToUpload?.name }}</p>
              </div>
            </div>
          </div>

          <div class="modal-actions" style="margin-top: 20px;">
            <button type="button" class="btn-cancel" @click="isModalOpen = false">Hủy</button>
            <button type="submit" class="btn-save" :disabled="isSaving">
              {{ isSaving ? 'Đang lưu ⏳...' : 'Lưu lại 💾' }}
            </button>
          </div>
        </form>

        <!-- Side Panel hiển thị thông tin lô hàng đã chọn -->
        <div v-if="showLoHangPanel && selectedLoHang" class="side-panel-shipment" style="position: static; border: 1px solid #eee;">
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
        </div>
      </div>
    </div>

    <!-- MODAL CHỌN LÔ HÀNG -->
    <div v-if="isLoHangPickerOpen" class="modal-overlay" style="z-index: 1100;">
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
        </div>
      </div>
    </div>

    <div v-if="zoomedImage" class="lightbox-overlay" @click="zoomedImage = null">
      <img :src="getImageUrl(zoomedImage)" class="lightbox-img">
      <span class="lightbox-close">✖</span>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const listDocs = ref([]);
const listLoHang = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);
const zoomedImage = ref(null);

// State cho chức năng Picker và Preview lô hàng
const isLoHangPickerOpen = ref(false);
const previewLoHang = ref(null);
const showLoHangPanel = ref(false);
const shipmentSearchQuery = ref('');

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
  shipmentSearchQuery.value = ''; // Reset tìm kiếm sau khi chọn xong
};

const searchQuery = ref(''); 
const filterLoai = ref('ALL');
const filterLoHang = ref('ALL');
const fileToUpload = ref(null);
const previewUrl = ref('');

const formData = ref({
  ma_chung_tu: null,
  loai_chung_tu: 'INV',
  ma_lo_hang: null
});

const isImage = (path) => {
  if (!path) return true;
  const extension = path.split('.').pop().toLowerCase();
  return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension);
};

const getImageUrl = (path) => {
  if (!path) return 'https://cdn-icons-png.flaticon.com/512/2965/2965335.png';
  if (path.startsWith('http')) return path;
  if (path.startsWith('blob:')) return path; // Cho preview
  return `http://127.0.0.1:8000/${path}`;
};

// Hàm Download File bằng Blob để giải quyết lỗi tải ảnh
const downloadFile = async (path, fileName) => {
  try {
    const fileUrl = getImageUrl(path);
    const response = await fetch(fileUrl);
    if (!response.ok) throw new Error('Không thể tải file từ server');
    
    const blob = await response.blob();
    const blobUrl = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = blobUrl;
    
    const extension = path.includes('.') ? path.split('.').pop() : 'jpg';
    link.setAttribute('download', `${fileName}.${extension}`); 
    
    document.body.appendChild(link);
    link.click();
    
    document.body.removeChild(link);
    window.URL.revokeObjectURL(blobUrl);
  } catch (error) {
    console.error("Lỗi khi tải file:", error);
    // Fallback: Mở sang tab mới nếu lỗi CORS
    window.open(getImageUrl(path), '_blank');
  }
};

const filteredDocs = computed(() => {
  return listDocs.value.filter(doc => {
    const matchLoai = filterLoai.value === 'ALL' || doc.loai_chung_tu === filterLoai.value;
    const matchLoHang = filterLoHang.value === 'ALL' || doc.ma_lo_hang === filterLoHang.value;
    const search = searchQuery.value.toLowerCase();
    const matchSearch = !search || 
      (doc.so_booking && doc.so_booking.toLowerCase().includes(search)) || 
      (doc.ten_lo_hang && doc.ten_lo_hang.toLowerCase().includes(search));
    
    return matchLoai && matchLoHang && matchSearch;
  });
});

const fetchData = async () => {
  isLoading.value = true;
  try {
    const [resDoc, resLoHang] = await Promise.all([
      fetch('http://127.0.0.1:8000/api/chung-tu'),
      fetch('http://127.0.0.1:8000/api/lo-hang')
    ]);
    const dataDoc = await resDoc.json();
    const dataLoHang = await resLoHang.json();
    
    if (dataDoc.success) {
      listDocs.value = dataDoc.data;
    }
    if (dataLoHang.success) {
      // Lọc chỉ hiển thị các lô hàng chưa hoàn tất hoặc bị hủy
      listLoHang.value = dataLoHang.data.filter(lh => 
        lh.trang_thai_lo_hang !== 'Hoàn tất' && lh.trang_thai_lo_hang !== 'Hủy'
      );
    }
  } catch (error) {
    console.error("Lỗi lấy dữ liệu chứng từ!");
  } finally {
    isLoading.value = false;
  }
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    fileToUpload.value = file;
    previewUrl.value = URL.createObjectURL(file);
  }
};

// Cập nhật hàm openModal để xử lý cả Thêm và Sửa
const openModal = (doc = null) => {
  if (doc) {
    // Chế độ Sửa: Đổ dữ liệu cũ vào form
    formData.value = { 
      ma_chung_tu: doc.ma_chung_tu, 
      loai_chung_tu: doc.loai_chung_tu, 
      ma_lo_hang: doc.ma_lo_hang 
    };
    fileToUpload.value = null; // Chưa chọn file mới
    previewUrl.value = getImageUrl(doc.hinh_anh); // Hiển thị ảnh cũ
    showLoHangPanel.value = true;
  } else {
    // Chế độ Thêm mới
    formData.value = { ma_chung_tu: null, loai_chung_tu: 'INV', ma_lo_hang: null };
    fileToUpload.value = null;
    previewUrl.value = '';
    showLoHangPanel.value = false;
  }
  
  isModalOpen.value = true;
};

const handleViewFile = (path) => {
  if (!path) return;
  if (isImage(path)) {
    zoomedImage.value = path;
  } else {
    window.open(getImageUrl(path), '_blank');
  }
};

// Cập nhật hàm saveDoc để hỗ trợ API Update
const saveDoc = async () => {
  isSaving.value = true;
  const payload = new FormData();
  
  // Nếu có ID tức là đang sửa, gửi thêm ID lên server
  if (formData.value.ma_chung_tu) {
    payload.append('ma_chung_tu', formData.value.ma_chung_tu);
  }
  payload.append('loai_chung_tu', formData.value.loai_chung_tu);
  payload.append('ma_lo_hang', formData.value.ma_lo_hang);
  
  // Chỉ gửi file lên nếu người dùng chọn file mới
  if (fileToUpload.value) {
    payload.append('hinh_anh', fileToUpload.value);
  }

  try {
    const res = await fetch('http://127.0.0.1:8000/api/chung-tu/save', {
      method: 'POST',
      body: payload
    });
    const data = await res.json();
    if (data.success) {
      isModalOpen.value = false;
      fetchData();
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (e) {
    alert("Lỗi kết nối upload server!");
  } finally {
    isSaving.value = false;
  }
};

const handleDelete = async (id) => {
  if (!confirm("Hủy vĩnh viễn chứng từ này khỏi hệ thống?")) return;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/chung-tu/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ma_chung_tu: id })
    });
    const data = await res.json();
    if (data.success) fetchData();
  } catch (e) {
    alert("Lỗi!");
  }
};

onMounted(fetchData);
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped src="../../assets/quanlychungtu.css"></style>
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
  animation: slideIn 0.3s ease;
}

.info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
.info-row span { color: #7f8c8d; }
.info-row strong { color: #2c3e50; text-align: right; }

@keyframes slideIn { from { transform: translateX(20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

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