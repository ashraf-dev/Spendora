<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import EmptyState from '@/components/spendora/EmptyState.vue';
import MonthNavigator from '@/components/spendora/MonthNavigator.vue';
import StatCard from '@/components/spendora/StatCard.vue';
import { formatMoney } from '@/lib/format';
import type {
    CategoryPayload,
    MonthNavigation,
    PaginatedExpenses,
} from '@/types/spendora';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Categories',
                href: '/categories',
            },
        ],
    },
});

const props = defineProps<{
    category: CategoryPayload;
    month: number;
    year: number;
    selected_month_total: string;
    expense_count: number;
    expenses: PaginatedExpenses;
    navigation: MonthNavigation;
}>();

function goToPage(page: number): void {
    router.get(
        `/categories/${props.category.id}`,
        {
            month: props.month,
            year: props.year,
            page,
        },
        { preserveState: true },
    );
}
</script>

<template>
    <Head :title="category.name" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <Link
                :href="`/categories?month=${month}&year=${year}`"
                class="text-sm font-medium text-[#006c49] hover:underline"
            >
                Back to categories
            </Link>
            <div
                class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
            >
                <div>
                    <h1 class="text-2xl font-semibold text-[#161d19]">
                        {{ category.name }}
                    </h1>
                    <p class="mt-1 text-sm text-[#3c4a42]">
                        Category spending for the selected month.
                    </p>
                </div>
                <div class="w-full sm:max-w-xs">
                    <MonthNavigator
                        :month="month"
                        :year="year"
                        :navigation="navigation"
                        :base-url="`/categories/${category.id}`"
                    />
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <StatCard
                label="Month total"
                :value="formatMoney(selected_month_total)"
            />
            <StatCard label="Expenses" :value="String(expense_count)" />
        </div>

        <section
            class="overflow-hidden rounded-xl border border-[#bbcabf] bg-white"
        >
            <div class="border-b border-[#bbcabf] px-4 py-3">
                <h2 class="font-semibold text-[#161d19]">Expenses</h2>
            </div>

            <EmptyState
                v-if="expenses.data.length === 0"
                title="No expenses found for this month"
                description="Try another month or add an expense in this category."
                class="m-4"
            />

            <ul v-else class="divide-y divide-[#bbcabf]/60">
                <li
                    v-for="expense in expenses.data"
                    :key="expense.id"
                    class="flex items-start justify-between gap-3 px-4 py-3"
                >
                    <div class="min-w-0">
                        <p class="truncate font-medium text-[#161d19]">
                            {{ expense.description || 'Expense' }}
                        </p>
                        <p class="text-sm text-[#3c4a42]">
                            {{ expense.expense_date }}
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <p class="font-semibold text-[#161d19]">
                            {{ formatMoney(expense.amount) }}
                        </p>
                        <Link
                            :href="`/expenses/${expense.id}/edit`"
                            class="text-sm font-medium text-[#006c49] hover:underline"
                        >
                            Edit
                        </Link>
                    </div>
                </li>
            </ul>

            <div
                v-if="expenses.meta.last_page > 1"
                class="flex items-center justify-between border-t border-[#bbcabf] px-4 py-3 text-sm text-[#3c4a42]"
            >
                <p>
                    Page {{ expenses.meta.current_page }} of
                    {{ expenses.meta.last_page }}
                </p>
                <div class="flex gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-[#bbcabf] px-3 py-1.5 disabled:opacity-40"
                        :disabled="expenses.meta.current_page <= 1"
                        @click="goToPage(expenses.meta.current_page - 1)"
                    >
                        Previous
                    </button>
                    <button
                        type="button"
                        class="rounded-lg border border-[#bbcabf] px-3 py-1.5 disabled:opacity-40"
                        :disabled="
                            expenses.meta.current_page >=
                            expenses.meta.last_page
                        "
                        @click="goToPage(expenses.meta.current_page + 1)"
                    >
                        Next
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>
