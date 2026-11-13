<script setup>
import { nextTick, onBeforeUnmount, ref } from 'vue';
import Icon from './Icon.vue';

defineProps({
    label: { type: String, default: 'Thao tác khác' }, // nhãn của nút + mặc định
});

const open = ref(false);
const trigger = ref(null);
const menu = ref(null);
const position = ref({ top: '0px', left: '0px' });

// Đặt menu ngay dưới nút, canh phải; sát đáy màn hình thì lật lên trên
const place = () => {
    if (!trigger.value || !menu.value) return;

    const t = trigger.value.getBoundingClientRect();
    const m = menu.value.getBoundingClientRect();
    const below = t.bottom + 4;
    const top = below + m.height > window.innerHeight - 8 ? t.top - m.height - 4 : below;

    position.value = {
        top: `${Math.max(8, top)}px`,
        left: `${Math.max(8, t.right - m.width)}px`,
    };
};

const onOutside = (e) => {
    if (trigger.value?.contains(e.target) || menu.value?.contains(e.target)) return;
    close();
};

const onKey = (e) => {
    if (e.key === 'Escape') close();
};

const close = () => {
    open.value = false;
    document.removeEventListener('click', onOutside, true);
    document.removeEventListener('keydown', onKey);
    window.removeEventListener('scroll', close, true);
    window.removeEventListener('resize', close);
};

const show = async () => {
    open.value = true;
    await nextTick();
    place();
    document.addEventListener('click', onOutside, true);
    document.addEventListener('keydown', onKey);
    window.addEventListener('scroll', close, true);
    window.addEventListener('resize', close);
};

const toggle = () => (open.value ? close() : show());

onBeforeUnmount(close);
</script>

<template>
    <span ref="trigger" class="inline-flex" @click="toggle">
        <slot name="trigger" :open="open">
            <button
                type="button"
                :title="label"
                :aria-label="label"
                aria-haspopup="menu"
                :aria-expanded="open"
                class="inline-flex rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-indigo-600"
            >
                <Icon name="plus" />
            </button>
        </slot>
    </span>

    <!-- Teleport ra body để menu không bị cắt bởi khung cuộn của bảng -->
    <Teleport to="body">
        <div
            v-if="open"
            ref="menu"
            role="menu"
            :style="position"
            class="fixed z-50 min-w-44 overflow-hidden rounded border border-slate-300 bg-white py-1 shadow-lg"
            @click="close"
        >
            <slot />
        </div>
    </Teleport>
</template>