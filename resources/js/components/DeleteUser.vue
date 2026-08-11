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

const passwordInput = useTemplateRef('passwordInput');
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
                Delete Account
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
                    <DialogTitle>Delete your account?</DialogTitle>
                    <DialogDescription>
                        This permanently deletes your account and all of its
                        data. Enter your password to confirm.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label for="delete-account-password" class="sr-only">
                        Password
                    </Label>
                    <PasswordInput
                        id="delete-account-password"
                        name="password"
                        ref="passwordInput"
                        placeholder="Password"
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
                            Cancel
                        </Button>
                    </DialogClose>
                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="processing"
                        data-test="confirm-delete-user-button"
                    >
                        {{ processing ? 'Deleting...' : 'Delete account' }}
                    </Button>
                </DialogFooter>
            </Form>
        </DialogContent>
    </Dialog>
</template>
