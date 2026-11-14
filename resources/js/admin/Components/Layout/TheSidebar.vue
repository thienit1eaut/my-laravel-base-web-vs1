<script setup>
import { computed, reactive } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Icon from '../UI/Icon.vue';
import { usePermission } from '../../Composables/usePermission.js';

defineProps({
    open: { type: Boolean, default: false }, // trạng thái mở trên màn hình nhỏ
});

const page = usePage();
const { can } = usePermission();
const year = new Date().getFullYear();

// Menu 2 cấp. Mục có "children" là nhóm. Có "permission" thì chỉ hiện khi người dùng có quyền đó.
// Thêm module mới: thêm một dòng vào "children" của nhóm phù hợp (hoặc thêm nhóm mới).
const menu = [
    { label: 'Tổng quan', href: '/admin', icon: 'home' },
    {
        label: 'Sản phẩm',
        icon: 'tag',
        children: [
            { label: 'Danh mục sản phẩm', href: '/admin/product-categories', permission: 'product-categories.view' },
            { label: 'Danh sách sản phẩm', href: '/admin/products', permission: 'products.view' },
            { label: 'Thẻ sản phẩm', href: '/admin/product-tags', permission: 'product-tags.view' },
            { label: 'Thương hiệu', href: '/admin/brands', permission: 'brands.view' },
        ],
    },
    {
        label: 'Bài viết',
        icon: 'document',
        children: [
            { label: 'Danh mục bài viết', href: '/admin/article-categories', permission: 'article-categories.view' },
            { label: 'Danh sách bài viết', href: '/admin/articles', permission: 'articles.view' },
            { label: 'Thẻ bài viết', href: '/admin/tags', permission: 'tags.view' },
        ],
    },
    { label: 'Thư viện ảnh', href: '/admin/medias', icon: 'photo', permission: 'medias.view' },
    {
        label: 'Người dùng',
        icon: 'users',
        children: [
            { label: 'Tài khoản', href: '/admin/accounts', permission: 'accounts.view' },
            { label: 'Vai trò', href: '/admin/roles', permission: 'roles.view' },
        ],
    },
    {
        label: 'Hệ thống',
        icon: 'menu',
        children: [
            { label: 'Ngôn ngữ', href: '/admin/languages', permission: 'languages.view' },
            { label: 'Bộ nhớ đệm', href: '/admin/cache', permission: 'cache.view' },
        ],
    },
];

const allowed = (item) => !item.permission || can(item.permission);

// Ẩn mục không có quyền; nhóm không còn mục con nào thì ẩn luôn
const visibleMenu = computed(() =>
    menu
        .map((item) => (item.children ? { ...item, children: item.children.filter(allowed) } : item))
        .filter((item) => (item.children ? item.children.length > 0 : allowed(item))),
);

const isActive = (href) => (href === '/admin' ? page.url === '/admin' : page.url.startsWith(href));
const hasActiveChild = (group) => group.children.some((child) => isActive(child.href));

// Nhóm tự mở khi có mục con đang xem; bấm vào tên nhóm để mở/đóng
const toggled = reactive({});
const isOpen = (group) => toggled[group.label] ?? hasActiveChild(group);
const toggle = (group) => {
    toggled[group.label] = !isOpen(group);
};
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-40 flex w-54 flex-col bg-slate-900 text-[14px] text-slate-100 transition-transform md:translate-x-0"
        :class="open ? 'translate-x-0' : '-translate-x-full'"
    >
        <!-- Logo -->
        <Link href="/admin" class="flex h-11 shrink-0 items-center gap-2 px-3 text-white">
            <span class="flex h-6 w-6 items-center justify-center rounded bg-indigo-600 text-xs font-bold">
                {{ (page.props.appName ?? 'A').charAt(0).toUpperCase() }}
            </span>
            <span class="truncate text-sm font-semibold">{{ page.props.appName }}</span>
        </Link>

        <!-- Menu -->
        <nav class="scroll-dark flex-1 overflow-y-auto py-1">
            <template v-for="item in visibleMenu" :key="item.label">
                <!-- Mục đơn -->
                <Link
                    v-if="!item.children"
                    :href="item.href"
                    class="flex items-center gap-2 px-3 py-2 font-semibold hover:text-wp-light"
                    :class="isActive(item.href) ? 'bg-indigo-600 text-white hover:text-white' : ''"
                >
                    <Icon :name="item.icon" size="h-[18px] w-[18px]" />
                    {{ item.label }}
                </Link>

                <!-- Nhóm 2 cấp -->
                <div v-else>
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-2 font-semibold text-left hover:text-wp-light"
                        :class="hasActiveChild(item) ? 'bg-indigo-600 text-white hover:text-white' : ''"
                        :aria-expanded="isOpen(item)"
                        @click="toggle(item)"
                    >
                        <Icon :name="item.icon" size="h-[18px] w-[18px]" />
                        <span class="flex-1">{{ item.label }}</span>
                        <Icon
                            name="chevron-down"
                            size="h-3 w-3"
                            class="transition-transform"
                            :class="{ 'rotate-180': isOpen(item) }"
                        />
                    </button>

                    <ul v-if="isOpen(item)" class="bg-slate-800 py-1">
                        <li v-for="child in item.children" :key="child.href">
                            <Link
                                :href="child.href"
                                class="block py-1 pl-9 pr-3 hover:text-wp-light"
                                :class="isActive(child.href) ? 'font-semibold text-white' : 'text-slate-300'"
                            >
                                {{ child.label }}
                            </Link>
                        </li>
                    </ul>
                </div>
            </template>
        </nav>

        <!-- Bản quyền -->
        <div class="shrink-0 border-t border-white/10 px-3 py-2 text-[12px] text-slate-400">
            &copy; {{ year }} {{ page.props.appName }}
        </div>
    </aside>
</template>