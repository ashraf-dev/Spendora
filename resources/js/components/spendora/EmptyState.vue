<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-xl border border-dashed border-[#bbcabf] bg-[#eef6ee]/60 px-6 py-12 text-center"
    >
        <p class="text-base font-semibold text-[#161d19]">{{ title }}</p>
        <p v-if="description" class="mt-2 max-w-sm text-sm text-[#3c4a42]">
            {{ description }}
        </p>
        <div v-if="$slots.default" class="mt-4">
            <slot />
        </div>
    </div>
</template>
