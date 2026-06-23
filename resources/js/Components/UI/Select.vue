<script setup>
const model = defineModel({
  type: [String, Number, Boolean],
  default: '',
})
</script>

<template>
  <select v-model="model" class="ui-input w-full">
    <slot />
  </select>
</template>
