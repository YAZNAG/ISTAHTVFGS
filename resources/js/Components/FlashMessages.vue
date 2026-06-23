<script setup>
import { ref, onMounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import {
  CheckCircleIcon,
  ExclamationCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'

const page = usePage()
const messages = ref([])

const addMessage = (text, type = 'info', duration = 5000) => {
  const id = Date.now() + Math.random()

  messages.value.push({ id, text, type })

  if (duration > 0) {
    setTimeout(() => removeMessage(id), duration)
  }
}

const removeMessage = (id) => {
  const index = messages.value.findIndex(message => message.id === id)
  if (index > -1) messages.value.splice(index, 1)
}

const getIcon = (type) => {
  switch (type) {
    case 'success': return CheckCircleIcon
    case 'error': return ExclamationCircleIcon
    case 'warning': return ExclamationTriangleIcon
    default: return InformationCircleIcon
  }
}

const toneClasses = (type) => ({
  success: 'border-green-200 bg-green-50 text-istaht-green',
  error: 'border-red-200 bg-red-50 text-istaht-red',
  warning: 'border-amber-200 bg-amber-50 text-amber-800',
  info: 'border-cyan-200 bg-cyan-50 text-istaht-blue',
}[type] || 'border-cyan-200 bg-cyan-50 text-istaht-blue')

const processMessages = () => {
  const flash = page.props.flash || {}

  Object.entries(flash).forEach(([type, message]) => {
    if (message && ['success', 'error', 'warning', 'info'].includes(type)) {
      addMessage(message, type)
    }
  })

  if (page.props.errors && Object.keys(page.props.errors).length > 0) {
    addMessage(Object.values(page.props.errors)[0], 'error')
  }
}

onMounted(processMessages)
watch(() => page.props, processMessages, { deep: true })
</script>

<template>
  <div class="pointer-events-none fixed bottom-5 right-4 z-50 w-[calc(100%-2rem)] max-w-sm space-y-2">
    <transition-group name="toast" tag="div" class="space-y-2">
      <div
        v-for="message in messages"
        :key="message.id"
        class="pointer-events-auto flex items-start gap-3 rounded-lg border p-4 shadow-panel backdrop-blur-sm"
        :class="toneClasses(message.type)"
      >
        <component :is="getIcon(message.type)" class="mt-0.5 h-5 w-5 shrink-0" />
        <div class="min-w-0 flex-1 text-sm font-semibold leading-5">{{ message.text }}</div>
        <button
          @click="removeMessage(message.id)"
          class="rounded-md p-1 transition hover:bg-black/5"
          aria-label="Fermer le message"
        >
          <XMarkIcon class="h-4 w-4" />
        </button>
      </div>
    </transition-group>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: opacity 0.24s ease, transform 0.24s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateY(12px);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(24px);
}
</style>
