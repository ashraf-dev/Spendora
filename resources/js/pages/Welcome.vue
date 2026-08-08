<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ChartNoAxesCombined,
    CheckCircle2,
    ChevronDown,
    Coffee,
    Globe2,
    Mail,
    Menu,
    MonitorSmartphone,
    PlayCircle,
    Share2,
    ShieldCheck,
    ShoppingCart,
    WalletCards,
    X,
    Zap,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { dashboard, login, register } from '@/routes';

const page = usePage();
const isMobileMenuOpen = ref(false);

const primaryDestination = computed(() =>
    page.props.auth.user ? dashboard() : register(),
);

const benefits = [
    {
        icon: Zap,
        title: 'Track expenses quickly',
        description:
            'Add new transactions in seconds. Our minimal interface ensures logging spending never feels like a chore.',
    },
    {
        icon: ChartNoAxesCombined,
        title: 'Understand spending',
        description:
            'Get instant breakdowns by category. Visualize your habits with intuitive charts that highlight where you can save.',
    },
    {
        icon: MonitorSmartphone,
        title: 'Stay organized anywhere',
        description:
            'Access your dashboard from any device. Your data is synced securely across phone, tablet, and desktop.',
    },
];

const steps = [
    {
        title: 'Add an expense',
        description:
            'Tap the button, enter the amount, and description. It takes less than 10 seconds.',
    },
    {
        title: 'Assign a category',
        description:
            'Select from presets like “Groceries”, “Rent”, or “Fun”. Or create your own custom categories.',
    },
    {
        title: 'See your spending clearly',
        description:
            'Watch your dashboard update in real-time, giving you total control over your monthly budget.',
    },
];

const highlights = [
    {
        title: 'Full Transaction Control',
        description:
            'Add, edit, or delete transactions with ease. Nothing is permanent until you say so.',
    },
    {
        title: 'Advanced Filtering',
        description:
            'Filter your spending by specific dates, custom categories, or keywords in seconds.',
    },
    {
        title: 'Multi-language Support',
        description:
            'Switch seamlessly between English and Arabic. Right-to-left layout is fully supported.',
    },
];
</script>

<template>
    <Head title="Know where your money goes">
        <meta
            head-key="description"
            name="description"
            content="Spendora makes expense tracking simple, clear, and effortless."
        />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin="anonymous"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Geist:wght@500;600;700&family=Inter:wght@400;500;600&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div
        class="min-h-screen bg-white font-['Inter'] text-[#161d19] selection:bg-[#10b981] selection:text-white"
    >
        <header
            class="sticky top-0 z-50 border-b border-[#bbcabf]/30 bg-[#f4fbf4]/85 shadow-sm backdrop-blur-md"
        >
            <nav
                class="mx-auto flex h-20 w-full max-w-[1280px] items-center justify-between px-5 md:px-10"
                aria-label="Main navigation"
            >
                <a
                    href="#top"
                    class="flex items-center gap-2 font-['Geist'] text-2xl font-bold text-[#006c49]"
                >
                    <WalletCards class="size-7" aria-hidden="true" />
                    <span>Spendora</span>
                </a>

                <div class="hidden items-center gap-8 md:flex">
                    <a
                        href="#features"
                        class="text-[#3c4a42] transition-colors duration-200 hover:text-[#006c49]"
                        >Features</a
                    >
                    <a
                        href="#how-it-works"
                        class="text-[#3c4a42] transition-colors duration-200 hover:text-[#006c49]"
                        >How It Works</a
                    >
                    <a
                        href="#get-started"
                        class="text-[#3c4a42] transition-colors duration-200 hover:text-[#006c49]"
                        >Get Started</a
                    >
                </div>

                <div class="flex items-center gap-2 sm:gap-4">
                    <button
                        type="button"
                        class="hidden items-center gap-1 rounded-lg px-2 py-1 text-[#3c4a42] transition-colors hover:text-[#006c49] sm:flex"
                        aria-label="Current language: English"
                    >
                        <Globe2 class="size-5" aria-hidden="true" />
                        <span class="font-['Geist'] text-sm font-medium"
                            >EN</span
                        >
                    </button>

                    <Link
                        v-if="page.props.auth.user"
                        :href="dashboard()"
                        class="hidden px-2 text-[#3c4a42] transition-colors hover:text-[#006c49] lg:block"
                        >Dashboard</Link
                    >
                    <Link
                        v-else
                        :href="login()"
                        class="hidden px-2 text-[#3c4a42] transition-colors hover:text-[#006c49] lg:block"
                        >Log In</Link
                    >

                    <Link
                        :href="primaryDestination"
                        class="inline-flex h-12 items-center rounded-lg bg-[#006c49] px-4 font-['Geist'] text-sm font-medium text-white shadow-md transition hover:brightness-110 active:scale-95 sm:px-6"
                    >
                        {{
                            page.props.auth.user
                                ? 'Open Dashboard'
                                : 'Start for Free'
                        }}
                    </Link>

                    <button
                        type="button"
                        class="inline-flex size-11 items-center justify-center rounded-lg text-[#3c4a42] md:hidden"
                        :aria-expanded="isMobileMenuOpen"
                        aria-controls="mobile-navigation"
                        aria-label="Toggle navigation"
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
                class="border-t border-[#bbcabf]/30 bg-[#f4fbf4] px-5 py-5 md:hidden"
            >
                <div class="mx-auto flex max-w-[1280px] flex-col gap-1">
                    <a
                        href="#features"
                        class="rounded-lg px-3 py-3 font-medium text-[#3c4a42] hover:bg-white"
                        @click="isMobileMenuOpen = false"
                        >Features</a
                    >
                    <a
                        href="#how-it-works"
                        class="rounded-lg px-3 py-3 font-medium text-[#3c4a42] hover:bg-white"
                        @click="isMobileMenuOpen = false"
                        >How It Works</a
                    >
                    <a
                        href="#get-started"
                        class="rounded-lg px-3 py-3 font-medium text-[#3c4a42] hover:bg-white"
                        @click="isMobileMenuOpen = false"
                        >Get Started</a
                    >
                    <Link
                        v-if="!page.props.auth.user"
                        :href="login()"
                        class="rounded-lg px-3 py-3 font-medium text-[#3c4a42] hover:bg-white"
                        >Log In</Link
                    >
                </div>
            </div>
        </header>

        <main id="top">
            <section class="relative overflow-hidden py-20 lg:pb-32">
                <div
                    class="absolute top-12 right-0 size-96 rounded-full bg-[#10b981]/8 blur-3xl"
                />
                <div
                    class="relative z-10 mx-auto grid w-full max-w-[1280px] grid-cols-1 items-center gap-16 px-5 md:px-10 lg:grid-cols-2"
                >
                    <div class="text-center lg:text-left">
                        <div
                            class="mb-5 inline-flex items-center gap-2 rounded-full bg-[#006c49]/5 px-4 py-2 font-['Geist'] text-xs font-semibold text-[#006c49]"
                        >
                            <ShieldCheck class="size-4" />
                            Simple, secure expense tracking
                        </div>
                        <h1
                            class="font-['Geist'] text-4xl leading-tight font-bold tracking-[-0.02em] text-[#161d19] sm:text-5xl lg:text-[48px] lg:leading-[56px]"
                        >
                            Know where your
                            <span class="text-[#006c49]">money goes</span>.
                        </h1>
                        <p
                            class="mx-auto mt-6 max-w-xl text-lg leading-7 text-[#3c4a42] lg:mx-0"
                        >
                            Record, organize, and understand your expenses—all
                            in one simple, beautiful dashboard designed for
                            clarity.
                        </p>
                        <div
                            class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row lg:justify-start"
                        >
                            <Link
                                :href="primaryDestination"
                                class="inline-flex h-14 w-full items-center justify-center rounded-xl bg-[#006c49] px-8 font-['Geist'] text-sm font-medium text-white shadow-lg transition hover:-translate-y-0.5 hover:shadow-[#006c49]/20 active:scale-95 sm:w-auto"
                            >
                                {{
                                    page.props.auth.user
                                        ? 'Go to Dashboard'
                                        : 'Start Tracking for Free'
                                }}
                            </Link>
                            <a
                                href="#how-it-works"
                                class="inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl bg-[#e3eae3] px-8 font-['Geist'] text-sm font-medium text-[#161d19] transition hover:bg-[#d4dcd5] active:scale-95 sm:w-auto"
                            >
                                <PlayCircle class="size-5" />
                                See How It Works
                            </a>
                        </div>
                        <p
                            class="mt-6 flex items-center justify-center gap-2 font-['Geist'] text-xs font-semibold text-[#3c4a42]/70 lg:justify-start"
                        >
                            <ShieldCheck class="size-4 text-[#006c49]" />
                            No credit card required
                        </p>
                    </div>

                    <div class="relative lg:scale-110">
                        <div
                            class="absolute -inset-4 rounded-full bg-[#006c49]/5 blur-3xl"
                        />
                        <div
                            class="relative overflow-hidden rounded-2xl border border-[#bbcabf]/30 bg-white shadow-2xl"
                        >
                            <div
                                class="flex items-center justify-between border-b border-[#bbcabf]/20 bg-[#e3eae3] px-6 py-4"
                            >
                                <div class="flex gap-2" aria-hidden="true">
                                    <span
                                        class="size-3 rounded-full bg-red-400"
                                    />
                                    <span
                                        class="size-3 rounded-full bg-yellow-400"
                                    />
                                    <span
                                        class="size-3 rounded-full bg-green-400"
                                    />
                                </div>
                                <div
                                    class="font-['Geist'] text-xs font-semibold text-[#3c4a42]"
                                >
                                    My Budget Dashboard
                                </div>
                                <div class="w-8" />
                            </div>
                            <div class="grid grid-cols-2 gap-4 p-5 sm:p-6">
                                <div
                                    class="col-span-2 rounded-xl border border-[#006c49]/10 bg-[#006c49]/5 p-4"
                                >
                                    <p
                                        class="text-xs font-semibold text-[#3c4a42]"
                                    >
                                        Total Spending — July
                                    </p>
                                    <p
                                        class="mt-1 font-['Geist'] text-2xl font-bold text-[#006c49]"
                                    >
                                        $2,450.80
                                    </p>
                                </div>
                                <div class="rounded-xl bg-[#eef6ee] p-4">
                                    <p
                                        class="text-xs font-semibold text-[#3c4a42]"
                                    >
                                        Food & Dining
                                    </p>
                                    <div
                                        class="mt-2 h-2 overflow-hidden rounded-full bg-[#bbcabf]/30"
                                    >
                                        <div
                                            class="h-full w-[65%] bg-[#006c49]"
                                        />
                                    </div>
                                    <p
                                        class="mt-1 text-right text-xs font-medium"
                                    >
                                        $420
                                    </p>
                                </div>
                                <div class="rounded-xl bg-[#eef6ee] p-4">
                                    <p
                                        class="text-xs font-semibold text-[#3c4a42]"
                                    >
                                        Entertainment
                                    </p>
                                    <div
                                        class="mt-2 h-2 overflow-hidden rounded-full bg-[#bbcabf]/30"
                                    >
                                        <div
                                            class="h-full w-[30%] bg-[#006c49]"
                                        />
                                    </div>
                                    <p
                                        class="mt-1 text-right text-xs font-medium"
                                    >
                                        $180
                                    </p>
                                </div>
                                <div
                                    class="col-span-2 flex flex-col gap-2 pt-2"
                                >
                                    <div
                                        class="flex items-center justify-between rounded-lg p-2 transition hover:bg-[#e8f0e9]"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex size-10 items-center justify-center rounded-lg bg-[#a43a3a]/10 text-[#a43a3a]"
                                            >
                                                <Coffee class="size-5" />
                                            </div>
                                            <div>
                                                <p
                                                    class="font-['Geist'] text-sm font-medium"
                                                >
                                                    Coffee House
                                                </p>
                                                <p
                                                    class="text-[10px] text-[#3c4a42]"
                                                >
                                                    Today, 9:30 AM
                                                </p>
                                            </div>
                                        </div>
                                        <p
                                            class="font-['Geist'] text-sm font-bold text-[#ba1a1a]"
                                        >
                                            -$4.50
                                        </p>
                                    </div>
                                    <div
                                        class="flex items-center justify-between rounded-lg p-2 transition hover:bg-[#e8f0e9]"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex size-10 items-center justify-center rounded-lg bg-[#006c49]/10 text-[#006c49]"
                                            >
                                                <ShoppingCart class="size-5" />
                                            </div>
                                            <div>
                                                <p
                                                    class="font-['Geist'] text-sm font-medium"
                                                >
                                                    Whole Foods
                                                </p>
                                                <p
                                                    class="text-[10px] text-[#3c4a42]"
                                                >
                                                    Yesterday
                                                </p>
                                            </div>
                                        </div>
                                        <p
                                            class="font-['Geist'] text-sm font-bold text-[#ba1a1a]"
                                        >
                                            -$82.10
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="scroll-mt-20 bg-[#eef6ee] py-24">
                <div class="mx-auto max-w-[1280px] px-5 md:px-10">
                    <div class="mb-16 text-center">
                        <h2
                            class="font-['Geist'] text-3xl leading-10 font-semibold tracking-[-0.01em]"
                        >
                            Financial clarity at your fingertips
                        </h2>
                        <p class="mt-4 text-[#3c4a42]">
                            Simple tools that make a huge difference in your
                            financial life.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                        <article
                            v-for="benefit in benefits"
                            :key="benefit.title"
                            class="rounded-2xl border border-[#bbcabf]/20 bg-white p-8 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-md"
                        >
                            <div
                                class="mb-6 flex size-14 items-center justify-center rounded-xl bg-[#006c49]/10 text-[#006c49]"
                            >
                                <component :is="benefit.icon" class="size-8" />
                            </div>
                            <h3
                                class="font-['Geist'] text-2xl leading-8 font-semibold"
                            >
                                {{ benefit.title }}
                            </h3>
                            <p class="mt-4 leading-6 text-[#3c4a42]">
                                {{ benefit.description }}
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <section
                id="how-it-works"
                class="scroll-mt-20 overflow-hidden py-24"
            >
                <div
                    class="mx-auto flex max-w-[1280px] flex-col items-center gap-20 px-5 md:px-10 lg:flex-row"
                >
                    <div class="w-full lg:w-1/2">
                        <div
                            class="mb-6 inline-flex items-center gap-2 rounded-full bg-[#006c49]/5 px-4 py-2 font-['Geist'] text-xs font-semibold text-[#006c49]"
                        >
                            <CheckCircle2 class="size-4" />
                            Simple 3-Step Process
                        </div>
                        <h2
                            class="font-['Geist'] text-3xl leading-10 font-semibold tracking-[-0.01em]"
                        >
                            Getting started is easier than you think
                        </h2>
                        <div class="mt-12 flex flex-col gap-10">
                            <div
                                v-for="(step, index) in steps"
                                :key="step.title"
                                class="flex gap-5 sm:gap-6"
                            >
                                <div
                                    class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#006c49] font-['Geist'] text-xl font-semibold text-white"
                                >
                                    {{ index + 1 }}
                                </div>
                                <div>
                                    <h3
                                        class="font-['Geist'] text-xl font-semibold"
                                    >
                                        {{ step.title }}
                                    </h3>
                                    <p class="mt-2 leading-6 text-[#3c4a42]">
                                        {{ step.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="relative w-full lg:w-1/2">
                        <div
                            class="absolute -top-10 -right-10 size-64 rounded-full bg-[#006c49]/10 blur-3xl"
                        />
                        <img
                            src="/images/spendora-workflow.png"
                            alt="Spendora transaction entry workflow"
                            class="relative z-10 h-auto w-full rounded-3xl border border-[#bbcabf]/30 shadow-2xl"
                        />
                    </div>
                </div>
            </section>

            <section class="bg-[#2b322d] py-24 text-[#ebf3eb]">
                <div
                    class="mx-auto grid max-w-[1280px] grid-cols-1 items-center gap-16 px-5 md:px-10 lg:grid-cols-2"
                >
                    <div class="relative order-2 lg:order-1">
                        <div
                            class="absolute inset-0 rounded-full bg-[#006c49]/20 blur-[100px]"
                        />
                        <div
                            class="relative overflow-hidden rounded-2xl border border-white/10 bg-[#eef6ee] p-6 text-[#161d19] shadow-2xl sm:p-8"
                        >
                            <div class="mb-8 flex items-center justify-between">
                                <h3
                                    class="font-['Geist'] text-2xl font-semibold"
                                >
                                    Spending Analysis
                                </h3>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        class="rounded-lg bg-[#006c49] px-3 py-1 font-['Geist'] text-xs font-semibold text-white"
                                    >
                                        Month
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-lg bg-[#e3eae3] px-3 py-1 font-['Geist'] text-xs font-semibold text-[#3c4a42]"
                                    >
                                        Year
                                    </button>
                                </div>
                            </div>
                            <div class="mb-8 flex h-48 items-end gap-3">
                                <div
                                    class="h-[40%] flex-1 rounded-t-lg bg-[#006c49]/20"
                                />
                                <div
                                    class="h-[60%] flex-1 rounded-t-lg bg-[#006c49]/40"
                                />
                                <div
                                    class="h-[35%] flex-1 rounded-t-lg bg-[#006c49]/20"
                                />
                                <div
                                    class="h-[90%] flex-1 rounded-t-lg bg-[#006c49]/80"
                                />
                                <div
                                    class="h-[45%] flex-1 rounded-t-lg bg-[#006c49]/20"
                                />
                                <div
                                    class="h-[20%] flex-1 rounded-t-lg bg-[#006c49]/10"
                                />
                            </div>
                            <div
                                class="flex flex-col gap-4 font-['Geist'] text-sm"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-2">
                                        <span
                                            class="size-3 rounded-full bg-[#006c49]"
                                        />
                                        Fixed Costs
                                    </span>
                                    <strong>$1,200.00</strong>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-2">
                                        <span
                                            class="size-3 rounded-full bg-[#006c49]/40"
                                        />
                                        Variable
                                    </span>
                                    <strong>$840.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="order-1 lg:order-2">
                        <h2
                            class="font-['Geist'] text-3xl leading-10 font-semibold tracking-[-0.01em] text-white"
                        >
                            Powerful features,<br />zero complexity.
                        </h2>
                        <ul class="mt-8 flex flex-col gap-6">
                            <li
                                v-for="highlight in highlights"
                                :key="highlight.title"
                                class="flex items-start gap-4"
                            >
                                <CheckCircle2
                                    class="mt-0.5 size-6 shrink-0 text-[#4edea3]"
                                />
                                <div>
                                    <strong class="mb-1 block text-white">
                                        {{ highlight.title }}
                                    </strong>
                                    <p class="leading-6 text-[#bbcabf]">
                                        {{ highlight.description }}
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="get-started" class="scroll-mt-20 bg-white py-24">
                <div class="mx-auto max-w-4xl px-5 text-center">
                    <h2
                        class="font-['Geist'] text-4xl leading-tight font-bold tracking-[-0.02em] sm:text-5xl"
                    >
                        Take control of your spending today.
                    </h2>
                    <p
                        class="mx-auto mt-8 max-w-3xl text-lg leading-7 text-[#3c4a42]"
                    >
                        Join thousands of users who have found financial peace
                        of mind with Spendora. It’s free to start and takes only
                        a minute to set up.
                    </p>
                    <Link
                        :href="primaryDestination"
                        class="mt-12 inline-flex min-h-16 items-center justify-center rounded-2xl bg-[#006c49] px-10 font-['Geist'] text-xl font-semibold text-white shadow-xl shadow-[#006c49]/30 transition hover:-translate-y-1 active:scale-95"
                    >
                        {{
                            page.props.auth.user
                                ? 'Open Your Dashboard'
                                : 'Create Your Free Account'
                        }}
                    </Link>
                </div>
            </section>
        </main>

        <footer class="border-t border-[#bbcabf]/20 bg-white">
            <div
                class="mx-auto grid max-w-[1280px] grid-cols-1 gap-10 px-5 py-12 sm:grid-cols-2 md:px-10 lg:grid-cols-4"
            >
                <div class="flex flex-col gap-4">
                    <a
                        href="#top"
                        class="flex items-center gap-2 font-['Geist'] text-2xl font-bold"
                    >
                        <WalletCards class="size-6 text-[#006c49]" />
                        <span>Spendora</span>
                    </a>
                    <p class="max-w-xs font-['Geist'] text-sm text-[#3c4a42]">
                        Making expense tracking effortless for everyone,
                        everywhere. Simple tools for better financial health.
                    </p>
                </div>
                <div class="flex flex-col gap-4">
                    <h3
                        class="font-['Geist'] text-xs font-semibold tracking-wider uppercase"
                    >
                        Product
                    </h3>
                    <a
                        href="#features"
                        class="text-sm text-[#3c4a42] hover:text-[#006c49]"
                    >
                        Features
                    </a>
                    <a
                        href="#how-it-works"
                        class="text-sm text-[#3c4a42] hover:text-[#006c49]"
                        >How It Works</a
                    >
                    <a
                        href="#get-started"
                        class="text-sm text-[#3c4a42] hover:text-[#006c49]"
                        >Security</a
                    >
                </div>
                <div class="flex flex-col gap-4">
                    <h3
                        class="font-['Geist'] text-xs font-semibold tracking-wider uppercase"
                    >
                        Support
                    </h3>
                    <span class="text-sm text-[#3c4a42]">Privacy Policy</span>
                    <span class="text-sm text-[#3c4a42]">Terms of Service</span>
                    <span class="text-sm text-[#3c4a42]">Contact</span>
                </div>
                <div class="flex flex-col gap-6">
                    <h3
                        class="font-['Geist'] text-xs font-semibold tracking-wider uppercase"
                    >
                        Language
                    </h3>
                    <div class="relative">
                        <select
                            class="w-full appearance-none rounded-lg border border-[#bbcabf]/30 bg-[#eef6ee] px-4 py-2 pr-10 font-['Geist'] text-sm outline-none focus:border-[#006c49]"
                            aria-label="Language"
                        >
                            <option>English (US)</option>
                            <option>العربية (Arabic)</option>
                        </select>
                        <ChevronDown
                            class="pointer-events-none absolute top-2.5 right-3 size-5 text-[#3c4a42]"
                        />
                    </div>
                    <div class="flex gap-4 text-[#3c4a42]">
                        <Share2 class="size-5" aria-label="Share Spendora" />
                        <Mail class="size-5" aria-label="Email Spendora" />
                    </div>
                </div>
            </div>
            <div class="border-t border-[#bbcabf]/10 px-5 py-8">
                <div
                    class="mx-auto flex max-w-[1280px] flex-col items-center justify-between gap-4 font-['Geist'] text-sm text-[#3c4a42] md:flex-row"
                >
                    <span
                        >© {{ new Date().getFullYear() }} Spendora. All rights
                        reserved.</span
                    >
                    <div class="flex gap-8">
                        <span>Cookie Policy</span>
                        <span>Status</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
