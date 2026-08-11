<script setup lang="ts">
import { Form, Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import EmptyState from '@/components/spendora/EmptyState.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { formatMoney } from '@/lib/format';
import type {
    CategoryPayload,
    ExpensePayload,
    PaginatedExpenses,
} from '@/types/spendora';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Expenses',
                href: '/expenses',
            },
        ],
    },
});

const props = defineProps<{
    expenses: PaginatedExpenses;
    total_amount: string;
    categories: CategoryPayload[];
    filters: {
        month: number | null;
        year: number | null;
        category_id: number | null;
        date_from: string | null;
        date_to: string | null;
        search: string | null;
        sort: string;
        direction: string;
        per_page: number;
    };
}>();

const search = ref(props.filters.search ?? '');
const categoryId = ref(
    props.filters.category_id ? String(props.filters.category_id) : '',
);
const month = ref(props.filters.month ? String(props.filters.month) : '');
const year = ref(props.filters.year ? String(props.filters.year) : '');
const expenseToDelete = ref<ExpensePayload | null>(null);
const deleteDialogOpen = ref(false);

watch(
    () => props.filters,
    (filters) => {
        search.value = filters.search ?? '';
        categoryId.value = filters.category_id
            ? String(filters.category_id)
            : '';
        month.value = filters.month ? String(filters.month) : '';
        year.value = filters.year ? String(filters.year) : '';
    },
);

const hasExpenses = computed(() => props.expenses.data.length > 0);

function categoryName(expense: {
    category_id: number;
    category?: CategoryPayload | null;
}): string {
    if (expense.category?.name) {
        return expense.category.name;
    }

    return (
        props.categories.find((category) => category.id === expense.category_id)
            ?.name ?? '—'
    );
}

function askDeleteExpense(expense: ExpensePayload): void {
    expenseToDelete.value = expense;
    deleteDialogOpen.value = true;
}

function closeDeleteDialog(): void {
    deleteDialogOpen.value = false;
    expenseToDelete.value = null;
}

function applyFilters(): void {
    router.get(
        '/expenses',
        {
            search: search.value || undefined,
            category_id: categoryId.value || undefined,
            month: month.value || undefined,
            year: year.value || undefined,
            sort: props.filters.sort,
            direction: props.filters.direction,
            per_page: props.filters.per_page,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
}

function goToPage(page: number): void {
    router.get(
        '/expenses',
        {
            ...Object.fromEntries(
                Object.entries({
                    search: search.value || undefined,
                    category_id: categoryId.value || undefined,
                    month: month.value || undefined,
                    year: year.value || undefined,
                    sort: props.filters.sort,
                    direction: props.filters.direction,
                    per_page: props.filters.per_page,
                    page,
                }).filter(([, value]) => value !== undefined),
            ),
        },
        { preserveState: true },
    );
}
</script>

<template>
    <Head title="Expenses" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-[#161d19]">Expenses</h1>
                <p class="mt-1 text-sm text-[#3c4a42]">
                    Browse, filter, and manage your expense history.
                </p>
            </div>
            <Link
                href="/expenses/create"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#006c49] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#00422b]"
            >
                <Plus class="size-4" />
                Add expense
            </Link>
        </div>

        <form
            class="grid gap-3 rounded-xl border border-[#bbcabf] bg-white p-4 md:grid-cols-4"
            @submit.prevent="applyFilters"
        >
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium text-[#161d19]"
                    >Search</label
                >
                <Input
                    v-model="search"
                    type="search"
                    placeholder="Search description"
                    class="h-11 border-[#bbcabf]"
                />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-[#161d19]"
                    >Category</label
                >
                <select
                    v-model="categoryId"
                    class="flex h-11 w-full rounded-lg border border-[#bbcabf] bg-white px-3 text-sm text-[#161d19]"
                >
                    <option value="">All categories</option>
                    <option
                        v-for="category in categories"
                        :key="category.id"
                        :value="String(category.id)"
                    >
                        {{ category.name }}
                    </option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-[#161d19]"
                        >Month</label
                    >
                    <Input
                        v-model="month"
                        type="number"
                        min="1"
                        max="12"
                        placeholder="MM"
                        class="h-11 border-[#bbcabf]"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-[#161d19]"
                        >Year</label
                    >
                    <Input
                        v-model="year"
                        type="number"
                        min="2000"
                        max="2100"
                        placeholder="YYYY"
                        class="h-11 border-[#bbcabf]"
                    />
                </div>
            </div>
            <div class="md:col-span-4">
                <button
                    type="submit"
                    class="rounded-lg bg-[#006c49] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#00422b]"
                >
                    Apply filters
                </button>
            </div>
        </form>

        <EmptyState
            v-if="!hasExpenses"
            title="No expenses found"
            description="Try adjusting filters or add a new expense."
        >
            <Link
                href="/expenses/create"
                class="inline-flex rounded-lg bg-[#006c49] px-4 py-2 text-sm font-semibold text-white"
            >
                Add expense
            </Link>
        </EmptyState>

        <div
            v-else
            class="overflow-hidden rounded-xl border border-[#bbcabf] bg-white"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#eef6ee] text-[#3c4a42]">
                        <tr>
                            <th class="px-4 py-3 font-medium">Date</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Description</th>
                            <th class="px-4 py-3 font-medium text-end">
                                Amount
                            </th>
                            <th class="px-4 py-3 font-medium text-end">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#bbcabf]/60">
                        <tr
                            v-for="expense in expenses.data"
                            :key="expense.id"
                            class="text-[#161d19]"
                        >
                            <td class="whitespace-nowrap px-4 py-3">
                                {{ expense.expense_date }}
                            </td>
                            <td class="px-4 py-3">
                                {{ categoryName(expense) }}
                            </td>
                            <td class="max-w-xs truncate px-4 py-3">
                                {{ expense.description || '—' }}
                            </td>
                            <td
                                class="whitespace-nowrap px-4 py-3 text-end font-semibold"
                            >
                                {{ formatMoney(expense.amount) }}
                            </td>
                            <td class="px-4 py-3">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Link
                                        :href="`/expenses/${expense.id}/edit`"
                                        class="inline-flex size-8 items-center justify-center rounded-lg text-[#006c49] hover:bg-[#eef6ee]"
                                        :aria-label="`Edit expense ${expense.id}`"
                                    >
                                        <Pencil class="size-4" />
                                    </Link>
                                    <button
                                        type="button"
                                        class="inline-flex size-8 items-center justify-center rounded-lg text-red-600 hover:bg-red-50"
                                        :aria-label="`Delete expense ${expense.id}`"
                                        data-test="delete-expense-button"
                                        @click="askDeleteExpense(expense)"
                                    >
                                        <Trash2 class="size-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="border-t border-[#bbcabf] bg-[#eef6ee]">
                        <tr class="text-[#161d19]">
                            <td
                                class="px-4 py-3 text-end font-semibold"
                                colspan="3"
                            >
                                Total
                            </td>
                            <td
                                class="whitespace-nowrap px-4 py-3 text-end font-semibold"
                            >
                                {{ formatMoney(total_amount) }}
                            </td>
                            <td class="px-4 py-3" />
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div
                v-if="expenses.meta.last_page > 1"
                class="flex items-center justify-between border-t border-[#bbcabf] px-4 py-3 text-sm text-[#3c4a42]"
            >
                <p>
                    Page {{ expenses.meta.current_page }} of
                    {{ expenses.meta.last_page }}
                    · {{ expenses.meta.total }} total
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
        </div>

        <Dialog
            :open="deleteDialogOpen"
            @update:open="
                (open) => (open ? (deleteDialogOpen = true) : closeDeleteDialog())
            "
        >
            <DialogContent>
                <Form
                    v-if="expenseToDelete"
                    :action="`/expenses/${expenseToDelete.id}`"
                    method="delete"
                    class="space-y-6"
                    v-slot="{ processing }"
                    @success="closeDeleteDialog"
                >
                    <DialogHeader class="space-y-3">
                        <DialogTitle>Delete this expense?</DialogTitle>
                        <DialogDescription>
                            This will permanently delete
                            <span class="font-medium text-[#161d19]">
                                {{
                                    expenseToDelete.description ||
                                    categoryName(expenseToDelete)
                                }}
                            </span>
                            ({{ formatMoney(expenseToDelete.amount) }}). This
                            action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button
                                type="button"
                                variant="secondary"
                                :disabled="processing"
                            >
                                Cancel
                            </Button>
                        </DialogClose>
                        <Button
                            type="submit"
                            variant="destructive"
                            :disabled="processing"
                            data-test="confirm-delete-expense-button"
                        >
                            {{ processing ? 'Deleting...' : 'Delete expense' }}
                        </Button>
                    </DialogFooter>
                </Form>
            </DialogContent>
        </Dialog>
    </div>
</template>
