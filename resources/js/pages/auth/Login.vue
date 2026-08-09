<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import AuthLoginLayout from '@/layouts/auth/AuthLoginLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import type { AuthCopy, AuthLocale } from '@/types';

defineOptions({ layout: AuthLoginLayout });

const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    locale: AuthLocale;
    copy: AuthCopy;
}>();
</script>

<template>
    <div
        class="relative flex w-full max-w-md flex-col overflow-hidden rounded-xl border border-[#bbcabf] bg-white p-8 shadow-[0_4px_6px_-1px_rgba(15,23,42,0.05),0_2px_4px_-2px_rgba(15,23,42,0.05)] md:p-10"
    >
        <Head :title="copy.login.meta_title">
            <meta
                head-key="description"
                name="description"
                :content="copy.login.meta_description"
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

        <div class="relative z-10 text-center">
            <h1
                class="text-2xl leading-8 font-semibold text-[#161d19] md:text-3xl md:leading-10"
            >
                {{ copy.login.title }}
            </h1>
            <p class="mt-2 text-base leading-6 text-[#3c4a42]">
                {{ copy.login.description }}
            </p>
        </div>

        <p
            v-if="status"
            class="relative z-10 mt-6 rounded-lg bg-[#eef6ee] px-4 py-3 text-center text-sm font-medium text-[#006c49]"
        >
            {{ status }}
        </p>

        <Form
            v-bind="store.form({ query: { locale: props.locale } })"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="relative z-10 mt-8 flex flex-col gap-4"
        >
            <div class="flex flex-col gap-1">
                <label
                    for="email"
                    class="text-sm leading-5 font-medium tracking-[0.01em] text-[#161d19]"
                >
                    {{ copy.login.email_label }}
                </label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    :placeholder="copy.login.email_placeholder"
                    :aria-invalid="Boolean(errors.email)"
                    class="h-12 rounded-lg border-[#bbcabf] bg-white px-4 text-base text-[#161d19] shadow-none placeholder:text-[#6c7a71] focus-visible:border-[#006c49] focus-visible:ring-1 focus-visible:ring-[#006c49]"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between gap-4">
                    <label
                        for="password"
                        class="text-sm leading-5 font-medium tracking-[0.01em] text-[#161d19]"
                    >
                        {{ copy.login.password_label }}
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="request({ query: { locale: props.locale } })"
                        class="text-sm font-medium text-[#006c49] transition-colors hover:text-[#10b981]"
                        :tabindex="5"
                    >
                        {{ copy.login.forgot_password }}
                    </Link>
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    :placeholder="copy.login.password_placeholder"
                    :show-password-label="copy.common.show_password"
                    :hide-password-label="copy.common.hide_password"
                    :aria-invalid="Boolean(errors.password)"
                    class="h-12 rounded-lg border-[#bbcabf] bg-white px-4 text-base text-[#161d19] shadow-none placeholder:text-[#6c7a71] focus-visible:border-[#006c49] focus-visible:ring-1 focus-visible:ring-[#006c49]"
                />
                <InputError :message="errors.password" />
            </div>

            <label
                for="remember"
                class="mt-2 flex cursor-pointer items-center gap-2 text-sm font-medium text-[#3c4a42]"
            >
                <Checkbox
                    id="remember"
                    name="remember"
                    value="1"
                    :tabindex="3"
                    class="border-[#bbcabf] focus-visible:ring-[#006c49]/30 data-[state=checked]:border-[#006c49] data-[state=checked]:bg-[#006c49]"
                />
                <span>{{ copy.login.remember }}</span>
            </label>

            <button
                type="submit"
                class="group mt-4 flex h-12 w-full items-center justify-center gap-2 rounded-lg bg-[#006c49] px-6 text-sm font-medium text-white shadow-sm transition-all duration-200 hover:bg-[#005b3e] active:scale-[0.98] disabled:pointer-events-none disabled:opacity-60"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                <template v-else>
                    <span>{{ copy.login.submit }}</span>
                    <ArrowRight
                        class="size-4 -translate-x-2 opacity-0 transition duration-200 group-hover:translate-x-0 group-hover:opacity-100"
                        :class="{ 'rotate-180': locale === 'ar' }"
                        aria-hidden="true"
                    />
                </template>
            </button>
        </Form>

        <p class="relative z-10 mt-8 text-center text-base text-[#3c4a42]">
            {{ copy.login.no_account }}
            <Link
                :href="register({ query: { locale: props.locale } })"
                class="font-medium text-[#006c49] transition-colors hover:text-[#10b981] hover:underline"
                :tabindex="6"
            >
                {{ copy.login.register }}
            </Link>
        </p>

        <img
            src="/images/spendora-login-decoration.png"
            alt=""
            class="pointer-events-none absolute -end-24 -bottom-24 size-64 rotate-12 rounded-full object-cover opacity-[0.03]"
            aria-hidden="true"
        />
    </div>
</template>
