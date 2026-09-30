import { Check, ChevronsUpDown } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

export type SearchableOption = {
    value: string;
    label: string;
    hint?: string;
};

type SearchableSelectProps = {
    id: string;
    value: string;
    onChange: (value: string) => void;
    options: SearchableOption[];
    placeholder: string;
    searchPlaceholder?: string;
    emptyText?: string;
    /** Bila diisi, muncul pilihan pertama untuk mengosongkan nilai. */
    clearLabel?: string;
    disabled?: boolean;
    invalid?: boolean;
    className?: string;
};

/**
 * Pilihan yang bisa dicari — untuk daftar panjang (akun, dosen) yang tidak
 * cocok dengan `Select` biasa. Dibangun dari Popover + Command (cmdk), jadi
 * navigasi keyboard dan pembaca layar ditangani library.
 */
export function SearchableSelect({
    id,
    value,
    onChange,
    options,
    placeholder,
    searchPlaceholder = 'Cari...',
    emptyText = 'Tidak ditemukan.',
    clearLabel,
    disabled = false,
    invalid = false,
    className,
}: SearchableSelectProps) {
    const [open, setOpen] = useState(false);
    const selected = options.find((option) => option.value === value);

    const choose = (next: string) => {
        onChange(next);
        setOpen(false);
    };

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    id={id}
                    type="button"
                    variant="outline"
                    role="combobox"
                    aria-expanded={open}
                    aria-invalid={invalid || undefined}
                    disabled={disabled}
                    className={cn(
                        'w-full justify-between font-normal',
                        !selected && 'text-muted-foreground',
                        className,
                    )}
                >
                    <span className="truncate">
                        {selected ? selected.label : placeholder}
                    </span>
                    <ChevronsUpDown aria-hidden="true" className="opacity-50" />
                </Button>
            </PopoverTrigger>
            <PopoverContent
                align="start"
                className="w-(--radix-popover-trigger-width) p-0"
            >
                <Command>
                    <CommandInput placeholder={searchPlaceholder} />
                    <CommandList>
                        <CommandEmpty>{emptyText}</CommandEmpty>
                        <CommandGroup>
                            {clearLabel && (
                                <CommandItem
                                    value={clearLabel}
                                    onSelect={() => choose('')}
                                >
                                    <span className="text-muted-foreground">
                                        {clearLabel}
                                    </span>
                                    {value === '' && (
                                        <Check className="ml-auto" />
                                    )}
                                </CommandItem>
                            )}
                            {options.map((option) => (
                                <CommandItem
                                    key={option.value}
                                    value={`${option.label} ${option.hint ?? ''}`}
                                    onSelect={() => choose(option.value)}
                                >
                                    <span className="flex min-w-0 flex-col">
                                        <span className="truncate">
                                            {option.label}
                                        </span>
                                        {option.hint && (
                                            <span className="text-muted-foreground truncate text-xs">
                                                {option.hint}
                                            </span>
                                        )}
                                    </span>
                                    {option.value === value && (
                                        <Check className="ml-auto" />
                                    )}
                                </CommandItem>
                            ))}
                        </CommandGroup>
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    );
}
