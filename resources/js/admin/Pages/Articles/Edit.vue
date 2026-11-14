<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import TabbedFormCard from '../../Components/UI/TabbedFormCard.vue';
import InputField from '../../Components/Fields/InputField.vue';
import SelectField from '../../Components/Fields/SelectField.vue';
import MultiSelectField from '../../Components/Fields/MultiSelectField.vue';
import TagInput from '../../Components/Fields/TagInput.vue';
import SlugField from '../../Components/Fields/SlugField.vue';
import TextareaField from '../../Components/Fields/TextareaField.vue';
import ToggleField from '../../Components/Fields/ToggleField.vue';
import RichTextField from '../../Components/Fields/RichTextField.vue';
import MediaField from '../../Components/Fields/MediaField.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    article: Object,
    categories: Array,
    tagOptions: { type: Array, default: () => [] },
});

const tabs = [
    { key: 'info', label: 'Thông tin', fields: ['image_id', 'category_id', 'extra_category_ids', 'tags', 'title', 'slug', 'excerpt', 'is_active'] },
    { key: 'content', label: 'Nội dung', fields: ['content'] },
    { key: 'seo', label: 'Cấu hình SEO', fields: ['meta_title', 'meta_description', 'meta_key', 'no_index', 'no_follow'] },
];

const form = useForm({
    category_id: props.article.category_id,
    extra_category_ids: props.article.extra_category_ids ?? [],
    tags: props.article.tags ?? [],
    title: props.article.title,
    slug: props.article.slug,
    excerpt: props.article.excerpt ?? '',
    content: props.article.content ?? '',
    image_id: props.article.image_id ?? null,
    meta_title: props.article.meta_title ?? '',
    meta_description: props.article.meta_description ?? '',
    meta_key: props.article.meta_key ?? '',
    no_index: Boolean(props.article.no_index),
    no_follow: Boolean(props.article.no_follow),
    is_active: props.article.is_active,
});

const submit = () => form.put(`/admin/articles/${props.article.id}`);
</script>

<template>
    <Head title="Sửa bài viết" />

    <TabbedFormCard
        :tabs="tabs"
        :errors="form.errors"
        :processing="form.processing"
        cancel-href="/admin/articles"
        @submit="submit"
    >
        <template #info>
            <MediaField
                v-model="form.image_id"
                label="Ảnh đại diện"
                :current-url="article.image_url"
                :error="form.errors.image_id"
            />
            <SelectField
                v-model="form.category_id"
                name="category_id"
                label="Danh mục"
                :options="categories"
                :error="form.errors.category_id"
            />
            <MultiSelectField
                v-model="form.extra_category_ids"
                label="Danh mục phụ"
                hint="Bài còn hiện trong các danh mục phụ này (tùy chọn)."
                :options="categories"
                :hide-id="form.category_id"
                :error="form.errors.extra_category_ids"
            />
            <TagInput
                v-model="form.tags"
                label="Thẻ"
                hint="Gõ tên thẻ rồi nhấn Enter. Thẻ chưa có sẽ được tạo mới."
                :suggestions="tagOptions"
                :error="form.errors.tags"
            />
            <InputField v-model="form.title" name="title" label="Tiêu đề bài viết" :error="form.errors.title" />
            <SlugField v-model="form.slug" :source="form.title" prefix="/bai-viet/" :error="form.errors.slug" editing />
            <TextareaField
                v-model="form.excerpt"
                name="excerpt"
                label="Tóm tắt"
                rows="3"
                :error="form.errors.excerpt"
            />
            <ToggleField v-model="form.is_active" label="Đang hiển thị" :error="form.errors.is_active" />
        </template>

        <template #content>
            <RichTextField v-model="form.content" label="Nội dung" :error="form.errors.content" />
        </template>

        <template #seo>
            <InputField
                v-model="form.meta_title"
                name="meta_title"
                label="Tiêu đề SEO"
                hint="Để trống sẽ dùng tiêu đề bài viết."
                :error="form.errors.meta_title"
            />
            <TextareaField
                v-model="form.meta_description"
                name="meta_description"
                label="Mô tả SEO"
                rows="2"
                hint="Để trống sẽ dùng phần tóm tắt."
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
