<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import FormCard from '../../Components/UI/FormCard.vue';
import InputField from '../../Components/Fields/InputField.vue';
import TextareaField from '../../Components/Fields/TextareaField.vue';
import CheckboxField from '../../Components/Fields/CheckboxField.vue';
import MediaField from '../../Components/Fields/MediaField.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    brand: Object,
});

const form = useForm({
    name: props.brand.name,
    slug: props.brand.slug,
    description: props.brand.description ?? '',
    image_id: props.brand.image_id ?? null,
    sort_order: props.brand.sort_order,
    is_active: props.brand.is_active,
});

const submit = () => form.put(`/admin/brands/${props.brand.id}`);
</script>

<template>
    <Head title="Sửa thương hiệu" />

    <FormCard :processing="form.processing" cancel-href="/admin/brands" @submit="submit">
        <MediaField
            v-model="form.image_id"
            label="Logo / hình ảnh"
            :current-url="brand.image_url"
            :error="form.errors.image_id"
        />
        <InputField v-model="form.name" name="name" label="Tên thương hiệu" :error="form.errors.name" />
        <InputField
            v-model="form.slug"
            name="slug"
            label="Đường dẫn (slug)"
            placeholder="Để trống để tự tạo từ tên"
            :error="form.errors.slug"
        />
        <TextareaField
            v-model="form.description"
            name="description"
            label="Mô tả"
            rows="4"
            :error="form.errors.description"
        />
        <InputField
            v-model="form.sort_order"
            name="sort_order"
            type="number"
            min="0"
            label="Thứ tự hiển thị"
            :error="form.errors.sort_order"
        />
        <CheckboxField v-model="form.is_active" label="Đang hiển thị" :error="form.errors.is_active" />
    </FormCard>
</template>