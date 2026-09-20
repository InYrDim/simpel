import { Head, useForm } from '@inertiajs/react';
import { useRef } from 'react';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import type { Props as ManagePasskeysProps } from '@/components/manage-passkeys';
import ManagePasskeys from '@/components/manage-passkeys';
import type { Props as ManageTwoFactorProps } from '@/components/manage-two-factor';
import ManageTwoFactor from '@/components/manage-two-factor';

// oxfmt-ignore
type Props = {
    passwordRules: string;
} & ManagePasskeysProps &
    ManageTwoFactorProps;

export default function Security(props: Props) {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    const { data, setData, put, processing, errors } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(SecurityController.update.url(), {
            preserveScroll: true,
            onError: () => {
                if (errors.password) {
                    passwordInput.current?.focus();
                }

                if (errors.current_password) {
                    currentPasswordInput.current?.focus();
                }
            },
        });
    };

    return (
        <>
            <Head title="Security settings" />

            <h1 className="sr-only">Security settings</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Update password"
                    description="Ensure your account is using a long, random password to stay secure"
                />

                <form onSubmit={submit} className="space-y-6">
                    <div className="grid gap-2">
                        <Label htmlFor="current_password">
                            Current password
                        </Label>

                        <PasswordInput
                            id="current_password"
                            ref={currentPasswordInput}
                            name="current_password"
                            value={data.current_password}
                            onChange={(e) =>
                                setData('current_password', e.target.value)
                            }
                            className="mt-1 block w-full"
                            autoComplete="current-password"
                            placeholder="Current password"
                        />

                        <InputError message={errors.current_password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">New password</Label>

                        <PasswordInput
                            id="password"
                            ref={passwordInput}
                            name="password"
                            value={data.password}
                            onChange={(e) =>
                                setData('password', e.target.value)
                            }
                            className="mt-1 block w-full"
                            autoComplete="new-password"
                            placeholder="New password"
                            passwordrules={props.passwordRules}
                        />

                        <InputError message={errors.password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">
                            Confirm password
                        </Label>

                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            value={data.password_confirmation}
                            onChange={(e) =>
                                setData('password_confirmation', e.target.value)
                            }
                            className="mt-1 block w-full"
                            autoComplete="new-password"
                            placeholder="Confirm password"
                            passwordrules={props.passwordRules}
                        />

                        <InputError message={errors.password_confirmation} />
                    </div>

                    <div className="flex items-center gap-4">
                        <Button
                            disabled={processing}
                            data-test="update-password-button"
                        >
                            Save
                        </Button>
                    </div>
                </form>
            </div>

            <ManageTwoFactor
                canManageTwoFactor={props.canManageTwoFactor}
                requiresConfirmation={props.requiresConfirmation}
                twoFactorEnabled={props.twoFactorEnabled}
            />

            <ManagePasskeys
                canManagePasskeys={props.canManagePasskeys}
                passkeys={props.passkeys}
            />
        </>
    );
}

Security.layout = {
    breadcrumbs: [
        {
            title: 'Security settings',
            href: SecurityController.edit.url(),
        },
    ],
};
