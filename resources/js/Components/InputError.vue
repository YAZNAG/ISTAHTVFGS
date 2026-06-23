<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="mt-1 text-sm font-medium text-istaht-red">
            {{ message }}
        </p>
    </div>
</template>
