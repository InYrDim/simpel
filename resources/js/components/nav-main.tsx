import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { useState } from 'react';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl, type IsCurrentUrlFn } from '@/hooks/use-current-url';
import type { NavChild, NavItem } from '@/types';

export function NavMain({ items }: { items: NavItem[] }) {
    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel className="text-sidebar-foreground/45 text-[10px] font-semibold tracking-widest uppercase">
                Menu
            </SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) => (
                    <NavMainItem key={item.title} item={item} />
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}

/**
 * Satu item navigasi tingkat atas.
 *
 * Dipisah menjadi komponen sendiri karena memakai hook — daftar nav kini
 * dinamis (disaring per role), jadi jumlah item bisa berubah antar render dan
 * hook tidak boleh dipanggil di dalam map.
 */
function NavMainItem({ item }: { item: NavItem }) {
    const { isCurrentUrl } = useCurrentUrl();
    const children = item.children ?? [];
    const [open, setOpen] = useState(
        children.some((child) => childHasActiveHref(child, isCurrentUrl)),
    );

    if (children.length === 0) {
        return (
            <SidebarMenuItem>
                <SidebarMenuButton
                    asChild
                    isActive={isCurrentUrl(item.href!)}
                    tooltip={{ children: item.title }}
                >
                    <Link href={item.href!} prefetch>
                        {item.icon && <item.icon />}
                        <span>{item.title}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        );
    }

    return (
        <Collapsible open={open} onOpenChange={setOpen}>
            <SidebarMenuItem>
                <CollapsibleTrigger asChild>
                    <SidebarMenuButton tooltip={{ children: item.title }}>
                        {item.icon && <item.icon />}
                        <span>{item.title}</span>
                        <ChevronRight className="ml-auto transition-transform duration-200 [&[data-state=open]]:rotate-90" />
                    </SidebarMenuButton>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <SidebarMenuSub>
                        {children.map((child) => (
                            <NavSubItem key={child.title} child={child} />
                        ))}
                    </SidebarMenuSub>
                </CollapsibleContent>
            </SidebarMenuItem>
        </Collapsible>
    );
}

/**
 * Item submenu, render rekursif untuk mendukung nested.
 */
function NavSubItem({ child }: { child: NavChild }) {
    const { isCurrentUrl } = useCurrentUrl();
    const children = child.children ?? [];
    const [open, setOpen] = useState(
        children.some((sub) => childHasActiveHref(sub, isCurrentUrl)),
    );

    if (children.length === 0) {
        const href = child.href!;
        return (
            <SidebarMenuSubItem>
                <SidebarMenuSubButton asChild isActive={isCurrentUrl(href)}>
                    <Link href={href} prefetch>
                        <span>{child.title}</span>
                    </Link>
                </SidebarMenuSubButton>
            </SidebarMenuSubItem>
        );
    }

    return (
        <Collapsible open={open} onOpenChange={setOpen}>
            <SidebarMenuSubItem>
                <CollapsibleTrigger asChild>
                    <SidebarMenuSubButton>
                        <span>{child.title}</span>
                        <ChevronRight className="ml-auto transition-transform duration-200 [&[data-state=open]]:rotate-90" />
                    </SidebarMenuSubButton>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <SidebarMenuSub>
                        {children.map((sub) => (
                            <NavSubItem key={sub.title} child={sub} />
                        ))}
                    </SidebarMenuSub>
                </CollapsibleContent>
            </SidebarMenuSubItem>
        </Collapsible>
    );
}

function childHasActiveHref(
    child: NavChild,
    isCurrentUrl: IsCurrentUrlFn,
): boolean {
    if (child.href && isCurrentUrl(child.href)) {
        return true;
    }

    return (child.children ?? []).some((sub) =>
        childHasActiveHref(sub, isCurrentUrl),
    );
}
