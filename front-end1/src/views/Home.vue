<template>
  <div class="dashboard-home">
    <!-- Welcome Banner -->
    <div class="welcome-section">
      <div class="welcome-text">
        <h1>Chào mừng trở lại, {{ userName }}! 👋</h1>
        <p>Hệ thống SINCERE LOGISTICS đã sẵn sàng. Bạn có <strong>{{ stats.urgent }}</strong> lô hàng cần xử lý gấp.</p>
      </div>
      <div class="quick-actions">
        <button class="btn-action primary" @click="$router.push('/lo-hang/thong-tin-lo-hang/add')">📦 Tạo Lô Hàng</button>
        <button class="btn-action secondary" @click="$router.push('/lo-hang/booking/add')">📑 Thêm Booking</button>
      </div>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-grid">
      <div class="stat-card blue">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
          <span class="stat-label">Tổng Lô Hàng</span>
          <span class="stat-value">{{ stats.total }}</span>
        </div>
      </div>
      <div class="stat-card orange">
        <div class="stat-icon">🚢</div>
        <div class="stat-info">
          <span class="stat-label">Đang Vận Chuyển</span>
          <span class="stat-value">{{ stats.shipping }}</span>
        </div>
      </div>
      <div class="stat-card green">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
          <span class="stat-label">Đã Thông Quan</span>
          <span class="stat-value">{{ stats.cleared }}</span>
        </div>
      </div>
      <div class="stat-card red">
        <div class="stat-icon">⚠️</div>
        <div class="stat-info">
          <span class="stat-label">Cần xử lý gấp</span>
          <span class="stat-value">{{ stats.urgent }}</span>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-content">
      <!-- Recent Shipments Table -->
      <div class="content-box shipment-recent">
        <div class="box-header">
          <h3>Lô hàng mới cập nhật</h3>
          <router-link to="/lo-hang/thong-tin-lo-hang">Xem tất cả</router-link>
        </div>
        <table class="minimal-table">
          <thead>
            <tr>
              <th>Mã Lô</th>
              <th>Tên lô hàng</th>
              <th>Khách Hàng</th>
              <th>Trạng Thái</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="lh in recentShipments" :key="lh.ma_lo_hang">
              <td class="fw-bold">#{{ lh.ma_lo_hang }}</td>
              <td>{{ lh.ten_lo_hang }}</td>
              <td>{{ lh.ten_khach_hang || 'Chưa xác định' }}</td>
              <td><span class="status-tag" :class="getStatusClass(lh.trang_thai_lo_hang)">{{ lh.trang_thai_lo_hang }}</span></td>
            </tr>
            <tr v-if="recentShipments.length === 0">
              <td colspan="4" style="text-align: center; padding: 20px; color: #95a5a6;">Chưa có dữ liệu lô hàng.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Reminders/Alerts -->
      <div class="content-box alerts-box">
        <div class="box-header">
          <h3>Nhắc nhở công việc</h3>
        </div>
        <ul class="alert-list">
          <li v-for="(alert, idx) in alerts" :key="idx" class="alert-item" :class="alert.type">
            <span class="alert-time">{{ alert.time }}</span>
            <p v-html="alert.msg"></p>
          </li>
          <li v-if="alerts.length === 0" class="alert-item info">
            <p>Không có nhắc nhở quan trọng trong hôm nay.</p>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const userName = ref('Admin');
const stats = ref({ total: 0, shipping: 0, cleared: 0, urgent: 0 });
const recentShipments = ref([]);
const alerts = ref([]);

const noItemsCount = ref(0);

const getStatusClass = (status) => {
  if (status === 'Đang vận chuyển') return 'shipping';
  if (['Đã thông quan', 'Hoàn tất'].includes(status)) return 'done';
  return 'process';
};

const fetchData = async () => {
  try {
    alerts.value = []; // Reset danh sách nhắc nhở trước khi tải

    // 1. Lấy dữ liệu Lô Hàng
    const resLH = await fetch('http://127.0.0.1:8000/api/lo-hang');
    const dataLH = await resLH.json();
    
    if (dataLH.success) {
      const list = dataLH.data;
      stats.value.total = list.length;
      stats.value.shipping = list.filter(i => i.trang_thai_lo_hang === 'Đang vận chuyển').length;
      stats.value.cleared = list.filter(i => i.trang_thai_lo_hang === 'Đã thông quan').length;
      
      // Lấy 5 lô hàng mới nhất
      recentShipments.value = [...list].sort((a, b) => b.ma_lo_hang - a.ma_lo_hang).slice(0, 5);

      // Thêm nhắc nhở lô hàng chưa có chi tiết hàng hóa
      const noItems = list.filter(lh => lh.has_items == 0);
      noItemsCount.value = noItems.length;
      noItems.forEach(lh => {
        alerts.value.push({
          time: 'Dữ liệu trống',
          msg: `Lô hàng <strong>#${lh.ma_lo_hang}</strong> (${lh.ten_lo_hang}) chưa có chi tiết hàng hóa.`,
          type: 'warning'
        });
      });
    }

    // 2. Lấy dữ liệu Booking để tính toán lịch Closing
    const resBK = await fetch('http://127.0.0.1:8000/api/bookings');
    const dataBK = await resBK.json();
    
    if (dataBK.success) {
      const bookings = dataBK.data;
      const now = new Date();
      
      // Lọc các booking sắp đến giờ cắt máng (trong vòng 48 giờ tới)
      const urgentBookings = bookings.filter(b => {
        if (!b.gio_cat_mang) return false;
        const closing = new Date(b.gio_cat_mang);
        const diffHours = (closing - now) / (1000 * 60 * 60);
        return diffHours > 0 && diffHours < 48;
      });

      // Cập nhật tổng số việc gấp (Booking sắp đóng + Lô hàng thiếu dữ liệu)
      stats.value.urgent = urgentBookings.length + noItemsCount.value;

      // Thêm nhắc nhở booking sắp cắt máng vào danh sách
      urgentBookings.forEach(b => {
        alerts.value.push({
          time: `Hạn: ${new Date(b.gio_cat_mang).toLocaleString('vi-VN')}`,
          msg: `Booking <strong>${b.so_booking}</strong> (${b.ten_con_tau}) sắp đến giờ cắt máng.`,
          type: 'warning'
        });
      });

      // Thêm thông tin tàu cập cảng (ETA hôm nay/ngày mai)
      const etaSoon = bookings.filter(b => b.eta && new Date(b.eta).toDateString() === now.toDateString());
      etaSoon.forEach(b => {
        alerts.value.push({
          time: 'Hôm nay',
          msg: `Tàu <strong>${b.ten_con_tau}</strong> dự kiến cập cảng.`,
          type: 'info'
        });
      });
    }
  } catch (error) {
    console.error("Lỗi khi tải dữ liệu dashboard:", error);
  }
};

onMounted(() => {
  const userData = localStorage.getItem('sincere_user');
  if (userData) {
    const user = JSON.parse(userData);
    userName.value = user.ho_ten;
  }
  fetchData();
});
</script>

<style scoped src="../assets/home.css"></style>