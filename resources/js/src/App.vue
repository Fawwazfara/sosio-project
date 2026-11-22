<template>
  <div class="min-h-screen bg-[#F5F3EE] text-[#1C1917]">
    <ToastRegion />
    <router-view></router-view>
  </div>
</template>

<script setup>
import ToastRegion from '@/components/shared/ToastRegion.vue';
</script>
