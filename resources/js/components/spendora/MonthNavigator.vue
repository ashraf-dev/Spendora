<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { useTranslations } from '@/composables/useTranslations';
import { monthLabel } from '@/lib/format';
import type { MonthNavigation } from '@/types/spendora';

const props = defineProps<{
    month: number;
    year: number;
    navigation: MonthNavigation;
    baseUrl: string;
}>();
const { t } = useTranslations();

function hrefFor(target: { month: number; year: number }): string {
    const url = new URL(props.baseUrl, window.location.origin);
    url.searchParams.set('month', String(target.month));
    url.searchParams.set('year', String(target.year));

    return `${url.pathname}?${url.searchParams.toString()}`;
}
</script>

<template>
    <div
        class="flex items-center justify-between gap-3 rounded-xl border border-[#bbcabf] bg-white px-3 py-2"
    >
        <Link
            :href="hrefFor(navigation.previous_month)"
            class="inline-flex size-9 items-center justify-center rounded-lg text-[#006c49] transition hover:bg-[#eef6ee]"
            preserve-scroll
            :aria-label="t('common.previous_month')"
        >
            <ChevronLeft class="size-5" />
        </Link>

        <p class="text-sm font-semibold text-[#161d19] md:text-base">
            {{ monthLabel(month, year) }}
        </p>

        <Link
            :href="hrefFor(navigation.next_month)"
            class="inline-flex size-9 items-center justify-center rounded-lg text-[#006c49] transition hover:bg-[#eef6ee]"
            preserve-scroll
            :aria-label="t('common.next_month')"
        >
            <ChevronRight class="size-5" />
        </Link>
    </div>
</template>
