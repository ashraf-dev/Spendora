<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowRight, WalletCards } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import AuthRegisterLayout from '@/layouts/auth/AuthRegisterLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import type { AuthCopy, AuthLocale } from '@/types';

defineOptions({ layout: AuthRegisterLayout });

const props = defineProps<{
    passwordRules: string;
    locale: AuthLocale;
    copy: AuthCopy;
}>();
</script>

<template>
    <div
        class="relative z-10 flex w-full max-w-md flex-col overflow-hidden rounded-xl border border-[#bbcabf] bg-white p-8 shadow-[0_4px_6px_-1px_rgba(15,23,42,0.05),0_2px_4px_-2px_rgba(15,23,42,0.05)]"
    >
        <Head :title="copy.register.meta_title">
            <meta
                head-key="description"
                name="description"
                :content="copy.register.meta_description"
            />
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link
                rel="preconnect"
                href="https://fonts.gstatic.com"
                crossorigin="anonymous"
            />
            <link
                href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap"
                rel="stylesheet"
            />
        </Head>

        <div class="relative z-10 mb-8 text-center">
            <Link
                :href="login({ query: { locale: props.locale } })"
                class="mb-4 inline-flex items-center gap-2 text-2xl font-bold text-[#006c49]"
            >
                <WalletCards class="size-7" aria-hidden="true" />
                <span>{{ copy.common.brand }}</span>
            </Link>
            <h1
                class="text-2xl leading-8 font-semibold text-[#161d19] md:text-3xl md:leading-10"
            >
                {{ copy.register.title }}
            </h1>
            <p class="mt-2 text-base leading-6 text-[#3c4a42]">
                {{ copy.register.description }}
            </p>
        </div>

        <Form
            v-bind="store.form({ query: { locale: props.locale } })"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="relative z-10 flex flex-col gap-4"
        >
            <div class="flex flex-col gap-1">
                <label
                    for="name"
                    class="text-sm leading-5 font-medium tracking-[0.01em] text-[#161d19]"
                >
                    {{ copy.register.name_label }}
                </label>
                <Input
                    id="name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="name"
                    name="name"
                    :placeholder="copy.register.name_placeholder"
                    :aria-invalid="Boolean(errors.name)"
                    class="h-12 rounded-lg border-[#bbcabf] bg-white px-4 text-base text-[#161d19] shadow-none placeholder:text-[#6c7a71] focus-visible:border-[#006c49] focus-visible:ring-1 focus-visible:ring-[#006c49]"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="flex flex-col gap-1">
                <label
                    for="email"
                    class="text-sm leading-5 font-medium tracking-[0.01em] text-[#161d19]"
                >
                    {{ copy.register.email_label }}
                </label>
                <Input
                    id="email"
                    type="email"
                    required
                    :tabindex="2"
                    autocomplete="email"
                    name="email"
                    :placeholder="copy.register.email_placeholder"
                    :aria-invalid="Boolean(errors.email)"
                    class="h-12 rounded-lg border-[#bbcabf] bg-white px-4 text-base text-[#161d19] shadow-none placeholder:text-[#6c7a71] focus-visible:border-[#006c49] focus-visible:ring-1 focus-visible:ring-[#006c49]"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="flex flex-col gap-1">
                <label
                    for="password"
                    class="text-sm leading-5 font-medium tracking-[0.01em] text-[#161d19]"
                >
                    {{ copy.register.password_label }}
                </label>
                <PasswordInput
                    id="password"
                    required
                    :tabindex="3"
                    autocomplete="new-password"
                    name="password"
                    :placeholder="copy.register.password_placeholder"
                    :passwordrules="passwordRules"
                    :show-password-label="copy.common.show_password"
                    :hide-password-label="copy.common.hide_password"
                    :aria-invalid="Boolean(errors.password)"
                    class="h-12 rounded-lg border-[#bbcabf] bg-white px-4 text-base text-[#161d19] shadow-none placeholder:text-[#6c7a71] focus-visible:border-[#006c49] focus-visible:ring-1 focus-visible:ring-[#006c49]"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex flex-col gap-1">
                <label
                    for="password_confirmation"
                    class="text-sm leading-5 font-medium tracking-[0.01em] text-[#161d19]"
                >
                    {{ copy.register.password_confirmation_label }}
                </label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    name="password_confirmation"
                    :placeholder="
                        copy.register.password_confirmation_placeholder
                    "
                    :passwordrules="passwordRules"
                    :show-password-label="copy.common.show_password"
                    :hide-password-label="copy.common.hide_password"
                    :aria-invalid="Boolean(errors.password_confirmation)"
                    class="h-12 rounded-lg border-[#bbcabf] bg-white px-4 text-base text-[#161d19] shadow-none placeholder:text-[#6c7a71] focus-visible:border-[#006c49] focus-visible:ring-1 focus-visible:ring-[#006c49]"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <button
                type="submit"
                class="group mt-4 flex h-12 w-full items-center justify-center gap-2 rounded-lg bg-[#006c49] px-6 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-[#005b3e] active:scale-[0.98] disabled:pointer-events-none disabled:opacity-60"
                tabindex="5"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                <template v-else>
                    <span>{{ copy.register.submit }}</span>
                    <ArrowRight
                        class="size-4 -translate-x-2 opacity-0 transition duration-200 group-hover:translate-x-0 group-hover:opacity-100"
                        :class="{ 'rotate-180': locale === 'ar' }"
                        aria-hidden="true"
                    />
                </template>
            </button>
        </Form>

        <p class="relative z-10 mt-8 text-center text-base text-[#3c4a42]">
            {{ copy.register.has_account }}
            <Link
                :href="login({ query: { locale: props.locale } })"
                class="font-medium text-[#006c49] transition-colors hover:text-[#10b981] hover:underline"
                :tabindex="6"
            >
                {{ copy.register.login }}
            </Link>
        </p>

        <img
            src="/images/spendora-register-decoration.png"
            alt=""
            class="pointer-events-none absolute -end-24 -bottom-24 size-64 rotate-12 rounded-full object-cover opacity-[0.03]"
            aria-hidden="true"
        />
    </div>
</template>
