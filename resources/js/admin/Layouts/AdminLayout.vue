<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import TheSidebar from '../Components/Layout/TheSidebar.vue';
import TheHeader from '../Components/Layout/TheHeader.vue';
import Breadcrumb from '../Components/UI/Breadcrumb.vue';
import FlashMessage from '../Components/UI/FlashMessage.vue';

const page = usePage();

// Menu trượt ra trên màn hình nhỏ, tự đóng khi chuyển trang
const sidebarOpen = ref(false);
watch(
    () => page.url,
    () => {
        sidebarOpen.value = false;
    },
);
</script>

<template>
    <div class="min-h-screen md:pl-54">
        <TheSidebar :open="sidebarOpen" />
        <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-black/40 md:hidden" @click="sidebarOpen = false" />

        <TheHeader @toggle-sidebar="sidebarOpen = !sidebarOpen" />

        <main class="p-4">
            <!-- Breadcrumb do controller trả về (prop "breadcrumbs") -->
            <Breadcrumb :items="page.props.breadcrumbs ?? []" class="mb-3" />
            <slot />
        </main>

        <FlashMessage />
    </div>
</template>