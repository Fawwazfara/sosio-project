<template>
  <div class="flex items-center justify-center min-h-screen bg-[#f2f7fd] p-4">
    <div class="ui-panel-elevated p-7 sm:p-8 max-w-md w-full text-center space-y-5 ui-stagger ui-stagger-1">
      <UiIcon name="school" :size="40" class="mx-auto text-[#1264c7]" />
      <div class="space-y-1.5">
        <h1 class="text-2xl font-bold text-[#172b4d] tracking-tight">As-Sajjaad</h1>
      </div>
      <router-link to="/login" class="ui-btn-primary ui-btn-icon-r w-full">
        <span>Masuk</span>
        <UiIcon name="arrow_forward" :size="17" :weight="600" directional />
      </router-link>
    </div>
  </div>
</template>

<script setup>
</script>
