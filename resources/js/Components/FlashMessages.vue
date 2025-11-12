<!-- resources/js/Components/FlashMessages.vue -->
<script setup>
import { ref, onMounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { 
  CheckCircleIcon,
  ExclamationCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const page = usePage()
const messages = ref([])

const addMessage = (text, type = 'info', duration = 5000) => {
  const id = Date.now() + Math.random()
  
  messages.value.push({
    id,
    text,
    type
  })
  
  // Auto-remove after duration
  if (duration > 0) {
    setTimeout(() => {
      removeMessage(id)
    }, duration)
  }
}

const removeMessage = (id) => {
  const index = messages.value.findIndex(m => m.id === id)
  if (index > -1) {
    messages.value.splice(index, 1)
  }
}

const getIcon = (type) => {
  switch (type) {
    case 'success': return CheckCircleIcon
    case 'error': return ExclamationCircleIcon
    case 'warning': return ExclamationTriangleIcon
    default: return InformationCircleIcon
  }
}

// Process flash messages
const processMessages = () => {
  const flash = page.props.flash || {}
  
  Object.entries(flash).forEach(([type, message]) => {
    if (message && ['success', 'error', 'warning', 'info'].includes(type)) {
      addMessage(message, type)
    }
  })
  
  // Handle validation errors
  if (page.props.errors && Object.keys(page.props.errors).length > 0) {
    const firstError = Object.values(page.props.errors)[0]
    addMessage(firstError, 'error')
  }
}

onMounted(() => {
  processMessages()
})

watch(() => page.props, () => {
  processMessages();

}, { deep: true })
</script>

<template>
  <div class="fixed bottom-5 right-4 z-50 space-y-2 max-w-sm w-full pointer-events-none">
    <transition-group name="toast" tag="div" class="space-y-2">
      <div
        v-for="message in messages"
        :key="message.id"
        class="pointer-events-auto flex items-start space-x-3 p-4 rounded-lg shadow-lg border backdrop-blur-sm"
        :class="{
          'bg-green-50 border-green-200 text-green-800': message.type === 'success',
          'bg-red-50 border-red-200 text-red-800': message.type === 'error',
          'bg-yellow-50 border-yellow-200 text-yellow-800': message.type === 'warning',
          'bg-blue-50 border-blue-200 text-blue-800': message.type === 'info',
        }"
      >
        <!-- Icon -->
        <component 
          :is="getIcon(message.type)" 
          class="w-5 h-5 mt-0.5 flex-shrink-0"
          :class="{
            'text-green-600': message.type === 'success',
            'text-red-600': message.type === 'error',
            'text-yellow-600': message.type === 'warning',
            'text-blue-600': message.type === 'info',
          }"
        />
        
        <!-- Message -->
        <div class="flex-1 text-sm font-medium">{{ message.text }}</div>
        
        <!-- Close Button -->
        <button 
          @click="removeMessage(message.id)"
          class="ml-2 -mr-1 -mt-1 p-1 rounded hover:bg-black hover:bg-opacity-5 transition-colors"
        >
          <XMarkIcon class="w-4 h-4" />
        </button>
      </div>
    </transition-group>
  </div>
</template>

<style scoped>
.toast-enter-active, .toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100px);
}

.toast-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>