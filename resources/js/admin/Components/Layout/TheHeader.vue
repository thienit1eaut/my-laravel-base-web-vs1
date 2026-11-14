<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Icon from '../UI/Icon.vue';
import Dropdown from '../UI/Dropdown.vue';
import DropdownItem from '../UI/DropdownItem.vue';

defineEmits(['toggle-sidebar']);

const page = usePage();

const user = computed(() => page.props.auth?.user);
// Tiêu đề trang do controller trả về (prop "title")
const title = computed(() => page.props.title ?? '');

const logout = () => router.post('/admin/logout');
</script>

<template>
    <header class="sticky top-0 z-20 flex h-11 items-center gap-2 bg-slate-900 px-3 text-slate-100">
        <!-- Nút mở menu (chỉ hiện trên màn hình nhỏ) -->
        <button
            type="button"
            class="rounded p-1 hover:bg-white/10 md:hidden"
            aria-label="Mở menu"
            @click="$emit('toggle-sidebar')"
        >
            <Icon name="menu" size="h-5 w-5" />
        </button>

        <h1 class="truncate text-[15px] font-semibold text-white">{{ title }}</h1>

        <div class="ml-auto flex items-center gap-1">
            <!-- Tạm thời chưa có chức năng -->
            <button
                type="button"
                class="rounded p-1.5 text-slate-300 hover:bg-white/10 hover:text-white"
                title="Thông báo"
                aria-label="Thông báo"
            >
                <Icon name="bell" size="h-5 w-5" />
            </button>
            <button
                type="button"
                class="rounded p-1.5 text-slate-300 hover:bg-white/10 hover:text-white"
                title="Tin nhắn"
                aria-label="Tin nhắn"
            >
                <Icon name="chat" size="h-5 w-5" />
            </button>

            <div class="ml-2">
                <Dropdown>
                    <template #trigger>
                        <button type="button" class="flex items-center gap-2 rounded px-1.5 py-1 hover:bg-white/10">
                            <img
                                v-if="user?.image_url"
                                :src="user.image_url"
                                alt=""
                                class="h-7 w-7 rounded-full object-cover"
                            />
                            <span
                                v-else
                                class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-600 text-xs font-semibold text-white"
                            >
                                {{ (user?.name ?? '?').charAt(0).toUpperCase() }}
                            </span>

                            <span class="hidden text-left leading-tight sm:block">
                                <span class="block text-xs font-semibold text-white">{{ user?.name }}</span>
                                <span v-if="user?.username" class="block text-[12px] text-slate-400">
                                    @{{ user.username }}
                                </span>
                            </span>

                            <Icon name="chevron-down" size="h-3 w-3" class="text-slate-400" />
                        </button>
                    </template>

                    <DropdownItem href="/admin/profile">Thông tin hồ sơ</DropdownItem>
                    <div class="my-1 border-t border-slate-200" />
                    <DropdownItem @click="logout">Đăng xuất hệ thống</DropdownItem>
                </Dropdown>
            </div>
        </div>
    </header>
</template>