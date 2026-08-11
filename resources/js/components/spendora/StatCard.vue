<script setup lang="ts">
defineProps<{
    label: string;
    value: string;
    hint?: string;
    tone?: 'default' | 'positive' | 'negative';
}>();
</script>

<template>
    <div
        class="rounded-xl border border-[#bbcabf] bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)]"
    >
        <p class="text-sm font-medium text-[#3c4a42]">{{ label }}</p>
        <p class="mt-2 text-2xl font-semibold tracking-tight text-[#161d19]">
            {{ value }}
        </p>
        <p
            v-if="hint"
            class="mt-1 text-xs font-medium"
            :class="{
                'text-[#3c4a42]': !tone || tone === 'default',
                'text-[#006c49]': tone === 'positive',
                'text-red-600': tone === 'negative',
            }"
        >
            {{ hint }}
        </p>
    </div>
</template>
