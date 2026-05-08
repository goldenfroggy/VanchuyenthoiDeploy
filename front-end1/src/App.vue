<template>
  <RouterView />
</template>

<style>
/* Bỏ chữ scoped đi để CSS này làm bá chủ toàn website */
html, body, #app {
  margin: 0 !important;
  padding: 0 !important;
  width: 100vw !important;
  max-width: none !important;
  height: 100vh !important;
  display: block !important;
  overflow-x: hidden !important; /* Chống cuộn ngang */
}
</style>