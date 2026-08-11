<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Bell, Plus, ReceiptText, TrendingDown, TrendingUp } from '@lucide/vue';
import { computed } from 'vue';
import EmptyState from '@/components/spendora/EmptyState.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import { formatMoney, formatPercent } from '@/lib/format';
import { dashboard } from '@/routes';
import {
    create as createExpense,
    index as expensesIndex,
} from '@/routes/expenses';
import type { ExpensePayload } from '@/types/spendora';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const props = defineProps<{
    latest_expenses: ExpensePayload[];
    totals: {
        today: string;
        current_month: string;
        previous_month: string;
        current_year: string;
        same_day_last_year: string;
    };
    comparisons: {
        month_over_month_percentage: number;
        today_vs_same_day_last_year_percentage: number;
    };
}>();

const page = usePage();
const { getInitials } = useInitials();

const user = computed(() => page.props.auth.user);
const firstName = computed(() => user.value.name.trim().split(/\s+/u)[0]);
const greeting = computed(() => {
    const hour = new Date().getHours();

    if (hour < 12) {
        return 'Good morning';
    }

    if (hour < 18) {
        return 'Good afternoon';
    }

    return 'Good evening';
});

const todayAmount = computed(() => Number(props.totals.today));
const lastYearAmount = computed(() => Number(props.totals.same_day_last_year));
const comparisonMaximum = computed(() =>
    Math.max(todayAmount.value, lastYearAmount.value),
);

function comparisonWidth(amount: number): string {
    if (comparisonMaximum.value <= 0) {
        return '0%';
    }

    return `${Math.max(4, (amount / comparisonMaximum.value) * 100)}%`;
}

function formatExpenseDate(date: string): string {
    return new Intl.DateTimeFormat(undefined, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(`${date}T00:00:00`));
}
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="min-h-full flex-1 bg-[#f4fbf4] px-5 py-8 font-sans text-[#161d19] md:px-10"
    >
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-6">
            <header class="flex items-center justify-between gap-6">
                <div class="min-w-0">
                    <h1
                        class="text-2xl font-semibold tracking-tight text-[#161d19] md:text-3xl"
                    >
                        {{ greeting }}, {{ firstName }}
                    </h1>
                    <p class="mt-1 text-sm text-[#565e74] md:text-base">
                        Here's a look at your finances today.
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-3 md:gap-4">
                    <button
                        type="button"
                        class="inline-flex size-11 items-center justify-center rounded-full border border-[#bbcabf] bg-white text-[#161d19] transition-colors hover:bg-[#e8f0e9] md:size-12"
                        aria-label="Notifications"
                    >
                        <Bell class="size-5" />
                    </button>

                    <Avatar
                        class="size-11 border-2 border-[#e3eae3] shadow-sm md:size-12"
                    >
                        <AvatarImage
                            v-if="user.avatar"
                            :src="user.avatar"
                            :alt="user.name"
                        />
                        <AvatarFallback
                            class="bg-[#006c49] font-semibold text-white"
                        >
                            {{ getInitials(user.name) }}
                        </AvatarFallback>
                    </Avatar>
                </div>
            </header>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <section
                    class="group relative overflow-hidden rounded-2xl border border-[#bbcabf] bg-white p-6 shadow-[0_4px_6px_-1px_rgba(15,23,42,0.05),0_2px_4px_-2px_rgba(15,23,42,0.05)] lg:col-span-2"
                >
                    <div
                        class="absolute -top-20 -right-20 size-64 rounded-full bg-[#10b981]/10 blur-3xl transition-opacity duration-500 group-hover:opacity-80"
                    />

                    <div
                        class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-end"
                    >
                        <div>
                            <p
                                class="text-sm font-medium tracking-wider text-[#565e74] uppercase"
                            >
                                Total spending this month
                            </p>
                            <p
                                class="mt-2 text-4xl font-bold tracking-tight text-[#161d19] md:text-5xl"
                            >
                                {{ formatMoney(totals.current_month) }}
                            </p>
                            <div class="mt-4 flex flex-wrap items-center gap-2">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full border border-[#006c49]/20 bg-[#e8f0e9] px-3 py-1 text-sm font-medium text-[#006c49]"
                                >
                                    <TrendingUp
                                        v-if="
                                            comparisons.month_over_month_percentage >=
                                            0
                                        "
                                        class="size-4"
                                    />
                                    <TrendingDown v-else class="size-4" />
                                    {{
                                        formatPercent(
                                            comparisons.month_over_month_percentage,
                                        )
                                    }}
                                </span>
                                <span class="text-sm text-[#565e74]">
                                    vs last month
                                </span>
                            </div>
                        </div>

                        <Link
                            :href="createExpense()"
                            class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-lg bg-[#006c49] px-6 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#005236]"
                        >
                            <Plus class="size-5" />
                            Add Expense
                        </Link>
                    </div>
                </section>

                <section
                    class="flex flex-col justify-between rounded-2xl border border-[#bbcabf] bg-white p-6 shadow-[0_4px_6px_-1px_rgba(15,23,42,0.05),0_2px_4px_-2px_rgba(15,23,42,0.05)]"
                >
                    <div class="flex items-start justify-between gap-3">
                        <p
                            class="text-sm font-medium tracking-wider text-[#565e74] uppercase"
                        >
                            Today vs same day last year
                        </p>
                        <span
                            class="shrink-0 rounded-full bg-[#eef6ee] px-2.5 py-1 text-xs font-semibold text-[#006c49]"
                        >
                            {{
                                formatPercent(
                                    comparisons.today_vs_same_day_last_year_percentage,
                                )
                            }}
                        </span>
                    </div>

                    <div class="mt-6 flex flex-col gap-6">
                        <div>
                            <div
                                class="mb-2 flex items-center justify-between gap-4"
                            >
                                <span class="text-sm font-medium">Today</span>
                                <span class="text-sm font-semibold">
                                    {{ formatMoney(totals.today) }}
                                </span>
                            </div>
                            <div
                                class="h-3 overflow-hidden rounded-full bg-[#e8f0e9]"
                            >
                                <div
                                    class="h-full rounded-full bg-[#006c49] transition-[width] duration-500"
                                    :style="{
                                        width: comparisonWidth(todayAmount),
                                    }"
                                />
                            </div>
                        </div>

                        <div>
                            <div
                                class="mb-2 flex items-center justify-between gap-4"
                            >
                                <span
                                    class="text-sm font-medium text-[#565e74]"
                                >
                                    Last year
                                </span>
                                <span
                                    class="text-sm font-medium text-[#565e74]"
                                >
                                    {{ formatMoney(totals.same_day_last_year) }}
                                </span>
                            </div>
                            <div
                                class="h-3 overflow-hidden rounded-full bg-[#e8f0e9]"
                            >
                                <div
                                    class="h-full rounded-full bg-[#6c7a71] transition-[width] duration-500"
                                    :style="{
                                        width: comparisonWidth(lastYearAmount),
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <section
                class="overflow-hidden rounded-2xl border border-[#bbcabf] bg-white shadow-[0_4px_6px_-1px_rgba(15,23,42,0.05),0_2px_4px_-2px_rgba(15,23,42,0.05)]"
            >
                <div
                    class="flex items-center justify-between gap-4 border-b border-[#bbcabf] bg-[#f4fbf4] p-6"
                >
                    <h2
                        class="text-xl font-semibold tracking-tight md:text-2xl"
                    >
                        Recent Expenses
                    </h2>
                    <Link
                        :href="expensesIndex()"
                        class="text-sm font-medium text-[#006c49] underline-offset-4 hover:underline"
                    >
                        View All
                    </Link>
                </div>

                <EmptyState
                    v-if="latest_expenses.length === 0"
                    class="m-6"
                    title="No recent expenses"
                    description="Add your first expense to see it here."
                />

                <div v-else class="overflow-x-auto">
                    <table
                        class="w-full min-w-[720px] border-collapse text-left"
                    >
                        <thead>
                            <tr class="border-b border-[#bbcabf] bg-[#f4fbf4]">
                                <th class="w-20 px-6 py-4" aria-label="Type" />
                                <th
                                    class="px-6 py-4 text-xs font-semibold tracking-wider text-[#565e74] uppercase"
                                >
                                    Transaction
                                </th>
                                <th
                                    class="px-6 py-4 text-xs font-semibold tracking-wider text-[#565e74] uppercase"
                                >
                                    Category
                                </th>
                                <th
                                    class="px-6 py-4 text-xs font-semibold tracking-wider text-[#565e74] uppercase"
                                >
                                    Date
                                </th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold tracking-wider text-[#565e74] uppercase"
                                >
                                    Amount
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#bbcabf]/50">
                            <tr
                                v-for="expense in latest_expenses"
                                :key="expense.id"
                                class="group transition-colors hover:bg-[#eef6ee]"
                            >
                                <td class="px-6 py-4">
                                    <div
                                        class="flex size-10 items-center justify-center rounded-full bg-[#e8f0e9] text-[#006c49] transition-colors group-hover:bg-[#10b981] group-hover:text-white"
                                    >
                                        <ReceiptText class="size-5" />
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium">
                                    {{
                                        expense.description ||
                                        expense.category?.name ||
                                        'Expense'
                                    }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full bg-[#e8f0e9] px-3 py-1 text-xs font-semibold text-[#3c4a42]"
                                    >
                                        {{ expense.category?.name || 'Other' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-[#565e74]">
                                    {{
                                        formatExpenseDate(expense.expense_date)
                                    }}
                                </td>
                                <td
                                    class="px-6 py-4 text-right font-semibold whitespace-nowrap"
                                >
                                    -{{ formatMoney(expense.amount) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</template>
