<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import TabbedFormCard from '../../Components/UI/TabbedFormCard.vue';
import InputField from '../../Components/Fields/InputField.vue';
import TextareaField from '../../Components/Fields/TextareaField.vue';
import ToggleField from '../../Components/Fields/ToggleField.vue';
import SlugField from '../../Components/Fields/SlugField.vue';
import MediaField from '../../Components/Fields/MediaField.vue';

defineOptions({ layout: AdminLayout });

const tabs = [
    { key: 'info', label: 'Thông tin', fields: ['name', 'slug', 'image_id', 'description', 'sort_order', 'is_active'] },
    { key: 'seo', label: 'Cấu hình SEO', fields: ['meta_title', 'meta_description', 'meta_key', 'no_index', 'no_follow'] },
];

const form = useForm({
    name: '',
    slug: '',
    description: '',
    image_id: null,
    meta_title: '',
    meta_description: '',
    meta_key: '',
    no_index: false,
    no_follow: false,
    sort_order: 0,
    is_active: true,
});

const submit = () => form.post('/admin/brands');
</script>

<template>
    <Head title="Thêm thương hiệu" />

    <TabbedFormCard
        :tabs="tabs"
        :errors="form.errors"
        :processing="form.processing"
        cancel-href="/admin/brands"
        @submit="submit"
    >
        <template #info>
            <MediaField v-model="form.image_id" label="Logo / hình ảnh" :error="form.errors.image_id" />
            <InputField v-model="form.name" name="name" label="Tên thương hiệu" :error="form.errors.name" />
            <SlugField v-model="form.slug" :source="form.name" :error="form.errors.slug" />
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
            <ToggleField v-model="form.is_active" label="Đang hiển thị" :error="form.errors.is_active" />
        </template>

        <template #seo>
            <InputField
                v-model="form.meta_title"
                name="meta_title"
                label="Tiêu đề SEO"
                hint="Để trống sẽ dùng tên thương hiệu."
                :error="form.errors.meta_title"
            />
            <TextareaField
                v-model="form.meta_description"
                name="meta_description"
                label="Mô tả SEO"
                rows="2"
                :error="form.errors.meta_description"
            />
            <InputField
                v-model="form.meta_key"
                name="meta_key"
                label="Từ khóa SEO"
                hint="Các từ khóa cách nhau bằng dấu phẩy."
                :error="form.errors.meta_key"
            />
            <ToggleField
                :model-value="!form.no_index"
                label="Cho phép index (hiện trên công cụ tìm kiếm)"
                :error="form.errors.no_index"
                @update:model-value="form.no_index = !$event"
            />
            <ToggleField
                :model-value="!form.no_follow"
                label="Cho phép follow (công cụ tìm kiếm theo các link)"
                :error="form.errors.no_follow"
                @update:model-value="form.no_follow = !$event"
            />
        </template>
    </TabbedFormCard>
</template>
