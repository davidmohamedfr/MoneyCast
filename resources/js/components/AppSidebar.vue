<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    FileUp,
    Folder,
    LayoutGrid,
    Plus,
    Receipt,
    Wallet,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
        icon: LayoutGrid,
    },
    {
        title: 'Accounts',
        href: '/accounts',
        icon: Wallet,
    },
    {
        title: 'Transactions',
        href: '/transactions',
        icon: Receipt,
    },
    {
        title: 'Imports',
        href: '/imports',
        icon: FileUp,
    },
];

const quickActions: NavItem[] = [
    {
        title: 'New Transaction',
        href: '/transactions/create',
        icon: Plus,
    },
    {
        title: 'New Account',
        href: '/accounts/create',
        icon: Wallet,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar id="sidebar-nav" collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard().url">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain :items="quickActions" title="Quick Actions" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
            <div
                class="mt-2 hidden px-4 text-center text-xs text-muted-foreground group-data-[collapsible=icon]:hidden md:block"
            >
                Press
                <kbd
                    class="mx-1 inline-flex h-4 min-w-4 items-center justify-center rounded border border-border bg-muted px-1 text-[10px] font-semibold"
                    >?</kbd
                >
                for shortcuts
            </div>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
