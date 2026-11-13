import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

// Dùng: const { can } = usePermission();  can('brands.create')
// Chỉ để ẩn/hiện giao diện. Việc chặn thật sự nằm ở phía server.
export function usePermission() {
    const page = usePage();

    const granted = computed(() => new Set(page.props.auth?.permissions ?? []));

    const can = (code) => granted.value.has(code);

    return { can };
}