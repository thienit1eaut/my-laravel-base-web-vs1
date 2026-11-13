<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import FormCard from '../../Components/UI/FormCard.vue';
import InputField from '../../Components/Fields/InputField.vue';
import TextareaField from '../../Components/Fields/TextareaField.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    role: Object,
});

const form = useForm({
    name: props.role.name,
    description: props.role.description ?? '',
});

const submit = () => form.put(`/admin/roles/${props.role.id}`);
</script>

<template>
    <Head title="Sửa vai trò" />

    <FormCard :processing="form.processing" cancel-href="/admin/roles" @submit="submit">
        <InputField
            v-model="form.name"
            name="name"
            label="Tên vai trò"
            :disabled="role.is_system"
            :hint="role.is_system ? 'Vai trò hệ thống không đổi được tên.' : undefined"
            :error="form.errors.name"
        />
        <TextareaField
            v-model="form.description"
            name="description"
            label="Mô tả"
            rows="3"
            :error="form.errors.description"
        />
    </FormCard>
</template>