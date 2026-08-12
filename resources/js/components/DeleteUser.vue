<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { useTemplateRef } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { useTranslations } from '@/composables/useTranslations';

const passwordInput = useTemplateRef('passwordInput');
const { t } = useTranslations();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <button
                type="button"
                class="flex min-h-12 w-full items-center justify-center gap-2 rounded-lg bg-[#ffdad6] px-4 text-sm font-semibold text-[#93000a] transition-colors hover:bg-[#ba1a1a] hover:text-white dark:bg-[#711419] dark:text-[#ffdad6] dark:hover:bg-[#a43a3a]"
                data-test="delete-user-button"
            >
                <Trash2 class="size-5" />
                {{ t('profile.delete_account') }}
            </button>
        </DialogTrigger>
        <DialogContent>
            <Form
                v-bind="ProfileController.destroy.form()"
                reset-on-success
                @error="() => passwordInput?.focus()"
                :options="{ preserveScroll: true }"
                class="space-y-6"
                v-slot="{ errors, processing, reset, clearErrors }"
            >
                <DialogHeader class="space-y-3">
                    <DialogTitle>{{
                        t('profile.delete_account_title')
                    }}</DialogTitle>
                    <DialogDescription>
                        {{ t('profile.delete_account_description') }}
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label for="delete-account-password" class="sr-only">
                        {{ t('profile.password') }}
                    </Label>
                    <PasswordInput
                        id="delete-account-password"
                        name="password"
                        ref="passwordInput"
                        :placeholder="t('profile.password')"
                        autocomplete="current-password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button
                            variant="secondary"
                            @click="
                                () => {
                                    clearErrors();
                                    reset();
                                }
                            "
                        >
                            {{ t('common.cancel') }}
                        </Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                        data-test="confirm-delete-user-button"
                    >
                        {{
                            processing
                                ? t('profile.deleting')
                                : t('profile.delete_account')
                        }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
