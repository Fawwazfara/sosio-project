<template>
  <div class="min-h-screen bg-[#F2F4F8] text-[#0F172A]">
    <ToastRegion />
    <router-view></router-view>
  </div>
</template>

<script setup>
import ToastRegion from '@/components/shared/ToastRegion.vue';
</script>
