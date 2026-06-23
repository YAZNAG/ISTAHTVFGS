<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  links: {
    type: Array,
    required: true,
  },
  from: {
    type: Number,
    default: 0,
  },
  to: {
    type: Number,
    default: 0,
  },
  total: {
    type: Number,
    default: 0,
  },
})
</script>

<template>
  <div
    v-if="links.length > 3"
    class="flex flex-col gap-3 border-t border-slate-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
  >
    <p class="text-sm text-slate-600">
      Affichage de
      <span class="font-bold text-istaht-navy">{{ from }}</span>
      à
      <span class="font-bold text-istaht-navy">{{ to }}</span>
      sur
      <span class="font-bold text-istaht-navy">{{ total }}</span>
      résultats
    </p>

    <nav class="flex flex-wrap items-center gap-1" aria-label="Pagination">
      <template v-for="(link, index) in links" :key="`${link.label}-${index}`">
        <Link
          v-if="link.url"
          :href="link.url"
          class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-bold transition duration-200"
          :class="link.active
            ? 'border-istaht-blue bg-istaht-blue text-white shadow-sm'
            : 'border-slate-200 bg-white text-slate-600 hover:border-istaht-cyan hover:bg-cyan-50 hover:text-istaht-blue'"
          v-html="link.label"
          :preserve-state="true"
          :preserve-scroll="true"
        />
        <span
          v-else
          class="inline-flex min-h-9 min-w-9 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm font-bold text-slate-400"
          v-html="link.label"
        />
      </template>
    </nav>
  </div>
</template>
