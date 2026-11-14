<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import TabbedFormCard from '../../Components/UI/TabbedFormCard.vue';
import InputField from '../../Components/Fields/InputField.vue';
import SelectField from '../../Components/Fields/SelectField.vue';
import SlugField from '../../Components/Fields/SlugField.vue';
import TextareaField from '../../Components/Fields/TextareaField.vue';
import ToggleField from '../../Components/Fields/ToggleField.vue';
import MediaField from '../../Components/Fields/MediaField.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    category: Object,
    parents: Array,
});

const tabs = [
    { key: 'info', label: 'Thông tin', fields: ['parent_id', 'name', 'slug', 'image_id', 'description', 'sort_order', 'is_active'] },
    { key: 'seo', label: 'Cấu hình SEO', fields: ['meta_title', 'meta_description', 'meta_key', 'no_index', 'no_follow'] },
];

const form = useForm({
    parent_id: props.category.parent_id ?? '',
    name: props.category.name,
    slug: props.category.slug,
    description: props.category.description ?? '',
    image_id: props.category.image_id ?? null,
    meta_title: props.category.meta_title ?? '',
    meta_description: props.category.meta_description ?? '',
    meta_key: props.category.meta_key ?? '',
    no_index: Boolean(props.category.no_index),
    no_follow: Boolean(props.category.no_follow),
    sort_order: props.category.sort_order,
    is_active: props.category.is_active,
});

const submit = () => form.put(`/admin/product-categories/${props.category.id}`);
</script>

<template>
    <Head title="Sửa danh mục sản phẩm" />

    <TabbedFormCard
        :tabs="tabs"
        :errors="form.errors"
        :processing="form.processing"
        cancel-href="/admin/product-categories"
        @submit="submit"
    >
        <template #info>
            <MediaField
                v-model="form.image_id"
                label="Hình ảnh"
                :current-url="category.image_url"
                :error="form.errors.image_id"
            />
            <InputField v-model="form.name" name="name" label="Tên danh mục" :error="form.errors.name" />
            <SelectField
                v-model="form.parent_id"
                name="parent_id"
                label="Danh mục cha"
                :options="parents"
                placeholder="-- Không có (danh mục gốc) --"
                :error="form.errors.parent_id"
            />
            <SlugField v-model="form.slug" :source="form.name" prefix="/danh-muc-san-pham/" :error="form.errors.slug" editing />
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
                hint="Để trống sẽ dùng tên danh mục."
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
