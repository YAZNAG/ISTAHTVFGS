<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="ui-button ui-button-ghost"
    >
        <slot />
    </button>
</template>
