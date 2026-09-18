import * as React from 'react';
import { Controller, FormProvider, useFormContext } from 'react-hook-form';
import { Label } from '@/components/ui/label';
import { Slot } from '@radix-ui/react-slot';
import { cn } from '@/lib/utils';

type FormProps<T extends Record<string, unknown>> = {
    methods: ReturnType<typeof import('react-hook-form').useForm<T>>;
    onSubmit: (e?: React.BaseSyntheticEvent) => void;
    children: React.ReactNode;
    className?: string;
};

function Form<T extends Record<string, unknown>>({ methods, onSubmit, children, className }: FormProps<T>) {
    return (
        <FormProvider {...methods}>
            <form onSubmit={onSubmit} className={cn('space-y-6', className)}>
                {children}
            </form>
        </FormProvider>
    );
}

function FormField<T extends Record<string, unknown>>({
    name,
    render,
}: {
    name: keyof T;
    render: (props: { field: Record<string, unknown>; fieldState: { error?: { message?: string } } }) => React.ReactElement;
}) {
    const methods = useFormContext<T>();
    const control = methods.control as never;
    return <Controller name={name as never} control={control} render={render} />;
}

function FormItem({ className, ...props }: React.ComponentProps<'div'>) {
    return <div className={cn('space-y-2', className)} {...props} />;
}

function FormLabel({ className, ...props }: React.ComponentProps<typeof Label>) {
    return <Label className={cn(className)} {...props} />;
}

function FormControl({ ...props }: React.ComponentProps<typeof Slot>) {
    return <Slot data-slot="form-control" {...props} />;
}

function FormMessage({ className, children, ...props }: React.ComponentProps<'p'>) {
    const ctx = useFormContext();
    const raw =
        (ctx?.formState?.errors as { root?: { message?: string } } | undefined)
            ?.root?.message;
    const body: React.ReactNode = children ?? raw;

    if (!body) return null;

    return (
        <p className={cn('text-sm text-red-600 dark:text-red-400', className)} {...props}>
            {body}
        </p>
    );
}

function FormFieldError({ error }: { error?: { message?: string } }) {
    if (!error?.message) return null;
    return <p className="text-sm text-red-600 dark:text-red-400">{error.message}</p>;
}

export { Form, FormField, FormItem, FormLabel, FormControl, FormMessage, FormFieldError };
