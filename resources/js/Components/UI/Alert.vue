<script setup>
import { computed } from 'vue'
import {
  CheckCircleIcon,
  ExclamationCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  type: {
    type: String,
    default: 'info',
    validator: value => ['info', 'success', 'warning', 'error'].includes(value),
  },
})

const config = computed(() => ({
  info: { icon: InformationCircleIcon, classes: 'border-cyan-200 bg-cyan-50 text-istaht-blue' },
  success: { icon: CheckCircleIcon, classes: 'border-green-200 bg-green-50 text-istaht-green' },
  warning: { icon: ExclamationTriangleIcon, classes: 'border-amber-200 bg-amber-50 text-amber-800' },
  error: { icon: ExclamationCircleIcon, classes: 'border-red-200 bg-red-50 text-istaht-red' },
}[props.type]))
</script>

<template>
  <div class="flex gap-3 rounded-lg border p-4 text-sm font-medium" :class="config.classes">
    <component :is="config.icon" class="mt-0.5 h-5 w-5 shrink-0" />
    <div class="min-w-0">
      <slot />
    </div>
  </div>
</template>
