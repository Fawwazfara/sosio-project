<template>
  <div class="inline-flex items-center justify-center bg-[#FDFCF9] p-3 rounded-xl border border-[#E6E1D6]" v-html="svgMarkup"></div>
</template>

<script setup>
import { ref, watchEffect } from 'vue';
import QRCode from 'qrcode';

const props = defineProps({
  value: {
    type: String,
    required: true,
  },
  size: {
    type: Number,
    default: 210,
  },
});

const svgMarkup = ref('');

watchEffect(async () => {
  if (!props.value) {
    svgMarkup.value = '';
    return;
  }
  try {
    svgMarkup.value = await QRCode.toString(props.value, {
      type: 'svg',
      width: props.size,
      margin: 1,
      errorCorrectionLevel: 'M',
      color: {
        dark: '#1C1917',
        light: '#FDFCF9',
      },
    });
  } catch (err) {
    console.error('QR generation error:', err);
    svgMarkup.value = '';
  }
});
</script>

