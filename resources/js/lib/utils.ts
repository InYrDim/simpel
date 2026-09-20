import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: string | { url: string } | undefined): string {
    if (typeof url === 'string') return url;
    return url?.url ?? '#';
}
