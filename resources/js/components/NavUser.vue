<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import { type User } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronsUpDown, LogIn, UserPlus } from 'lucide-vue-next';
import UserMenuContent from './UserMenuContent.vue';

const page = usePage();
const user = page.props.auth.user as User | null;
const { isMobile, state } = useSidebar();
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <!-- Authenticated User Dropdown -->
            <DropdownMenu v-if="user">
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton size="lg" class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground">
                        <UserInfo :user="user" />
                        <ChevronsUpDown class="ml-auto size-4" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                    :side="isMobile ? 'bottom' : state === 'collapsed' ? 'left' : 'bottom'"
                    align="end"
                    :side-offset="4"
                >
                    <UserMenuContent :user="user" />
                </DropdownMenuContent>
            </DropdownMenu>

            <!-- Guest User Buttons -->
            <div v-else class="space-y-2">
                <SidebarMenuButton size="lg" as-child>
                    <Link :href="route('login')" class="flex items-center gap-2 w-full">
                        <LogIn class="size-4" />
                        <span>Login</span>
                    </Link>
                </SidebarMenuButton>
                <SidebarMenuButton size="lg" as-child>
                    <Link :href="route('register')" class="flex items-center gap-2 w-full">
                        <UserPlus class="size-4" />
                        <span>Register</span>
                    </Link>
                </SidebarMenuButton>
            </div>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
