<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Languages, WalletCards } from '@lucide/vue';
import { computed } from 'vue';
import { home, login } from '@/routes';
import type { AuthCopy, AuthLocale } from '@/types';

const page = usePage<{ locale: AuthLocale; copy: AuthCopy }>();
const isArabic = computed(() => page.props.locale === 'ar');
const fontClass = computed(() =>
    isArabic.value ? "font-['Noto_Kufi_Arabic']" : "font-['Inter']",
);
</script>

<template>
    <div
        :dir="isArabic ? 'rtl' : 'ltr'"
        :class="fontClass"
        class="flex min-h-svh flex-col bg-[#f4fbf4] text-[#161d19] selection:bg-[#10b981] selection:text-[#00422b]"
    >
        <header
            class="relative flex w-full items-center justify-center bg-[#f4fbf4] px-5 py-4 md:px-10"
        >
            <Link
                :href="home({ locale: page.props.locale })"
                class="flex items-center gap-2 text-2xl font-bold text-[#006c49]"
                :aria-label="page.props.copy.common.home_aria"
            >
                <WalletCards class="size-7" aria-hidden="true" />
                <span>{{ page.props.copy.common.brand }}</span>
            </Link>

            <nav
                class="absolute end-5 flex items-center gap-1 rounded-full border border-[#bbcabf] bg-white p-1 text-xs shadow-sm md:end-10"
                :aria-label="page.props.copy.common.language_label"
            >
                <Languages
                    class="mx-1 size-4 text-[#006c49]"
                    aria-hidden="true"
                />
                <Link
                    :href="login({ query: { locale: 'en' } })"
                    class="rounded-full px-2.5 py-1 font-semibold transition"
                    :class="
                        page.props.locale === 'en'
                            ? 'bg-[#006c49] text-white'
                            : 'text-[#3c4a42] hover:bg-[#eef6ee]'
                    "
                    :aria-current="
                        page.props.locale === 'en' ? 'page' : undefined
                    "
                >
                    {{ page.props.copy.common.english }}
                </Link>
                <Link
                    :href="login({ query: { locale: 'ar' } })"
                    class="rounded-full px-2.5 py-1 font-semibold transition"
                    :class="
                        page.props.locale === 'ar'
                            ? 'bg-[#006c49] text-white'
                            : 'text-[#3c4a42] hover:bg-[#eef6ee]'
                    "
                    :aria-current="
                        page.props.locale === 'ar' ? 'page' : undefined
                    "
                >
                    {{ page.props.copy.common.arabic }}
                </Link>
            </nav>
        </header>

        <main
            class="flex flex-1 items-center justify-center px-5 py-8 md:px-10"
        >
            <slot />
        </main>

        <footer
            class="flex w-full items-center justify-center border-t border-[#bbcabf] bg-[#eef6ee] px-5 py-4 md:px-10"
        >
            <p class="text-xs font-semibold text-[#3c4a42]">
                {{ page.props.copy.common.copyright }}
            </p>
        </footer>
    </div>
</template>
