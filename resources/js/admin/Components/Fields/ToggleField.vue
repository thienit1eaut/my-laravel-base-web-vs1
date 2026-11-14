<script setup>
// Nút bật/tắt (toggle switch) cỡ vừa nhỏ, dùng cho các trạng thái như is_active.
// Dùng: <ToggleField v-model="form.is_active" label="Đang hiển thị" :error="form.errors.is_active" />
defineProps({
    label: String,
    error: String,
    disabled: { type: Boolean, default: false },
});

const model = defineModel({ type: Boolean, default: false });

const toggle = () => {
    model.value = !model.value;
};
</script>

<template>
    <div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                role="switch"
                :aria-checked="model"
                :aria-label="label"
                :disabled="disabled"
                class="relative inline-flex h-[18px] w-8 shrink-0 cursor-pointer items-center rounded-full transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-50"
                :class="model ? 'bg-indigo-600' : 'bg-slate-300'"
                @click="toggle"
            >
                <span
                    class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform"
                    :class="model ? 'translate-x-[16px]' : 'translate-x-[2px]'"
                />
            </button>
            <span v-if="label" class="cursor-pointer select-none text-[14px] text-slate-700" @click="!disabled && toggle()">
                {{ label }}
            </span>
        </div>
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
    </div>
</template>
