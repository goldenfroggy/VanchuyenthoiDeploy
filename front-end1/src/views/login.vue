<template>
  <div class="login-container">
    <div class="login-card">
      <div class="logo-box">
        <h2>SINCERE LOGISTICS</h2>
        <p>Hệ thống Quản lý Giao nhận</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div v-if="errorMessage" class="alert-error">
          {{ errorMessage }}
        </div>

        <div class="form-group">
          <label>Địa chỉ Email</label>
          <input 
            type="email" 
            v-model="email" 
            placeholder="Nhập email của bạn (VD: example@gmail.com)" 
            required
          />
        </div>

        <div class="form-group">
          <label>Mật Khẩu</label>
          <div style="position: relative;">
            <input 
              :type="showPassword ? 'text' : 'password'" 
              v-model="password" 
              placeholder="Nhập mật khẩu..." 
              required
              style="width: 100%; padding-right: 40px;"
            />
            <span @click="showPassword = !showPassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; user-select: none; font-size: 18px;">
              {{ showPassword ? '🔓' : '🔒' }}
            </span>
          </div>
        </div>

        <button type="submit" class="btn-login" :disabled="isLoading">
          {{ isLoading ? 'ĐANG XỬ LÝ...' : 'ĐĂNG NHẬP HỆ THỐNG' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

// Biến lưu trữ
const email = ref('');
const password = ref('');
const errorMessage = ref('');
const isLoading = ref(false);
const showPassword = ref(false);
const router = useRouter();

const handleLogin = async () => {
  isLoading.value = true;
  errorMessage.value = '';

  try {
    // SỬA ĐƯỜNG DẪN Ở ĐÂY SANG CỔNG 8000 CỦA LARAVEL
    const response = await fetch('http://127.0.0.1:8000/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json' // Bổ sung header này để Laravel hiểu đây là request API
      },
      body: JSON.stringify({
        email: email.value,
        mat_khau: password.value
      })
    });

    const data = await response.json();

    if (data.success) {
      // Lưu vào localStorage y như cũ
      localStorage.setItem('sincere_user', JSON.stringify(data.user));
      alert(data.message);
      router.push('/home');
    } else {
      errorMessage.value = data.message;
    }
  } catch (error) {
    errorMessage.value = "Không thể kết nối đến máy chủ Laravel. Vui lòng chạy lệnh 'php artisan serve'!";
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped src="../assets/login.css"></style>