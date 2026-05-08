<template>
  <div class="he-thong-page">
    <h1>Báo cáo thống kê</h1>

    <div class="sub-content">
      <router-view />
    </div>
  </div>
</template>

<style scoped>
.he-thong-page { padding: 8px; color: #2a3d52; }
.he-thong-page h1 { margin-bottom: 12px; color: #2c3e50; }
.sub-content { background: white; border: 1px solid #dfe6e9; border-radius: 8px; padding: 16px; min-height: 300px; color: #2a3d52; }
</style>
