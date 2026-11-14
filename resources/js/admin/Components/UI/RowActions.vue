<script setup>
import IconButton from './IconButton.vue';
import Icon from './Icon.vue';
import Dropdown from './Dropdown.vue';

defineProps({
    viewHref: String,     // có thì hiện icon Xem chi tiết
    viewDisabledReason: String, // có thì nút Xem bị xám, rê chuột hiện lý do (không bấm được)
    viewExternal: Boolean, // true thì Xem mở ở tab mới (link ngoài trang quản trị)
    editHref: String,     // có thì hiện icon Sửa
    deletable: Boolean,   // true thì hiện icon Xóa (bắn sự kiện delete)
});

defineEmits(['delete']);
</script>

<template>
    <div class="flex items-center justify-end gap-0.5">
        <span
            v-if="viewDisabledReason"
            class="inline-flex cursor-not-allowed rounded-md p-1.5 text-slate-300"
            :title="viewDisabledReason"
            :aria-label="`Xem chi tiết (không khả dụng: ${viewDisabledReason})`"
        >
            <Icon name="eye" />
        </span>
        <IconButton v-else-if="viewHref" icon="eye" label="Xem chi tiết" :href="viewHref" :external="viewExternal" />
        <IconButton v-if="editHref" icon="edit" label="Sửa" :href="editHref" />
        <IconButton v-if="deletable" icon="trash" label="Xóa" variant="danger" @click="$emit('delete')" />

        <!-- Dấu + chỉ hiện khi nơi dùng đưa thao tác vào slot "more" -->
        <Dropdown v-if="$slots.more">
            <slot name="more" />
        </Dropdown>
    </div>
</template>