<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    CalendarDays,
    ChartColumn,
    CircleHelp,
    LayoutDashboard,
    LogOut,
    Tags,
    UserRound,
} from '@lucide/vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { useTranslations } from '@/composables/useTranslations';
import { analytics, dashboard, logout } from '@/routes';
import { index as categoriesIndex } from '@/routes/categories';
import { index as expensesIndex } from '@/routes/expenses';
import { edit as profileEdit } from '@/routes/profile';

const { isCurrentOrParentUrl, isCurrentUrl } = useCurrentUrl();
const { locale, t } = useTranslations();

const navigationItemClass =
    'flex min-h-12 items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200 active:translate-x-1';
</script>

<template>
    <Sidebar
        :side="locale === 'ar' ? 'right' : 'left'"
        collapsible="offcanvas"
        variant="sidebar"
        class="border-e border-[#bbcabf] bg-[#e8f0e9] [--sidebar-background:#e8f0e9] [--sidebar-foreground:#3c4a42]"
    >
        <SidebarHeader class="gap-0 bg-[#e8f0e9] px-6 pt-8 pb-10">
            <Link :href="dashboard()" class="flex items-center gap-3">
                <span
                    class="flex size-10 shrink-0 items-center justify-center rounded-full bg-[#006c49] text-2xl font-semibold text-white shadow-sm"
                    aria-hidden="true"
                >
                    S
                </span>
                <span class="min-w-0">
                    <span
                        class="block truncate text-xl font-bold tracking-tight text-[#006c49]"
                    >
                        {{ t('brand.name') }}
                    </span>
                    <span
                        class="block truncate text-xs font-semibold text-[#565e74]"
                    >
                        {{ t('brand.tagline') }}
                    </span>
                </span>
            </Link>
        </SidebarHeader>

        <SidebarContent class="gap-0 bg-[#e8f0e9] px-6">
            <nav
                :aria-label="t('navigation.primary')"
                class="flex flex-col gap-2"
            >
                <Link
                    :href="dashboard()"
                    :class="[
                        navigationItemClass,
                        isCurrentUrl(dashboard())
                            ? 'bg-[#10b981] font-bold text-[#00422b] shadow-sm'
                            : 'text-[#3c4a42] hover:bg-[#dde4dd]',
                    ]"
                >
                    <LayoutDashboard class="size-5 shrink-0" />
                    <span>{{ t('navigation.home') }}</span>
                </Link>

                <Link
                    :href="expensesIndex()"
                    :class="[
                        navigationItemClass,
                        isCurrentOrParentUrl(expensesIndex())
                            ? 'bg-[#10b981] font-bold text-[#00422b] shadow-sm'
                            : 'text-[#3c4a42] hover:bg-[#dde4dd]',
                    ]"
                >
                    <CalendarDays class="size-5 shrink-0" />
                    <span>{{ t('navigation.expenses') }}</span>
                </Link>

                <Link
                    :href="categoriesIndex()"
                    :class="[
                        navigationItemClass,
                        isCurrentOrParentUrl(categoriesIndex())
                            ? 'bg-[#10b981] font-bold text-[#00422b] shadow-sm'
                            : 'text-[#3c4a42] hover:bg-[#dde4dd]',
                    ]"
                >
                    <Tags class="size-5 shrink-0" />
                    <span>{{ t('navigation.categories') }}</span>
                </Link>

                <Link
                    :href="analytics()"
                    :class="[
                        navigationItemClass,
                        isCurrentOrParentUrl(analytics())
                            ? 'bg-[#10b981] font-bold text-[#00422b] shadow-sm'
                            : 'text-[#3c4a42] hover:bg-[#dde4dd]',
                    ]"
                >
                    <ChartColumn class="size-5 shrink-0" />
                    <span>{{ t('navigation.analytics') }}</span>
                </Link>

                <Link
                    :href="profileEdit()"
                    :class="[
                        navigationItemClass,
                        isCurrentOrParentUrl(profileEdit())
                            ? 'bg-[#10b981] font-bold text-[#00422b] shadow-sm'
                            : 'text-[#3c4a42] hover:bg-[#dde4dd]',
                    ]"
                >
                    <UserRound class="size-5 shrink-0" />
                    <span>{{ t('navigation.profile') }}</span>
                </Link>
            </nav>
        </SidebarContent>

        <SidebarFooter class="gap-6 bg-[#e8f0e9] px-6 pt-6 pb-8">
            <div class="flex flex-col gap-1 border-t border-[#bbcabf] pt-6">
                <button
                    type="button"
                    class="flex items-center gap-3 rounded-lg px-4 py-2 text-start text-xs font-semibold text-[#3c4a42] transition-colors hover:bg-[#dde4dd] hover:text-[#006c49]"
                >
                    <CircleHelp class="size-4" />
                    <span>{{ t('navigation.help') }}</span>
                </button>

                <Link
                    :href="logout()"
                    method="post"
                    as="button"
                    class="flex w-full items-center gap-3 rounded-lg px-4 py-2 text-start text-xs font-semibold text-[#3c4a42] transition-colors hover:bg-[#dde4dd] hover:text-[#006c49]"
                >
                    <LogOut class="size-4" />
                    <span>{{ t('navigation.logout') }}</span>
                </Link>
            </div>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
