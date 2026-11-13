<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Button from '../../Components/UI/Button.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    role: Object,
    modules: Array,
    granted: Array,   // id các quyền vai trò đang có
    grantable: Array, // id các quyền người đang thao tác được phép bật/tắt
});

const standard = [
    { action: 'view', label: 'Xem' },
    { action: 'create', label: 'Thêm' },
    { action: 'update', label: 'Sửa' },
    { action: 'delete', label: 'Xóa' },
];
const standardKeys = standard.map((item) => item.action);

const form = useForm({
    permissions: [...props.granted],
});

const grantableSet = computed(() => new Set(props.grantable));

const hasLocked = computed(() =>
    props.modules.some((module) => module.permissions.some((p) => !grantableSet.value.has(p.id))),
);

const find = (module, action) => module.permissions.find((p) => p.action === action);
const extras = (module) => module.permissions.filter((p) => !standardKeys.includes(p.action));
const toggleableIds = (module) =>
    module.permissions.map((p) => p.id).filter((id) => grantableSet.value.has(id));

const rowAll = (module) => {
    const ids = toggleableIds(module);
    return ids.length > 0 && ids.every((id) => form.permissions.includes(id));
};
const rowSome = (module) => {
    const ids = toggleableIds(module);
    return !rowAll(module) && ids.some((id) => form.permissions.includes(id));
};
const toggleRow = (module, checked) => {
    const ids = toggleableIds(module);
    form.permissions = checked
        ? [...new Set([...form.permissions, ...ids])]
        : form.permissions.filter((id) => !ids.includes(id));
};

const submit = () => form.put(`/admin/roles/${props.role.id}/permissions`);
</script>

<template>
    <Head title="Phân quyền" />

    <p class="mb-4 text-sm text-slate-600">
        Tick các hành động mà vai trò này được phép thực hiện. Dấu "—" nghĩa là module không có hành động đó.
    </p>

    <form class="rounded border border-slate-300 bg-white p-3 shadow-[0_1px_1px_rgba(0,0,0,0.04)]" @submit.prevent="submit">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-slate-500">
                    <tr>
                        <th class="px-3 py-2 font-medium">Module</th>
                        <th v-for="item in standard" :key="item.action" class="px-3 py-2 text-center font-medium">
                            {{ item.label }}
                        </th>
                        <th class="px-3 py-2 font-medium">Chức năng khác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="module in modules" :key="module.id" class="border-b border-slate-100">
                        <td class="px-3 py-2">
                            <label class="flex items-center gap-2 font-medium">
                                <input
                                    type="checkbox"
                                    class="rounded border-slate-300"
                                    :checked="rowAll(module)"
                                    :indeterminate="rowSome(module)"
                                    :disabled="toggleableIds(module).length === 0"
                                    @change="toggleRow(module, $event.target.checked)"
                                />
                                {{ module.name }}
                            </label>
                        </td>

                        <td v-for="item in standard" :key="item.action" class="px-3 py-2 text-center">
                            <input
                                v-if="find(module, item.action)"
                                v-model="form.permissions"
                                type="checkbox"
                                class="rounded border-slate-300"
                                :value="find(module, item.action).id"
                                :disabled="!grantableSet.has(find(module, item.action).id)"
                            />
                            <span v-else class="text-slate-300">—</span>
                        </td>

                        <td class="px-3 py-2">
                            <div class="flex flex-wrap gap-x-4 gap-y-1">
                                <label
                                    v-for="permission in extras(module)"
                                    :key="permission.id"
                                    class="flex items-center gap-1.5"
                                >
                                    <input
                                        v-model="form.permissions"
                                        type="checkbox"
                                        class="rounded border-slate-300"
                                        :value="permission.id"
                                        :disabled="!grantableSet.has(permission.id)"
                                    />
                                    {{ permission.name }}
                                </label>
                                <span v-if="extras(module).length === 0" class="text-slate-300">—</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-if="hasLocked" class="mt-3 text-xs text-slate-500">
            Các ô bị khóa là quyền bạn không có nên không thể thay đổi.
        </p>
        <p v-if="form.errors.permissions" class="mt-3 text-sm text-red-600">{{ form.errors.permissions }}</p>

        <div class="mt-3 flex items-center gap-2">
            <Button type="submit" :disabled="form.processing">Lưu phân quyền</Button>
            <Button variant="secondary" href="/admin/roles">Hủy</Button>
        </div>
    </form>
</template>