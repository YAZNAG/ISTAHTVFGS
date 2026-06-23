<script setup>
import { computed } from 'vue'

const props = defineProps({
  tone: {
    type: String,
    default: 'neutral',
    validator: value => ['neutral', 'info', 'success', 'warning', 'danger'].includes(value),
  },
})

const classes = computed(() => ({
  neutral: 'bg-slate-100 text-slate-700 ring-slate-200',
  info: 'bg-cyan-50 text-istaht-blue ring-cyan-200',
  success: 'bg-green-50 text-istaht-green ring-green-200',
  warning: 'bg-amber-50 text-amber-700 ring-amber-200',
  danger: 'bg-red-50 text-istaht-red ring-red-200',
}[props.tone]))
</script>

<template>
  <span class="ui-badge" :class="classes">
    <slot />
  </span>
</template>
