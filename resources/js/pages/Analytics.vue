<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import EmptyState from '@/components/spendora/EmptyState.vue';
import MonthNavigator from '@/components/spendora/MonthNavigator.vue';
import StatCard from '@/components/spendora/StatCard.vue';
import { formatMoney, formatPercent } from '@/lib/format';
import type {
    CategoryTotalRow,
    MonthNavigation,
} from '@/types/spendora';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Analytics',
                href: '/analytics',
            },
        ],
    },
});

const props = defineProps<{
    month: number;
    year: number;
    selected_month_total: string;
    previous_month_total: string;
    current_year_total: string;
    expense_count: number;
    highest_expense: string;
    average_expense: string;
    daily_totals: Array<{ date: string; total: string }>;
    category_totals: CategoryTotalRow[];
    navigation: MonthNavigation;
}>();

const chartBarMaxHeightPx = 160;

const maxDaily = computed(() =>
    Math.max(
        ...props.daily_totals.map((day) => Number(day.total)),
        0,
    ),
);

const monthChange = computed(() => {
    const current = Number(props.selected_month_total);
    const previous = Number(props.previous_month_total);

    if (previous === 0) {
        return current === 0 ? 0 : 100;
    }

    return ((current - previous) / previous) * 100;
});

const activeDays = computed(() =>
    props.daily_totals.filter((day) => Number(day.total) > 0),
);

function barHeightPx(total: string): string {
    const value = Number(total);

    if (maxDaily.value <= 0 || value <= 0) {
        return '0px';
    }

    return `${Math.max((value / maxDaily.value) * chartBarMaxHeightPx, 4)}px`;
}

function dayNumber(date: string): string {
    return String(Number(date.slice(-2)));
}
</script>

<template>
    <Head title="Analytics" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-[#161d19]">
                    Analytics
                </h1>
                <p class="mt-1 text-sm text-[#3c4a42]">
                    Monthly totals, trends, and category breakdown.
                </p>
            </div>
            <div class="w-full sm:max-w-xs">
                <MonthNavigator
                    :month="month"
                    :year="year"
                    :navigation="navigation"
                    base-url="/analytics"
                />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <StatCard
                label="Selected month"
                :value="formatMoney(selected_month_total)"
                :hint="`${formatPercent(monthChange)} vs previous month`"
                :tone="
                    monthChange > 0
                        ? 'negative'
                        : monthChange < 0
                          ? 'positive'
                          : 'default'
                "
            />
            <StatCard
                label="Previous month"
                :value="formatMoney(previous_month_total)"
            />
            <StatCard
                label="Year to date"
                :value="formatMoney(current_year_total)"
            />
            <StatCard label="Expense count" :value="String(expense_count)" />
            <StatCard
                label="Highest expense"
                :value="formatMoney(highest_expense)"
            />
            <StatCard
                label="Average expense"
                :value="formatMoney(average_expense)"
            />
        </div>

        <section
            class="rounded-xl border border-[#bbcabf] bg-white p-4 md:p-5"
        >
            <h2 class="text-lg font-semibold text-[#161d19]">
                Daily spending
            </h2>
            <p class="mt-1 text-sm text-[#3c4a42]">
                Totals for each day in the selected month.
            </p>

            <EmptyState
                v-if="activeDays.length === 0"
                class="mt-6"
                title="No expenses found for this month"
                description="Add expenses to see daily trends."
            />

            <div
                v-else
                class="mt-6 flex h-52 items-end gap-1 overflow-x-auto pb-1"
                role="img"
                :aria-label="`Daily spending for ${month}/${year}`"
            >
                <div
                    v-for="day in daily_totals"
                    :key="day.date"
                    class="flex h-full min-w-2.5 flex-1 flex-col items-center justify-end gap-1"
                    :title="`${day.date}: ${formatMoney(day.total)}`"
                >
                    <div
                        class="w-full max-w-4 rounded-t"
                        :class="
                            Number(day.total) > 0
                                ? 'bg-[#006c49]/80'
                                : 'bg-transparent'
                        "
                        :style="{ height: barHeightPx(day.total) }"
                    />
                    <span class="text-[10px] leading-none text-[#565e74]">
                        {{ dayNumber(day.date) }}
                    </span>
                </div>
            </div>
        </section>

        <section
            class="rounded-xl border border-[#bbcabf] bg-white p-4 md:p-5"
        >
            <h2 class="text-lg font-semibold text-[#161d19]">
                By category
            </h2>

            <EmptyState
                v-if="
                    category_totals.every(
                        (row) => Number(row.total_amount) === 0,
                    )
                "
                class="mt-6"
                title="No category data available"
            />

            <ul v-else class="mt-4 space-y-4">
                <li
                    v-for="row in category_totals.filter(
                        (item) => Number(item.total_amount) > 0,
                    )"
                    :key="row.category.id"
                >
                    <div class="mb-1 flex items-center justify-between gap-3">
                        <p class="font-medium text-[#161d19]">
                            {{ row.category.name }}
                        </p>
                        <p class="text-sm font-semibold text-[#161d19]">
                            {{ formatMoney(row.total_amount) }}
                        </p>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-[#eef6ee]">
                        <div
                            class="h-full rounded-full bg-[#10b981]"
                            :style="{
                                width: `${
                                    Number(selected_month_total) > 0
                                        ? Math.min(
                                              (Number(row.total_amount) /
                                                  Number(
                                                      selected_month_total,
                                                  )) *
                                                  100,
                                              100,
                                          )
                                        : 0
                                }%`,
                            }"
                        />
                    </div>
                </li>
            </ul>
        </section>
    </div>
</template>
