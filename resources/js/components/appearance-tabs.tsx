import { Monitor, Moon, Sun, type LucideIcon } from 'lucide-react';
import type { HTMLAttributes } from 'react';
import type { Appearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';

export default function AppearanceToggleTab({
    className = '',
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    const { appearance, updateAppearance } = useAppearance();

    const tabs: { value: Appearance; icon: LucideIcon; label: string }[] = [
        { value: 'light', icon: Sun, label: 'Light' },
        { value: 'dark', icon: Moon, label: 'Dark' },
        { value: 'system', icon: Monitor, label: 'System' },
    ];

    return (
        <div
            className={cn(
                'bg-muted inline-flex gap-1 rounded-lg p-1',
                className,
            )}
            {...props}
        >
            <ToggleGroup
                type="single"
                value={appearance}
                onValueChange={(v) => v && updateAppearance(v as Appearance)}
                variant="outline"
                size="sm"
                className="border-0 shadow-none"
            >
                {tabs.map(({ value, icon: Icon, label }) => (
                    <ToggleGroupItem
                        key={value}
                        value={value}
                        aria-label={label}
                    >
                        <Icon className="h-4 w-4" />
                        <span>{label}</span>
                    </ToggleGroupItem>
                ))}
            </ToggleGroup>
        </div>
    );
}
