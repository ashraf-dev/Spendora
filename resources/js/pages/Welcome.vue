<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ChartNoAxesCombined,
    CheckCircle2,
    Globe2,
    Menu,
    MonitorSmartphone,
    PlayCircle,
    ShieldCheck,
    WalletCards,
    X,
    Zap,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { dashboard, home, login, register } from '@/routes';

type CopyItem = { title: string; description: string };
interface WelcomeCopy {
    meta: { title: string; description: string };
    brand: string;
    language: Record<string, string>;
    navigation: Record<string, string>;
    hero: Record<string, string>;
    preview: Record<string, string>;
    benefits: { title: string; description: string; items: CopyItem[] };
    process: {
        eyebrow: string;
        title: string;
        image_alt: string;
        items: CopyItem[];
    };
    highlights: { title: string; title_accent: string; items: CopyItem[] };
    cta: Record<string, string>;
    footer: Record<string, string>;
}

const props = defineProps<{ locale: 'en' | 'ar'; copy: WelcomeCopy }>();
const page = usePage();
const isMobileMenuOpen = ref(false);
const isArabic = computed(() => props.locale === 'ar');
const fontClass = computed(() =>
    isArabic.value ? "font-['Noto_Kufi_Arabic']" : "font-['Inter']",
);
const primaryDestination = computed(() =>
    page.props.auth.user
        ? dashboard()
        : register({ query: { locale: props.locale } }),
);
const headerAction = computed(() =>
    page.props.auth.user
        ? props.copy.navigation.open_dashboard
        : props.copy.navigation.start_free,
);
const heroAction = computed(() =>
    page.props.auth.user
        ? props.copy.hero.authenticated_action
        : props.copy.hero.guest_action,
);
const ctaAction = computed(() =>
    page.props.auth.user
        ? props.copy.cta.authenticated_action
        : props.copy.cta.guest_action,
);
const benefitIcons = [Zap, ChartNoAxesCombined, MonitorSmartphone];
</script>

<template>
    <Head :title="copy.meta.title">
        <meta
            head-key="description"
            name="description"
            :content="copy.meta.description"
        />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin="anonymous"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Kufi+Arabic:wght@400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div
        id="top"
        :dir="isArabic ? 'rtl' : 'ltr'"
        :class="fontClass"
        class="min-h-screen bg-white text-[#161d19] selection:bg-emerald-500 selection:text-white"
    >
        <header
            class="sticky top-0 z-50 border-b border-emerald-950/10 bg-[#f4fbf4]/90 shadow-sm backdrop-blur-md"
        >
            <div class="mx-auto flex max-w-7xl justify-end px-5 pt-2 md:px-10">
                <div
                    class="inline-flex items-center gap-1 rounded-full border border-emerald-950/10 bg-white p-1 text-xs shadow-sm"
                    :aria-label="copy.language.label"
                >
                    <Globe2
                        class="mx-1 size-4 text-[#006c49]"
                        aria-hidden="true"
                    />
                    <Link
                        :href="home({ locale: 'en' })"
                        :aria-current="locale === 'en' ? 'page' : undefined"
                        class="rounded-full px-2.5 py-1 font-semibold transition"
                        :class="
                            locale === 'en'
                                ? 'bg-[#006c49] text-white'
                                : 'text-[#3c4a42] hover:bg-[#eef6ee]'
                        "
                        >{{ copy.language.english_short }}</Link
                    >
                    <span class="text-emerald-950/30" aria-hidden="true"
                        >/</span
                    >
                    <Link
                        :href="home({ locale: 'ar' })"
                        :aria-current="locale === 'ar' ? 'page' : undefined"
                        class="rounded-full px-2.5 py-1 font-semibold transition"
                        :class="
                            locale === 'ar'
                                ? 'bg-[#006c49] text-white'
                                : 'text-[#3c4a42] hover:bg-[#eef6ee]'
                        "
                        >{{ copy.language.arabic_short }}</Link
                    >
                </div>
            </div>

            <nav
                class="mx-auto flex h-16 max-w-7xl items-center justify-between px-5 md:px-10"
                :aria-label="copy.navigation.label"
            >
                <a
                    href="#top"
                    class="flex items-center gap-2 text-2xl font-bold text-[#006c49]"
                >
                    <WalletCards class="size-7" aria-hidden="true" />
                    <span>{{ copy.brand }}</span>
                </a>
                <div class="hidden items-center gap-8 md:flex">
                    <a
                        href="#features"
                        class="text-[#3c4a42] transition hover:text-[#006c49]"
                    >
                        {{ copy.navigation.features }}
                    </a>
                    <a
                        href="#how-it-works"
                        class="text-[#3c4a42] transition hover:text-[#006c49]"
                    >
                        {{ copy.navigation.how_it_works }}
                    </a>
                    <a
                        href="#get-started"
                        class="text-[#3c4a42] transition hover:text-[#006c49]"
                    >
                        {{ copy.navigation.get_started }}
                    </a>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        v-if="page.props.auth.user"
                        :href="dashboard()"
                        class="hidden text-[#3c4a42] hover:text-[#006c49] lg:block"
                        >{{ copy.navigation.dashboard }}</Link
                    >
                    <Link
                        v-else
                        :href="login({ query: { locale: props.locale } })"
                        class="hidden text-[#3c4a42] hover:text-[#006c49] lg:block"
                        >{{ copy.navigation.login }}</Link
                    >
                    <Link
                        :href="primaryDestination"
                        class="inline-flex h-11 items-center rounded-lg bg-[#006c49] px-4 text-sm font-semibold text-white shadow-md transition hover:brightness-110 sm:px-6"
                        >{{ headerAction }}</Link
                    >
                    <button
                        type="button"
                        class="inline-flex size-11 items-center justify-center rounded-lg text-[#3c4a42] md:hidden"
                        :aria-expanded="isMobileMenuOpen"
                        aria-controls="mobile-navigation"
                        :aria-label="copy.navigation.toggle"
                        @click="isMobileMenuOpen = !isMobileMenuOpen"
                    >
                        <X v-if="isMobileMenuOpen" class="size-6" />
                        <Menu v-else class="size-6" />
                    </button>
                </div>
            </nav>
            <div
                v-if="isMobileMenuOpen"
                id="mobile-navigation"
                class="border-t border-emerald-950/10 bg-[#f4fbf4] px-5 py-4 md:hidden"
            >
                <a href="#features" class="block rounded-lg px-3 py-3">{{
                    copy.navigation.features
                }}</a>
                <a href="#how-it-works" class="block rounded-lg px-3 py-3">{{
                    copy.navigation.how_it_works
                }}</a>
                <a href="#get-started" class="block rounded-lg px-3 py-3">{{
                    copy.navigation.get_started
                }}</a>
            </div>
        </header>

        <main>
            <section class="relative overflow-hidden py-20 lg:py-28">
                <div
                    class="absolute end-0 top-12 size-96 rounded-full bg-emerald-400/10 blur-3xl"
                />
                <div
                    class="relative mx-auto grid max-w-7xl items-center gap-16 px-5 md:px-10 lg:grid-cols-2"
                >
                    <div class="text-center lg:text-start">
                        <div
                            class="mb-5 inline-flex items-center gap-2 rounded-full bg-[#006c49]/5 px-4 py-2 text-xs font-semibold text-[#006c49]"
                        >
                            <ShieldCheck class="size-4" aria-hidden="true" />
                            {{ copy.hero.eyebrow }}
                        </div>
                        <h1
                            class="text-4xl leading-tight font-bold tracking-tight sm:text-5xl lg:text-6xl"
                        >
                            {{ copy.hero.title_before }}
                            <span class="text-[#006c49]">{{
                                copy.hero.title_accent
                            }}</span>
                        </h1>
                        <p
                            class="mx-auto mt-6 max-w-xl text-lg leading-8 text-[#3c4a42] lg:mx-0"
                        >
                            {{ copy.hero.description }}
                        </p>
                        <div
                            class="mt-10 flex flex-col justify-center gap-4 sm:flex-row lg:justify-start"
                        >
                            <Link
                                :href="primaryDestination"
                                class="inline-flex h-14 items-center justify-center rounded-xl bg-[#006c49] px-8 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5"
                                >{{ heroAction }}</Link
                            >
                            <a
                                href="#how-it-works"
                                class="inline-flex h-14 items-center justify-center gap-2 rounded-xl bg-[#e3eae3] px-8 text-sm font-semibold transition hover:bg-[#d4dcd5]"
                            >
                                <PlayCircle class="size-5" aria-hidden="true" />
                                {{ copy.hero.secondary_action }}
                            </a>
                        </div>
                        <p
                            class="mt-6 flex items-center justify-center gap-2 text-xs font-semibold text-[#3c4a42]/70 lg:justify-start"
                        >
                            <ShieldCheck
                                class="size-4 text-[#006c49]"
                                aria-hidden="true"
                            />
                            {{ copy.hero.note }}
                        </p>
                    </div>

                    <div
                        class="overflow-hidden rounded-3xl border border-emerald-950/10 bg-white p-6 shadow-2xl"
                    >
                        <div
                            class="flex items-center justify-between border-b border-emerald-950/10 pb-5"
                        >
                            <div class="flex gap-2" aria-hidden="true">
                                <span class="size-3 rounded-full bg-red-400" />
                                <span
                                    class="size-3 rounded-full bg-yellow-400"
                                />
                                <span
                                    class="size-3 rounded-full bg-green-400"
                                />
                            </div>
                            <strong class="text-sm text-[#3c4a42]">{{
                                copy.preview.title
                            }}</strong>
                        </div>
                        <div
                            class="mt-6 rounded-2xl bg-[#006c49]/5 p-6 text-center"
                        >
                            <p class="text-sm font-semibold text-[#3c4a42]">
                                {{ copy.preview.total_label }}
                            </p>
                            <p class="mt-2 text-4xl font-bold text-[#006c49]">
                                {{ copy.preview.total_value }}
                            </p>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-4">
                            <div class="rounded-2xl bg-[#eef6ee] p-5">
                                <p class="text-sm font-semibold">
                                    {{ copy.preview.food }}
                                </p>
                                <div
                                    class="mt-4 h-2 rounded-full bg-emerald-950/10"
                                >
                                    <div
                                        class="h-full w-2/3 rounded-full bg-[#006c49]"
                                    />
                                </div>
                                <p class="mt-2 text-end text-sm">
                                    {{ copy.preview.food_value }}
                                </p>
                            </div>
                            <div class="rounded-2xl bg-[#eef6ee] p-5">
                                <p class="text-sm font-semibold">
                                    {{ copy.preview.entertainment }}
                                </p>
                                <div
                                    class="mt-4 h-2 rounded-full bg-emerald-950/10"
                                >
                                    <div
                                        class="h-full w-1/3 rounded-full bg-[#006c49]"
                                    />
                                </div>
                                <p class="mt-2 text-end text-sm">
                                    {{ copy.preview.entertainment_value }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="scroll-mt-28 bg-[#eef6ee] py-24">
                <div class="mx-auto max-w-7xl px-5 md:px-10">
                    <div class="mb-14 text-center">
                        <h2 class="text-3xl font-semibold sm:text-4xl">
                            {{ copy.benefits.title }}
                        </h2>
                        <p class="mt-4 text-[#3c4a42]">
                            {{ copy.benefits.description }}
                        </p>
                    </div>
                    <div class="grid gap-8 md:grid-cols-3">
                        <article
                            v-for="(benefit, index) in copy.benefits.items"
                            :key="benefit.title"
                            class="rounded-2xl border border-emerald-950/10 bg-white p-8 shadow-sm transition hover:-translate-y-1 hover:shadow-md"
                        >
                            <div
                                class="mb-6 flex size-14 items-center justify-center rounded-xl bg-[#006c49]/10 text-[#006c49]"
                            >
                                <component
                                    :is="benefitIcons[index]"
                                    class="size-8"
                                    aria-hidden="true"
                                />
                            </div>
                            <h3 class="text-2xl font-semibold">
                                {{ benefit.title }}
                            </h3>
                            <p class="mt-4 leading-7 text-[#3c4a42]">
                                {{ benefit.description }}
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="how-it-works" class="scroll-mt-28 py-24">
                <div
                    class="mx-auto flex max-w-7xl flex-col items-center gap-16 px-5 md:px-10 lg:flex-row"
                >
                    <div class="w-full lg:w-1/2">
                        <div
                            class="mb-6 inline-flex items-center gap-2 rounded-full bg-[#006c49]/5 px-4 py-2 text-xs font-semibold text-[#006c49]"
                        >
                            <CheckCircle2 class="size-4" aria-hidden="true" />
                            {{ copy.process.eyebrow }}
                        </div>
                        <h2 class="text-3xl font-semibold sm:text-4xl">
                            {{ copy.process.title }}
                        </h2>
                        <div class="mt-10 flex flex-col gap-8">
                            <div
                                v-for="(step, index) in copy.process.items"
                                :key="step.title"
                                class="flex gap-5"
                            >
                                <span
                                    class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#006c49] text-lg font-semibold text-white"
                                >
                                    {{ index + 1 }}
                                </span>
                                <div>
                                    <h3 class="text-xl font-semibold">
                                        {{ step.title }}
                                    </h3>
                                    <p class="mt-2 leading-7 text-[#3c4a42]">
                                        {{ step.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <img
                        src="/images/spendora-workflow.png"
                        :alt="copy.process.image_alt"
                        class="w-full rounded-3xl border border-emerald-950/10 shadow-2xl lg:w-1/2"
                    />
                </div>
            </section>

            <section class="bg-[#2b322d] py-24 text-white">
                <div
                    class="mx-auto grid max-w-7xl gap-14 px-5 md:px-10 lg:grid-cols-2"
                >
                    <h2
                        class="text-3xl leading-tight font-semibold sm:text-4xl"
                    >
                        {{ copy.highlights.title }}<br />
                        <span class="text-[#4edea3]">{{
                            copy.highlights.title_accent
                        }}</span>
                    </h2>
                    <ul class="flex flex-col gap-6">
                        <li
                            v-for="highlight in copy.highlights.items"
                            :key="highlight.title"
                            class="flex gap-4"
                        >
                            <CheckCircle2
                                class="mt-1 size-6 shrink-0 text-[#4edea3]"
                                aria-hidden="true"
                            />
                            <div>
                                <strong>{{ highlight.title }}</strong>
                                <p class="mt-1 leading-7 text-[#bbcabf]">
                                    {{ highlight.description }}
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </section>

            <section id="get-started" class="scroll-mt-28 py-24 text-center">
                <div class="mx-auto max-w-4xl px-5">
                    <h2 class="text-4xl font-bold sm:text-5xl">
                        {{ copy.cta.title }}
                    </h2>
                    <p
                        class="mx-auto mt-7 max-w-3xl text-lg leading-8 text-[#3c4a42]"
                    >
                        {{ copy.cta.description }}
                    </p>
                    <Link
                        :href="primaryDestination"
                        class="mt-10 inline-flex min-h-16 items-center justify-center rounded-2xl bg-[#006c49] px-10 text-xl font-semibold text-white shadow-xl shadow-emerald-900/20 transition hover:-translate-y-1"
                        >{{ ctaAction }}</Link
                    >
                </div>
            </section>
        </main>

        <footer class="border-t border-emerald-950/10 bg-[#f4fbf4]">
            <div
                class="mx-auto flex max-w-7xl flex-col justify-between gap-8 px-5 py-10 md:flex-row md:items-center md:px-10"
            >
                <div>
                    <a
                        href="#top"
                        class="flex items-center gap-2 text-xl font-bold text-[#006c49]"
                    >
                        <WalletCards class="size-6" aria-hidden="true" />
                        <span>{{ copy.brand }}</span>
                    </a>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-[#3c4a42]">
                        {{ copy.footer.tagline }}
                    </p>
                </div>
                <div
                    class="flex flex-col gap-3 text-sm text-[#3c4a42] md:items-end"
                >
                    <span>{{ copy.footer.copyright }}</span>
                    <div class="flex gap-6">
                        <span>{{ copy.footer.privacy }}</span>
                        <span>{{ copy.footer.terms }}</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
