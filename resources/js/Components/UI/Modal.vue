<script setup>
import Modal from '@/Components/Modal.vue'

defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  maxWidth: {
    type: String,
    default: '2xl',
  },
  closeable: {
    type: Boolean,
    default: true,
  },
})

defineEmits(['close'])
</script>

<template>
  <Modal :show="show" :max-width="maxWidth" :closeable="closeable" @close="$emit('close')">
    <slot />
  </Modal>
</template>
