<template>
  <div class="flex items-center justify-center min-h-screen bg-[#F5F3EE] p-4">
    <div class="ui-panel-elevated p-7 sm:p-8 max-w-md w-full text-center space-y-5 ui-stagger ui-stagger-1">
      <div class="w-12 h-12 mx-auto rounded-[12px] bg-[#292524] text-white shadow-[var(--shadow-navy-card)] flex items-center justify-center font-bold text-lg">
        AS
      </div>
      <div class="space-y-1.5">
        <h1 class="text-2xl font-bold text-[#1C1917] tracking-tight">Madrasah As-Sajjaad</h1>
      </div>
      <router-link to="/login" class="ui-btn-primary ui-btn-icon-r w-full">
        <span>Masuk ke Portal</span>
        <UiIcon name="arrow_forward" :size="17" :weight="600" directional />
      </router-link>
    </div>
  </div>
</template>

<script setup>
</script>
