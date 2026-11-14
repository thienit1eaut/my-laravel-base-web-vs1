<script setup>
import { reactive, ref, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import DataTable from '../../Components/UI/DataTable.vue';
import RowActions from '../../Components/UI/RowActions.vue';
import Pagination from '../../Components/UI/Pagination.vue';
import Badge from '../../Components/UI/Badge.vue';
import Button from '../../Components/UI/Button.vue';
import SelectField from '../../Components/Fields/SelectField.vue';
import QuickToggle from '../../Components/UI/QuickToggle.vue';
import { usePermission } from '../../Composables/usePermission.js';

const { can } = usePermission();
// Sửa nhanh ngay trên dòng: cần quyền sửa module; server kiểm tra lại (AdminController::quickUpdate)
const canQuick = () => can('accounts.update');
const quickUrl = (row) => `/admin/accounts/${row.id}/quick`;

defineOptions({ layout: AdminLayout });

const props = defineProps({
    accounts: Object,
    roles: Array,
    filters: Object,
});

const page = usePage();

const columns = [
    { key: 'name', label: 'Tài khoản' },
    { key: 'email', label: 'Email' },
    { key: 'role_name', label: 'Vai trò' },
    { key: 'is_active', label: 'Trạng thái' },
];

const statusOptions = [
    { value: '1', label: 'Đang hoạt động' },
    { value: '0', label: 'Đã khóa' },
];

// Danh sách id các dòng đang được chọn (dùng cho thao tác hàng loạt sau này)
const selected = ref([]);

const filters = reactive({
    search: props.filters.search ?? '',
    role: props.filters.role ?? '',
    status: props.filters.status ?? '',
});

// Lọc tự động sau khi ngừng thao tác 300ms
let timer;
watch(filters, () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        const params = Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== ''));

        router.get('/admin/accounts', params, { preserveState: true, replace: true });
    }, 300);
});

const remove = (account) => {
    if (confirm(`Xóa tài khoản "${account.name}"?`)) {
        router.delete(`/admin/accounts/${account.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Tài khoản" />

    <div class="mb-3 flex flex-wrap items-center gap-2">
        <SearchInput v-model="filters.search" placeholder="Tìm theo tên, username, email..." />
        <div class="w-full sm:w-44">
            <SelectField v-model="filters.role" :options="roles" placeholder="Tất cả vai trò" />
        </div>
        <div class="w-full sm:w-44">
            <SelectField
                v-model="filters.status"
                :options="statusOptions"
                value-key="value"
                label-key="label"
                placeholder="Tất cả trạng thái"
            />
        </div>
        <Button v-if="can('accounts.create')" href="/admin/accounts/create" class="ml-auto">
            Thêm tài khoản
        </Button>
    </div>

    <DataTable
        v-model:selected="selected"
        :columns="columns"
        :rows="accounts.data"
        empty-text="Không có tài khoản nào."
    >
        <template #cell-name="{ row }">
            <div class="flex items-center gap-2.5">
                <img v-if="row.image_url" :src="row.image_url" alt="" class="h-10 w-10 rounded-full object-cover" />
                <span
                    v-else
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-xs font-medium text-slate-600"
                >
                    {{ row.name.charAt(0).toUpperCase() }}
                </span>
                <div>
                    <div class="font-semibold text-indigo-600">{{ row.name }}</div>
                    <div class="text-xs text-slate-500">
                        {{ row.username ? '@' + row.username : 'Chưa đặt username' }}
                    </div>
                </div>
            </div>
        </template>

        <template #cell-role_name="{ row }">
            {{ row.role_name ?? '—' }}
        </template>

        <template #cell-is_active="{ row }">
            <!-- Không cho tự khóa chính mình (server cũng chặn) -->
            <QuickToggle
                v-if="canQuick('is_active')"
                :url="quickUrl(row)"
                field="is_active"
                :value="row.is_active"
                :disabled="row.id === page.props.auth.user.id"
                label="Hoạt động"
            />
            <Badge v-else :variant="row.is_active ? 'success' : 'danger'">
                {{ row.is_active ? 'Hoạt động' : 'Đã khóa' }}
            </Badge>
        </template>

        <template #actions="{ row }">
            <RowActions
                :edit-href="can('accounts.update') ? `/admin/accounts/${row.id}/edit` : undefined"
                :deletable="can('accounts.delete') && row.id !== page.props.auth.user.id"
                @delete="remove(row)"
            />
        </template>
    </DataTable>

    <Pagination :links="accounts.links" />
</template>