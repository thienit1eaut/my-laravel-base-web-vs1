<script setup>
import { computed } from 'vue';
import { usePermission } from '../../Composables/usePermission.js';

const props = defineProps({
    // Một mã quyền, hoặc mảng mã quyền (có một trong các quyền là hiện)
    permission: { type: [String, Array], required: true },
});

const { can } = usePermission();

const allowed = computed(() => [].concat(props.permission).some(can));
</script>

<template>
    <slot v-if="allowed" />
    <slot v-else name="fallback" />
</template>