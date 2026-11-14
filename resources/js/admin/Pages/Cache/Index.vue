<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Button from '../../Components/UI/Button.vue';
import { usePermission } from '../../Composables/usePermission.js';

defineOptions({ layout: AdminLayout });

defineProps({
    pageCacheEnabled: Boolean,
});

const { can } = usePermission();
const processing = ref(null);

const clear = (url, message, key) => {
    if (!confirm(message)) return;

    router.post(url, {}, {
        preserveScroll: true,
        onStart: () => (processing.value = key),
        onFinish: () => (processing.value = null),
    });
};
</script>

<template>
    <Head title="Bộ nhớ đệm" />

    <div class="max-w-2xl space-y-4">
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <h2 class="text-[15px] font-semibold text-slate-800">Cache trang web</h2>
            <p class="mt-1 text-[14px] text-slate-600">
                HTML các trang bài viết và danh mục hiển thị cho khách được lưu sẵn để tải nhanh.
                Thêm, sửa, xóa bài hoặc danh mục sẽ tự làm mới; dùng nút này khi muốn làm mới ngay (ví dụ vừa thay ảnh trong Thư viện).
            </p>
            <p v-if="!pageCacheEnabled" class="mt-2 text-xs text-amber-700">
                Cache trang web đang tắt (PAGE_CACHE_ENABLED=false trong .env), nên hiện chưa có trang nào được lưu.
            </p>
            <Button
                v-if="can('cache.clear-web')"
                class="mt-3"
                :disabled="processing === 'web'"
                @click="clear('/admin/cache/clear-web', 'Xóa cache trang web?', 'web')"
            >
                Xóa cache trang web
            </Button>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm">
            <h2 class="text-[15px] font-semibold text-slate-800">Cache quản trị</h2>
            <p class="mt-1 text-[14px] text-slate-600">
                Dữ liệu dùng chung trong quản trị được lưu tạm (hiện là cây danh mục cha/con).
                Dùng nút này nếu bạn sửa dữ liệu trực tiếp trong database và thấy danh sách chưa cập nhật.
            </p>
            <Button
                v-if="can('cache.clear-admin')"
                variant="secondary"
                class="mt-3"
                :disabled="processing === 'admin'"
                @click="clear('/admin/cache/clear-admin', 'Xóa cache quản trị?', 'admin')"
            >
                Xóa cache quản trị
            </Button>
        </div>
    </div>
</template>
