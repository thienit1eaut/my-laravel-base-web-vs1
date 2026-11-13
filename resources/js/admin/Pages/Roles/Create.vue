<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import FormCard from '../../Components/UI/FormCard.vue';
import InputField from '../../Components/Fields/InputField.vue';
import TextareaField from '../../Components/Fields/TextareaField.vue';

defineOptions({ layout: AdminLayout });

const form = useForm({
    name: '',
    description: '',
});

const submit = () => form.post('/admin/roles');
</script>

<template>
    <Head title="Thêm vai trò" />

    <FormCard :processing="form.processing" cancel-href="/admin/roles" @submit="submit">
        <InputField v-model="form.name" name="name" label="Tên vai trò" :error="form.errors.name" />
        <TextareaField
            v-model="form.description"
            name="description"
            label="Mô tả"
            rows="3"
            :error="form.errors.description"
        />
    </FormCard>
</template>