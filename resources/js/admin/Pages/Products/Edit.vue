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
import GalleryField from '../../Components/Fields/GalleryField.vue';
import SpecsField from '../../Components/Fields/SpecsField.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    product: Object,
    categories: Array,
    brands: Array,
    tagOptions: { type: Array, default: () => [] },
});

const tabs = [
    { key: 'info', label: 'Thông tin', fields: ['image_id', 'gallery_ids', 'category_id', 'extra_category_ids', 'brand_id', 'tags', 'name', 'slug', 'sku', 'price', 'sale_price', 'stock', 'short_description', 'is_active'] },
    { key: 'content', label: 'Nội dung', fields: ['content', 'specs'] },
    { key: 'seo', label: 'Cấu hình SEO', fields: ['meta_title', 'meta_description', 'meta_key', 'no_index', 'no_follow'] },
];

const form = useForm({
    category_id: props.product.category_id,
    extra_category_ids: props.product.extra_category_ids ?? [],
    brand_id: props.product.brand_id ?? '',
    tags: props.product.tags ?? [],
    name: props.product.name,
    slug: props.product.slug,
    sku: props.product.sku ?? '',
    price: props.product.price,
    sale_price: props.product.sale_price ?? '',
    stock: props.product.stock,
    short_description: props.product.short_description ?? '',
    content: props.product.content ?? '',
    specs: (props.product.specs ?? []).map((row) => ({ ...row })),
    image_id: props.product.image_id ?? null,
    gallery_ids: (props.product.gallery ?? []).map((item) => item.id),
    meta_title: props.product.meta_title ?? '',
    meta_description: props.product.meta_description ?? '',
    meta_key: props.product.meta_key ?? '',
    no_index: Boolean(props.product.no_index),
    no_follow: Boolean(props.product.no_follow),
    is_active: props.product.is_active,
});

const submit = () => form.put(`/admin/products/${props.product.id}`);
</script>

<template>
    <Head title="Sửa sản phẩm" />

    <TabbedFormCard
        :tabs="tabs"
        :errors="form.errors"
        :processing="form.processing"
        cancel-href="/admin/products"
        @submit="submit"
    >
        <template #info>
            <MediaField
                v-model="form.image_id"
                label="Ảnh đại diện"
                :current-url="product.image_url"
                :error="form.errors.image_id"
            />
            <GalleryField
                v-model="form.gallery_ids"
                label="Thư viện ảnh"
                hint="Tối đa 20 ảnh, dùng mũi tên để đổi thứ tự."
                :initial="product.gallery"
                :error="form.errors.gallery_ids"
            />
            <InputField v-model="form.name" name="name" label="Tên sản phẩm" :error="form.errors.name" />
            <SlugField v-model="form.slug" :source="form.name" prefix="/san-pham/" :error="form.errors.slug" editing />
            <SelectField
                v-model="form.category_id"
                name="category_id"
                label="Danh mục chính"
                :options="categories"
                :error="form.errors.category_id"
            />
            <MultiSelectField
                v-model="form.extra_category_ids"
                label="Danh mục phụ"
                hint="Sản phẩm còn hiện trong các danh mục phụ này (tùy chọn)."
                :options="categories"
                :hide-id="form.category_id"
                :error="form.errors.extra_category_ids"
            />
            <SelectField
                v-model="form.brand_id"
                name="brand_id"
                label="Thương hiệu"
                :options="brands"
                placeholder="-- Không có --"
                :error="form.errors.brand_id"
            />
            <TagInput
                v-model="form.tags"
                label="Thẻ"
                hint="Gõ tên thẻ rồi nhấn Enter. Thẻ chưa có sẽ được tạo mới."
                :suggestions="tagOptions"
                :error="form.errors.tags"
            />
            <InputField v-model="form.sku" name="sku" label="Mã SKU" :error="form.errors.sku" />
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <InputField v-model="form.price" name="price" type="number" min="0" label="Giá gốc (đ)" :error="form.errors.price" />
                <InputField
                    v-model="form.sale_price"
                    name="sale_price"
                    type="number"
                    min="0"
                    label="Giá khuyến mãi (đ)"
                    hint="Để trống nếu không giảm giá."
                    :error="form.errors.sale_price"
                />
                <InputField v-model="form.stock" name="stock" type="number" min="0" label="Tồn kho" :error="form.errors.stock" />
            </div>
            <TextareaField
                v-model="form.short_description"
                name="short_description"
                label="Mô tả ngắn"
                rows="3"
                :error="form.errors.short_description"
            />
            <ToggleField v-model="form.is_active" label="Đang hiển thị" :error="form.errors.is_active" />
        </template>

        <template #content>
            <RichTextField v-model="form.content" label="Mô tả chi tiết" :error="form.errors.content" />
            <SpecsField v-model="form.specs" label="Thông số kỹ thuật" :errors="form.errors" />
        </template>

        <template #seo>
            <InputField
                v-model="form.meta_title"
                name="meta_title"
                label="Tiêu đề SEO"
                hint="Để trống sẽ dùng tên sản phẩm."
                :error="form.errors.meta_title"
            />
            <TextareaField
                v-model="form.meta_description"
                name="meta_description"
                label="Mô tả SEO"
                rows="2"
                hint="Để trống sẽ dùng mô tả ngắn."
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
