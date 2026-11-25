<template>
  <section class="edu-hero">
    <img
      :src="image"
      :alt="imageAlt"
      class="edu-hero-photo"
      fetchpriority="high"
    />
    <div class="edu-hero-wash"></div>
    <div class="edu-hero-copy">
      <h2>
        Assalamu’alaikum,<br /><span>{{ name }}!</span>
      </h2>
      <router-link :to="to" class="ui-btn-primary"
        >{{ action }}<UiIcon name="arrow_forward" :size="18"
      /></router-link>
    </div>
  </section>
</template>

<script setup>
defineProps({
  name: { type: String, required: true },
  action: { type: String, required: true },
  to: { type: String, required: true },
  image: { type: String, default: '/images/education/indonesia-classroom.jpg' },
  imageAlt: {
    type: String,
    default:
      'Guru dan siswi berhijab dalam kegiatan belajar di Pandeglang, Banten',
  },
});
</script>
