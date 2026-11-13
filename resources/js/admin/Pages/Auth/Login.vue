<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputField from '../../Components/Fields/InputField.vue';
import CheckboxField from '../../Components/Fields/CheckboxField.vue';
import Button from '../../Components/UI/Button.vue';

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/admin/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Đăng nhập quản trị" />

    <div class="flex min-h-screen items-center justify-center px-4">
        <form
            class="w-full max-w-xs space-y-4 rounded border border-slate-300 bg-white p-6 shadow-[0_1px_3px_rgba(0,0,0,0.04)]"
            @submit.prevent="submit"
        >
            <h1 class="text-center text-lg font-semibold text-slate-800">Đăng nhập quản trị</h1>

            <InputField
                v-model="form.login"
                name="login"
                label="Email hoặc tên đăng nhập"
                autocomplete="username"
                autofocus
                :error="form.errors.login"
            />
            <InputField
                v-model="form.password"
                name="password"
                type="password"
                label="Mật khẩu"
                autocomplete="current-password"
                :error="form.errors.password"
            />
            <CheckboxField v-model="form.remember" label="Ghi nhớ đăng nhập" />

            <Button type="submit" class="w-full" :disabled="form.processing">Đăng nhập</Button>
        </form>
    </div>
</template>