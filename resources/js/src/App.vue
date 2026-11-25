<template>
  <div class="min-h-screen bg-[#f2f7fd] text-[#172b4d]">
    <ToastRegion />
    <router-view></router-view>
  </div>
</template>

<script setup>
import ToastRegion from '@/components/shared/ToastRegion.vue';
</script>
