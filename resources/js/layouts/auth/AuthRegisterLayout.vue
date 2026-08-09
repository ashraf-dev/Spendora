<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Languages, WalletCards } from '@lucide/vue';
import { computed } from 'vue';
import { home, register } from '@/routes';
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
        <main
            class="relative flex flex-1 items-center justify-center overflow-hidden px-5 py-16 md:px-10"
        >
            <nav
                class="absolute end-5 top-4 z-20 flex items-center gap-1 rounded-full border border-[#bbcabf] bg-white p-1 text-xs shadow-sm md:end-10"
                :aria-label="page.props.copy.common.language_label"
            >
                <Languages
                    class="mx-1 size-4 text-[#006c49]"
                    aria-hidden="true"
                />
                <Link
                    :href="register({ query: { locale: 'en' } })"
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
                    :href="register({ query: { locale: 'ar' } })"
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

            <div
                class="absolute -start-32 -top-32 size-80 rounded-full bg-[#10b981]/8 blur-3xl"
                aria-hidden="true"
            />
            <div
                class="absolute -end-32 -bottom-36 size-96 rounded-full bg-[#006c49]/8 blur-3xl"
                aria-hidden="true"
            />
            <slot />
        </main>

        <footer
            class="grid w-full grid-cols-1 items-center gap-5 border-t border-[#bbcabf] bg-[#eef6ee] px-5 py-8 text-center md:grid-cols-3 md:px-10 md:text-start"
        >
            <Link
                :href="home({ locale: page.props.locale })"
                class="flex items-center justify-center gap-2 text-sm font-bold text-[#006c49] md:justify-start"
                :aria-label="page.props.copy.common.home_aria"
            >
                <WalletCards class="size-5" aria-hidden="true" />
                <span>{{ page.props.copy.common.brand }}</span>
            </Link>
            <nav
                class="flex flex-wrap justify-center gap-4 text-xs font-semibold text-[#3c4a42]"
                :aria-label="page.props.copy.common.legal_navigation"
            >
                <span>{{ page.props.copy.common.privacy }}</span>
                <span>{{ page.props.copy.common.terms }}</span>
                <span>{{ page.props.copy.common.help }}</span>
            </nav>
            <p class="text-center text-sm text-[#3c4a42] md:text-end">
                {{ page.props.copy.common.copyright }}
            </p>
        </footer>
    </div>
</template>
