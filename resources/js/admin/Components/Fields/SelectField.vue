<script setup>
defineOptions({ inheritAttrs: false });

defineProps({
    name: String,
    label: String,
    error: String,
    hint: String,
    options: { type: Array, default: () => [] },
    placeholder: { type: String, default: '-- Chọn --' },
    valueKey: { type: String, default: 'id' },
    labelKey: { type: String, default: 'name' },
});

const model = defineModel();
</script>

<template>
    <div>
        <label v-if="label" :for="name" class="block text-[14px] font-semibold text-slate-800">{{ label }}</label>
        <select
            :id="name"
            v-model="model"
            :name="name"
            v-bind="$attrs"
            class="block w-full rounded-[4px] border bg-white px-2 py-1 text-[14px] leading-5 text-slate-800 focus:outline-none focus:ring-1"
            :class="[
                label ? 'mt-1' : '',
                error
                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                    : 'border-[#8c8f94] focus:border-indigo-600 focus:ring-indigo-600',
            ]"
        >
            <option value="">{{ placeholder }}</option>
            <option v-for="option in options" :key="option[valueKey]" :value="option[valueKey]">
                {{ option[labelKey] }}
            </option>
        </select>
        <p v-if="hint && !error" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
    </div>
</template>