<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import FormCard from '../../Components/UI/FormCard.vue';
import InputField from '../../Components/Fields/InputField.vue';
import ImageUpload from '../../Components/Fields/ImageUpload.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    profile: Object,
});

const form = useForm({
    name: props.profile.name,
    username: props.profile.username ?? '',
    email: props.profile.email,
    current_password: '',
    password: '',
    password_confirmation: '',
    image: null,
    remove_image: false,
});

// Gửi bằng POST kèm _method=put để PHP đọc được dữ liệu dạng multipart (có file ảnh)
const submit = () =>
    form
        .transform((data) => ({ ...data, _method: 'put' }))
        .post('/admin/profile', {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => form.reset('current_password', 'password', 'password_confirmation'),
        });
</script>

<template>
    <Head title="Thông tin hồ sơ" />

    <FormCard :processing="form.processing" submit-label="Lưu hồ sơ" @submit="submit">
        <ImageUpload
            v-model="form.image"
            v-model:removed="form.remove_image"
            label="Ảnh đại diện (avatar)"
            hint="JPG, PNG hoặc WEBP, tối đa 2MB."
            :current-url="profile.image_url"
            :error="form.errors.image"
        />

        <p class="text-[14px] text-slate-600">
            Vai trò: <strong class="text-slate-800">{{ profile.role_name ?? 'Chưa có' }}</strong>
        </p>

        <div class="grid gap-4 sm:grid-cols-2">
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
            label="Email"
            autocomplete="off"
            :error="form.errors.email"
        />

        <div class="border-t border-slate-200 pt-4">
            <h3 class="mb-3 text-[14px] font-semibold text-slate-800">Đổi mật khẩu</h3>

            <div class="space-y-4">
                <InputField
                    v-model="form.current_password"
                    name="current_password"
                    type="password"
                    label="Mật khẩu hiện tại"
                    autocomplete="current-password"
                    hint="Chỉ cần nhập khi muốn đổi mật khẩu."
                    :error="form.errors.current_password"
                />

                <div class="grid gap-4 sm:grid-cols-2">
                    <InputField
                        v-model="form.password"
                        name="password"
                        type="password"
                        label="Mật khẩu mới"
                        autocomplete="new-password"
                        hint="Tối thiểu 8 ký tự."
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
            </div>
        </div>
    </FormCard>
</template>