<script setup>
defineProps({
    label: String,
    error: String,
});

const model = defineModel({ type: Boolean, default: false });
</script>

<template>
    <div>
        <label class="flex items-center gap-2 text-[14px] text-slate-700">
            <input v-model="model" type="checkbox" />
            {{ label }}
        </label>
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
    </div>
</template>