<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { useTranslations } from '@/composables/useTranslations';
import { index, store, update } from '@/routes/expenses';
import type { CategoryPayload, ExpensePayload } from '@/types/spendora';

const props = defineProps<{
    expense: ExpensePayload | null;
    categories: CategoryPayload[];
}>();

const isEdit = Boolean(props.expense);
const { t } = useTranslations();
</script>

<template>
    <Head
        :title="isEdit ? t('expenses.edit_title') : t('expenses.add_title')"
    />

    <div class="mx-auto flex w-full max-w-xl flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <Link
                :href="index()"
                class="text-sm font-medium text-[#006c49] hover:underline"
            >
                {{ t('expenses.back') }}
            </Link>
            <h1 class="mt-2 text-2xl font-semibold text-[#161d19]">
                {{
                    isEdit ? t('expenses.edit_title') : t('expenses.add_title')
                }}
            </h1>
            <p class="mt-1 text-sm text-[#3c4a42]">
                {{
                    isEdit
                        ? t('expenses.edit_description')
                        : t('expenses.add_description')
                }}
            </p>
        </div>

        <Form
            v-bind="isEdit ? update.form(expense!) : store.form()"
            class="space-y-4 rounded-xl border border-[#bbcabf] bg-white p-5 md:p-6"
            v-slot="{ errors, processing }"
        >
            <div class="flex flex-col gap-1">
                <label
                    for="category_id"
                    class="text-sm font-medium text-[#161d19]"
                    >{{ t('common.category') }}</label
                >
                <select
                    id="category_id"
                    name="category_id"
                    required
                    class="flex h-12 w-full rounded-lg border border-[#bbcabf] bg-white px-3 text-base text-[#161d19]"
                    :aria-invalid="Boolean(errors.category_id)"
                >
                    <option value="" disabled :selected="!expense">
                        {{ t('expenses.select_category') }}
                    </option>
                    <option
                        v-for="category in categories"
                        :key="category.id"
                        :value="category.id"
                        :selected="expense?.category_id === category.id"
                    >
                        {{ category.name }}
                    </option>
                </select>
                <InputError :message="errors.category_id" />
            </div>

            <div class="flex flex-col gap-1">
                <label
                    for="expense_date"
                    class="text-sm font-medium text-[#161d19]"
                    >{{ t('common.date') }}</label
                >
                <Input
                    id="expense_date"
                    type="date"
                    name="expense_date"
                    required
                    :default-value="
                        expense?.expense_date ??
                        new Date().toISOString().slice(0, 10)
                    "
                    class="h-12 border-[#bbcabf]"
                    :aria-invalid="Boolean(errors.expense_date)"
                />
                <InputError :message="errors.expense_date" />
            </div>

            <div class="flex flex-col gap-1">
                <label
                    for="description"
                    class="text-sm font-medium text-[#161d19]"
                    >{{ t('common.description') }}</label
                >
                <Input
                    id="description"
                    type="text"
                    name="description"
                    :default-value="expense?.description ?? ''"
                    :placeholder="t('expenses.description_placeholder')"
                    class="h-12 border-[#bbcabf]"
                    :aria-invalid="Boolean(errors.description)"
                />
                <InputError :message="errors.description" />
            </div>

            <div class="flex flex-col gap-1">
                <label
                    for="amount"
                    class="text-sm font-medium text-[#161d19]"
                    >{{ t('common.amount') }}</label
                >
                <Input
                    id="amount"
                    type="number"
                    name="amount"
                    required
                    step="0.01"
                    min="0.01"
                    :default-value="expense?.amount ?? ''"
                    placeholder="25.50"
                    class="h-12 border-[#bbcabf]"
                    :aria-invalid="Boolean(errors.amount)"
                />
                <InputError :message="errors.amount" />
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#006c49] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#00422b] disabled:opacity-60"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" />
                    {{
                        processing
                            ? isEdit
                                ? t('expenses.updating')
                                : t('expenses.adding')
                            : isEdit
                              ? t('expenses.update')
                              : t('common.add_expense')
                    }}
                </button>
                <Link
                    :href="index()"
                    class="text-sm font-medium text-[#3c4a42] hover:text-[#161d19]"
                >
                    {{ t('common.cancel') }}
                </Link>
            </div>
        </Form>
    </div>
</template>
