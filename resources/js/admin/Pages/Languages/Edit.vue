<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import TabbedFormCard from '../../Components/UI/TabbedFormCard.vue';
import InputField from '../../Components/Fields/InputField.vue';
import SelectField from '../../Components/Fields/SelectField.vue';
import ToggleField from '../../Components/Fields/ToggleField.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    language: Object,
    flags: Array,
});

const tabs = [{ key: 'info', label: 'Thông tin', fields: ['code', 'name', 'prefix', 'flag', 'sort_order', 'is_active'] }];

const form = useForm({
    name: props.language.name,
    prefix: props.language.prefix,
    flag: props.language.flag ?? '',
    sort_order: props.language.sort_order,
    is_active: props.language.is_active,
});

const submit = () => form.put(`/admin/languages/${props.language.id}`);
</script>

<template>
    <Head title="Sửa ngôn ngữ" />

    <TabbedFormCard
        :tabs="tabs"
        :errors="form.errors"
        :processing="form.processing"
        cancel-href="/admin/languages"
        @submit="submit"
    >
        <template #info>
            <InputField :model-value="language.code" name="code" label="Mã ngôn ngữ" disabled hint="Không đổi được vì đã gắn với các bản dịch." />
            <InputField v-model="form.name" name="name" label="Tên ngôn ngữ" placeholder="vd: 日本語 hoặc Tiếng Nhật" :error="form.errors.name" />
            <InputField
                v-model="form.prefix"
                name="prefix"
                label="Tiền tố trên đường dẫn"
                placeholder="vd: jp"
                hint="Link sẽ có dạng /jp/... Đổi tiền tố sẽ làm các link đã đăng thay đổi."
                :error="form.errors.prefix"
            />
            <SelectField
                v-model="form.flag"
                name="flag"
                label="Lá cờ"
                :options="flags"
                placeholder="-- Không có --"
                :error="form.errors.flag"
            />
            <InputField v-model="form.sort_order" name="sort_order" type="number" min="0" label="Thứ tự hiển thị" :error="form.errors.sort_order" />
            <ToggleField v-model="form.is_active" label="Đang bật" :error="form.errors.is_active" />
        </template>
    </TabbedFormCard>
</template>
