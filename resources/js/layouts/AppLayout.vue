<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [], hideHeader = false } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
    hideHeader?: boolean;
}>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" :hide-header="hideHeader">
        <slot />
    </AppLayout>
</template>
