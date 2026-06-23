<script setup>
defineProps({
  padded: {
    type: Boolean,
    default: true,
  },
})
</script>

<template>
  <section class="ui-card rounded-lg" :class="padded ? 'p-5' : ''">
    <slot />
  </section>
</template>
