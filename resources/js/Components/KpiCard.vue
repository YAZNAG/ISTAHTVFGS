<script setup>
import * as HeroIcons from '@heroicons/vue/24/outline'
import { computed, onMounted, ref, watch } from 'vue'

const props = defineProps({
  title: String,
  value: [String, Number],
  icon: String,
  color: {
    type: String,
    default: 'blue',
  },
  caption: {
    type: String,
    default: '',
  },
})

const displayedValue = ref(0)
const iconComponent = computed(() => HeroIcons[props.icon] || HeroIcons.ChartBarIcon)

const numericValue = computed(() => {
  const parsed = Number(props.value)
  return Number.isFinite(parsed) ? parsed : null
})

const formattedValue = computed(() => {
  if (numericValue.value === null) return props.value
  return Math.round(displayedValue.value).toLocaleString('fr-FR')
})

const colorClass = computed(() => {
  const map = {
    blue: 'bg-istaht-blue text-white shadow-blue-900/10',
    cyan: 'bg-istaht-cyan text-white shadow-cyan-900/10',
    green: 'bg-istaht-green text-white shadow-green-900/10',
    orange: 'bg-istaht-orange text-white shadow-orange-900/10',
    amber: 'bg-istaht-amber text-white shadow-amber-900/10',
    red: 'bg-istaht-red text-white shadow-red-900/10',
    purple: 'bg-istaht-navy text-white shadow-blue-900/10',
  }
  return map[props.color] || map.blue
})

function animateValue() {
  if (numericValue.value === null) return

  const target = numericValue.value
  const duration = 650
  const start = performance.now()

  const tick = (time) => {
    const progress = Math.min((time - start) / duration, 1)
    const eased = 1 - Math.pow(1 - progress, 3)
    displayedValue.value = target * eased

    if (progress < 1) {
      requestAnimationFrame(tick)
    }
  }

  requestAnimationFrame(tick)
}

onMounted(animateValue)
watch(() => props.value, animateValue)
</script>

<template>
  <div class="dashboard-card group rounded-lg p-5">
    <div class="flex items-start justify-between gap-4">
      <div class="min-w-0">
        <p class="text-sm font-semibold text-slate-500">{{ title }}</p>
        <p class="mt-3 text-3xl font-bold tracking-normal text-istaht-navy">
          {{ formattedValue }}
        </p>
        <p v-if="caption" class="mt-1 text-xs font-medium text-slate-400">{{ caption }}</p>
      </div>
      <div :class="['rounded-lg p-3 shadow-lg transition duration-200 group-hover:scale-105', colorClass]">
        <component :is="iconComponent" class="h-6 w-6" />
      </div>
    </div>
  </div>
</template>
