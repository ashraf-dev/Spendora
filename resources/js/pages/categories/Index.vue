<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Bell,
    Car,
    Clapperboard,
    GraduationCap,
    HeartPulse,
    Plane,
    ReceiptText,
    Search,
    Shapes,
    ShoppingBag,
    SlidersHorizontal,
    Store,
    Utensils,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import EmptyState from '@/components/spendora/EmptyState.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useInitials } from '@/composables/useInitials';
import { useTranslations } from '@/composables/useTranslations';
import { formatMoney, formatPercent, monthLabel } from '@/lib/format';
import {
    index as categoriesIndex,
    show as categoryShow,
} from '@/routes/categories';
import type { CategoryTotalRow, MonthNavigation } from '@/types/spendora';

defineOptions({
    layout: {
        hideHeader: true,
    },
});

const props = defineProps<{
    month: number;
    year: number;
    month_total: string;
    categories: CategoryTotalRow[];
    navigation: MonthNavigation;
}>();

const page = usePage();
const { getInitials } = useInitials();
const { locale, t } = useTranslations();
const showSpentCategoriesOnly = ref(false);
const selectedCategoryId = ref(
    props.categories.find((row) => Number(row.total_amount) > 0)?.category.id ??
        props.categories[0]?.category.id ??
        null,
);

const user = computed(() => page.props.auth.user);
const visibleCategories = computed(() =>
    showSpentCategoriesOnly.value
        ? props.categories.filter((row) => Number(row.total_amount) > 0)
        : props.categories,
);
const selectedCategory = computed(() =>
    props.categories.find(
        (row) => row.category.id === selectedCategoryId.value,
    ),
);

const categoryIcons = {
    food: Utensils,
    transportation: Car,
    shopping: ShoppingBag,
    bills: ReceiptText,
    health: HeartPulse,
    education: GraduationCap,
    entertainment: Clapperboard,
    travel: Plane,
    other: Shapes,
};

const categoryStyles = [
    {
        icon: 'bg-[#10b981] text-[#00422b]',
        accent: 'text-[#006c49]',
        progress: 'bg-[#006c49]',
    },
    {
        icon: 'bg-[#dae2fd] text-[#3f465c]',
        accent: 'text-[#565e74]',
        progress: 'bg-[#565e74]',
    },
    {
        icon: 'bg-[#fc7c78]/45 text-[#842225]',
        accent: 'text-[#a43a3a]',
        progress: 'bg-[#a43a3a]',
    },
    {
        icon: 'bg-[#dde4dd] text-[#3c4a42]',
        accent: 'text-[#3c4a42]',
        progress: 'bg-[#3c4a42]',
    },
];

function iconFor(category: CategoryTotalRow) {
    const icon = category.category.icon?.toLowerCase() ?? 'other';

    return categoryIcons[icon as keyof typeof categoryIcons] ?? Shapes;
}

function styleFor(index: number) {
    return categoryStyles[index % categoryStyles.length];
}

function formatExpenseDate(date: string): string {
    return new Intl.DateTimeFormat(locale.value, {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(new Date(`${date}T00:00:00`));
}
</script>

<template>
    <Head :title="t('categories.title')" />

    <div class="min-h-full flex-1 bg-[#f4fbf4] font-sans text-[#161d19]">
        <header
            class="sticky top-0 z-30 flex min-h-20 items-center justify-between gap-4 border-b border-[#bbcabf] bg-[#f4fbf4]/95 px-5 py-3 backdrop-blur md:px-10"
        >
            <div class="flex min-w-0 items-center gap-3 md:gap-4">
                <SidebarTrigger class="md:hidden" />
                <div
                    class="flex items-center rounded-full border border-[#bbcabf] bg-[#e8f0e9] p-1"
                >
                    <Link
                        :href="
                            categoriesIndex({
                                query: navigation.previous_month,
                            })
                        "
                        class="flex size-8 items-center justify-center rounded-full text-xl text-[#161d19] transition-colors hover:bg-[#dde4dd]"
                        preserve-scroll
                        :aria-label="t('common.previous_month')"
                    >
                        ‹
                    </Link>
                    <span
                        class="min-w-28 px-3 text-center text-sm font-medium whitespace-nowrap"
                    >
                        {{ monthLabel(month, year) }}
                    </span>
                    <Link
                        :href="
                            categoriesIndex({ query: navigation.next_month })
                        "
                        class="flex size-8 items-center justify-center rounded-full text-xl text-[#161d19] transition-colors hover:bg-[#dde4dd]"
                        preserve-scroll
                        :aria-label="t('common.next_month')"
                    >
                        ›
                    </Link>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-3 md:gap-6">
                <div class="hidden text-end sm:block">
                    <p class="text-xs font-semibold text-[#565e74]">
                        {{ t('categories.total_spent') }}
                    </p>
                    <p class="text-lg font-bold md:text-2xl">
                        {{ formatMoney(month_total) }}
                    </p>
                </div>
                <div class="hidden h-10 w-px bg-[#bbcabf] sm:block" />
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="hidden size-10 items-center justify-center rounded-full bg-[#e8f0e9] transition-colors hover:bg-[#dde4dd] sm:flex"
                        :aria-label="t('common.search')"
                    >
                        <Search class="size-5" />
                    </button>
                    <button
                        type="button"
                        class="relative flex size-10 items-center justify-center rounded-full bg-[#e8f0e9] transition-colors hover:bg-[#dde4dd]"
                        :aria-label="t('common.notifications')"
                    >
                        <Bell class="size-5" />
                        <span
                            class="absolute end-2 top-2 size-2 rounded-full bg-[#ba1a1a]"
                        />
                    </button>
                    <Avatar class="size-10 border border-[#bbcabf]">
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
            </div>
        </header>

        <main
            class="mx-auto grid w-full max-w-7xl grid-cols-1 gap-6 px-5 py-8 md:px-10 lg:grid-cols-12"
        >
            <section class="flex flex-col gap-4 lg:col-span-7 xl:col-span-8">
                <div class="flex items-center justify-between gap-4 pb-2">
                    <div>
                        <h1
                            class="text-2xl font-semibold tracking-tight md:text-3xl"
                        >
                            {{ t('categories.title') }}
                        </h1>
                        <p class="mt-1 text-sm text-[#565e74] sm:hidden">
                            {{
                                t('categories.spent_this_month', {
                                    amount: formatMoney(month_total),
                                })
                            }}
                        </p>
                    </div>
                    <button
                        type="button"
                        :aria-pressed="showSpentCategoriesOnly"
                        class="flex items-center gap-1.5 text-sm font-medium text-[#006c49] underline-offset-4 hover:underline"
                        @click="
                            showSpentCategoriesOnly = !showSpentCategoriesOnly
                        "
                    >
                        <SlidersHorizontal class="size-4" />
                        {{
                            showSpentCategoriesOnly
                                ? t('categories.show_all')
                                : t('categories.filter')
                        }}
                    </button>
                </div>

                <EmptyState
                    v-if="visibleCategories.length === 0"
                    :title="t('categories.empty')"
                    :description="t('categories.empty_description')"
                />

                <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <button
                        v-for="(row, index) in visibleCategories"
                        :key="row.category.id"
                        type="button"
                        :aria-pressed="selectedCategoryId === row.category.id"
                        :class="[
                            'group relative overflow-hidden rounded-xl bg-white p-4 text-start shadow-[0_4px_6px_-1px_rgba(15,23,42,0.05),0_2px_4px_-2px_rgba(15,23,42,0.05)] transition-all duration-200 hover:-translate-y-1 hover:border-[#006c49]',
                            selectedCategoryId === row.category.id
                                ? 'border-2 border-[#006c49]'
                                : 'border border-[#bbcabf]',
                        ]"
                        @click="selectedCategoryId = row.category.id"
                    >
                        <span
                            v-if="selectedCategoryId === row.category.id"
                            class="absolute -end-4 -top-4 size-24 rounded-bl-full bg-[#10b981]/20 rtl:rounded-br-full rtl:rounded-bl-none"
                        />
                        <span
                            class="relative z-10 flex items-start justify-between gap-3"
                        >
                            <span class="flex min-w-0 items-center gap-3">
                                <span
                                    :class="[
                                        'flex size-10 shrink-0 items-center justify-center rounded-full',
                                        styleFor(index).icon,
                                    ]"
                                >
                                    <component
                                        :is="iconFor(row)"
                                        class="size-5"
                                    />
                                </span>
                                <span class="min-w-0">
                                    <span
                                        class="block truncate text-sm font-bold"
                                    >
                                        {{ row.category.name }}
                                    </span>
                                    <span
                                        class="block text-xs font-semibold text-[#6c7a71]"
                                    >
                                        {{ row.expense_count }}
                                        {{
                                            row.expense_count === 1
                                                ? t('categories.transaction')
                                                : t('categories.transactions')
                                        }}
                                    </span>
                                </span>
                            </span>
                            <span
                                :class="[
                                    'shrink-0 text-sm font-bold',
                                    styleFor(index).accent,
                                ]"
                            >
                                {{
                                    formatPercent(row.percentage ?? 0).replace(
                                        '+',
                                        '',
                                    )
                                }}
                            </span>
                        </span>

                        <span class="relative z-10 mt-8 block">
                            <span
                                class="mb-2 flex items-end justify-between gap-3"
                            >
                                <span class="text-xl font-bold md:text-2xl">
                                    {{ formatMoney(row.total_amount) }}
                                </span>
                                <span
                                    class="text-xs font-semibold text-[#6c7a71]"
                                >
                                    {{
                                        t('categories.of_total', {
                                            amount: formatMoney(month_total),
                                        })
                                    }}
                                </span>
                            </span>
                            <span
                                class="block h-2 overflow-hidden rounded-full bg-[#e8f0e9]"
                            >
                                <span
                                    :class="[
                                        'block h-full rounded-full transition-[width] duration-500',
                                        styleFor(index).progress,
                                    ]"
                                    :style="{
                                        width: `${Math.min(
                                            row.percentage ?? 0,
                                            100,
                                        )}%`,
                                    }"
                                />
                            </span>
                        </span>
                    </button>
                </div>
            </section>

            <aside class="lg:col-span-5 xl:col-span-4">
                <div
                    v-if="selectedCategory"
                    class="overflow-hidden rounded-xl border border-[#bbcabf] bg-white shadow-[0_4px_6px_-1px_rgba(15,23,42,0.05),0_2px_4px_-2px_rgba(15,23,42,0.05)] lg:sticky lg:top-28"
                >
                    <div class="border-b border-[#bbcabf] bg-white p-4">
                        <div class="mb-2 flex items-center gap-3">
                            <span
                                class="flex size-8 items-center justify-center rounded-full bg-[#10b981] text-[#00422b]"
                            >
                                <component
                                    :is="iconFor(selectedCategory)"
                                    class="size-4"
                                />
                            </span>
                            <h2 class="truncate text-xl font-bold">
                                {{ selectedCategory.category.name }}
                            </h2>
                        </div>
                        <div class="flex items-end justify-between gap-3">
                            <span class="text-xs font-semibold text-[#6c7a71]">
                                {{ monthLabel(month, year) }}
                            </span>
                            <span class="text-sm font-bold text-[#006c49]">
                                -{{
                                    formatMoney(selectedCategory.total_amount)
                                }}
                            </span>
                        </div>
                    </div>

                    <EmptyState
                        v-if="!selectedCategory.recent_expenses?.length"
                        class="m-4"
                        :title="t('categories.no_transactions')"
                        :description="
                            t('categories.no_transactions_description')
                        "
                    />

                    <ul v-else class="flex flex-col px-4">
                        <li
                            v-for="expense in selectedCategory.recent_expenses"
                            :key="expense.id"
                            class="group flex items-center justify-between gap-3 border-b border-[#dde4dd] px-2 py-3 last:border-0 hover:bg-[#f4fbf4]"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <span
                                    class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-[#e8f0e9] text-[#3c4a42] transition-colors group-hover:bg-[#10b981] group-hover:text-[#00422b]"
                                >
                                    <Store class="size-5" />
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold">
                                        {{
                                            expense.description ||
                                            selectedCategory.category.name
                                        }}
                                    </p>
                                    <p
                                        class="text-xs font-semibold text-[#6c7a71]"
                                    >
                                        {{
                                            formatExpenseDate(
                                                expense.expense_date,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                            <span class="shrink-0 text-sm font-bold">
                                -{{ formatMoney(expense.amount) }}
                            </span>
                        </li>
                    </ul>

                    <div class="border-t border-[#bbcabf] p-4">
                        <Link
                            :href="
                                categoryShow(selectedCategory.category, {
                                    query: { month, year },
                                })
                            "
                            class="flex min-h-12 w-full items-center justify-center rounded-lg bg-[#e8f0e9] px-4 py-3 text-sm font-medium transition-colors hover:bg-[#dde4dd]"
                        >
                            {{ t('categories.view_transactions') }}
                        </Link>
                    </div>
                </div>
            </aside>
        </main>
    </div>
</template>
