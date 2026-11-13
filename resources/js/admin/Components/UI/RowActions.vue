<script setup>
import IconButton from './IconButton.vue';
import Dropdown from './Dropdown.vue';

defineProps({
    viewHref: String,     // có thì hiện icon Xem chi tiết
    editHref: String,     // có thì hiện icon Sửa
    deletable: Boolean,   // true thì hiện icon Xóa (bắn sự kiện delete)
});

defineEmits(['delete']);
</script>

<template>
    <div class="flex items-center justify-end gap-0.5">
        <IconButton v-if="viewHref" icon="eye" label="Xem chi tiết" :href="viewHref" />
        <IconButton v-if="editHref" icon="edit" label="Sửa" :href="editHref" />
        <IconButton v-if="deletable" icon="trash" label="Xóa" variant="danger" @click="$emit('delete')" />

        <!-- Dấu + chỉ hiện khi nơi dùng đưa thao tác vào slot "more" -->
        <Dropdown v-if="$slots.more">
            <slot name="more" />
        </Dropdown>
    </div>
</template>