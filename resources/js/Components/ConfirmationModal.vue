<script setup>
import {
  ExclamationCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'
import { computed, ref } from 'vue'

const props = defineProps({
  show: Boolean,
  title: String,
  message: String,
  type: {
    type: String,
    default: 'danger',
    validator: value => ['info', 'warning', 'danger'].includes(value),
  },
  confirmLabel: {
    type: String,
    default: 'Confirmer',
  },
  onConfirm: Function,
})

const emit = defineEmits(['close'])
const processing = ref(false)

function close() {
  emit('close')
}

function confirm() {
  close()
  props.onConfirm?.()
}

const confirmClasses = computed(() => {
  switch (props.type) {
    case 'info':
      return 'ui-button-secondary'
    case 'warning':
      return 'bg-istaht-amber text-white hover:bg-orange-600'
    case 'danger':
    default:
      return 'ui-button-danger'
  }
})

const iconColor = computed(() => {
  switch (props.type) {
    case 'info':
      return 'text-istaht-cyan'
    case 'warning':
      return 'text-istaht-amber'
    case 'danger':
    default:
      return 'text-istaht-red'
  }
})

const iconComponent = computed(() => {
  switch (props.type) {
    case 'info':
      return InformationCircleIcon
    case 'warning':
      return ExclamationCircleIcon
    case 'danger':
    default:
      return ExclamationTriangleIcon
  }
})
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center bg-istaht-navy/55 p-4 backdrop-blur-sm"
  >
    <div class="relative w-full max-w-md animate-fade-up rounded-lg border border-slate-200 bg-white p-6 shadow-panel">
      <button
        @click="close"
        class="ui-icon-button absolute right-3 top-3"
        aria-label="Fermer"
      >
        <XMarkIcon class="h-5 w-5" />
      </button>

      <div class="mb-4 flex items-center gap-3 pr-8">
        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-slate-50">
          <component :is="iconComponent" class="h-6 w-6" :class="iconColor" />
        </div>
        <h3 class="text-lg font-bold text-istaht-navy">{{ title }}</h3>
      </div>

      <p class="mb-6 text-sm leading-6 text-slate-600" v-html="message"></p>

      <div class="flex justify-end gap-3">
        <button @click="close" class="ui-button ui-button-ghost">
          Annuler
        </button>
        <button @click="confirm" :disabled="processing" :class="['ui-button', confirmClasses]">
          {{ confirmLabel }}
        </button>
      </div>
    </div>
  </div>
</template>
