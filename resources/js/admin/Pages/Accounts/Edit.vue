<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import FormCard from '../../Components/UI/FormCard.vue';
import InputField from '../../Components/Fields/InputField.vue';
import SelectField from '../../Components/Fields/SelectField.vue';
import TextareaField from '../../Components/Fields/TextareaField.vue';
import ToggleField from '../../Components/Fields/ToggleField.vue';
import ImageUpload from '../../Components/Fields/ImageUpload.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    account: Object,
    roles: Array,
});

const form = useForm({
    name: props.account.name,
    username: props.account.username ?? '',
    email: props.account.email,
    id_role: props.account.id_role ?? '',
    password: '',
    password_confirmation: '',
    is_active: props.account.is_active,
    image: null,
    remove_image: false,
    note: props.account.note ?? '',
});

// Gửi bằng POST kèm _method=put để PHP đọc được dữ liệu dạng multipart (có file ảnh)
const submit = () =>
    form
        .transform((data) => ({ ...data, _method: 'put' }))
        .post(`/admin/accounts/${props.account.id}`, {
            forceFormData: true,
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
</script>

<template>
    <Head title="Sửa tài khoản" />

    <FormCard :processing="form.processing" cancel-href="/admin/accounts" @submit="submit">
        <ImageUpload
            v-model="form.image"
            v-model:removed="form.remove_image"
            label="Ảnh đại diện"
            hint="JPG, PNG hoặc WEBP, tối đa 2MB."
            :current-url="account.image_url"
            :error="form.errors.image"
        />

        <div class="grid gap-5 sm:grid-cols-2">
            <InputField v-model="form.name" name="name" label="Họ tên" :error="form.errors.name" />
            <InputField
                v-model="form.username"
                name="username"
                label="Tên đăng nhập (username)"
                autocomplete="off"
                :error="form.errors.username"
            />
        </div>

        <InputField
            v-model="form.email"
            name="email"
            type="email"
            label="Email (dùng để đăng nhập)"
            autocomplete="off"
            :error="form.errors.email"
        />

        <SelectField
            v-model="form.id_role"
            name="id_role"
            label="Vai trò"
            :options="roles"
            :error="form.errors.id_role"
        />

        <div class="grid gap-5 sm:grid-cols-2">
            <InputField
                v-model="form.password"
                name="password"
                type="password"
                label="Mật khẩu mới"
                autocomplete="new-password"
                hint="Để trống nếu không đổi mật khẩu."
                :error="form.errors.password"
            />
            <InputField
                v-model="form.password_confirmation"
                name="password_confirmation"
                type="password"
                label="Nhập lại mật khẩu mới"
                autocomplete="new-password"
                :error="form.errors.password_confirmation"
            />
        </div>

        <TextareaField v-model="form.note" name="note" label="Ghi chú" rows="3" :error="form.errors.note" />

        <ToggleField v-model="form.is_active" label="Đang hoạt động (được phép đăng nhập)" :error="form.errors.is_active" />
    </FormCard>
</template>