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
import { Link, usePage } from '@inertiajs/vue3';
import {Apple, Calendar, ChartColumnIncreasing, Clock, LayoutList, Salad, User, Users} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { trans } from '@/helpers/translator';
import { route } from 'ziggy-js';
import { computed } from 'vue';

const page = usePage();
const authUser = computed(() => page.props.auth.user as { roles?: string } | null);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: trans('dashboard'),
            href: dashboard(),
            icon: ChartColumnIncreasing,
        },
        {
            title: trans('ingredients'),
            href: route('ingredients.index'),
            icon: Apple,
        },
        {
            title: trans('diets'),
            href: route('diet_types.index'),
            icon: LayoutList,
        },
        {
            title: trans('mealtimes'),
            href: route('mealtimes.index'),
            icon: Clock,
        },
        {
            title: trans('eaters'),
            href: route('eaters.index'),
            icon: User,
        },
        {
            title: trans('dishes'),
            href: route('dishes.index'),
            icon: Salad,
        },
        {
            title: trans('days'),
            href: route('days.index'),
            icon: Calendar,
        },
    ];

    if (authUser.value?.roles === 'ROLE_SUPERADMIN') {
        items.push({
            title: trans('users'),
            href: route('users.index'),
            icon: Users,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [
    /*
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
    */
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
