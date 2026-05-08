<template>
  <div class="danh-muc-page">
    <h1>Quản lý Danh mục</h1>

    <div class="sub-content">
      <router-view />
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.danh-muc-page { padding: 8px; color: #2a3d52; }
.danh-muc-page h1 { margin-bottom: 12px; color: #2c3e50; }
.sub-content { background: white; border: 1px solid #dfe6e9; border-radius: 8px; padding: 16px; min-height: 300px; color: #2a3d52; }
.sub-content p { color: #2a3d52; }
</style>
