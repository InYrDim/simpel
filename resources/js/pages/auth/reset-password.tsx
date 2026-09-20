import { Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useForm } from '@inertiajs/react';
import { update } from '@/routes/password';

type Props = {
    token: string;
    email: string;
    passwordRules: string;
};

export default function ResetPassword({ token, email, passwordRules }: Props) {
    const { data, setData, patch, processing, errors } = useForm({
        email,
        password: '',
        password_confirmation: '',
        token,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(update.url(), {
            onError: () => {},
        });
    };

    return (
        <>
            <Head title="Reset password" />

            <form onSubmit={submit} className="grid gap-6">
                <div className="grid gap-2">
                    <Label htmlFor="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        value={email}
                        readOnly
                        className="mt-1 block w-full"
                    />
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="password">Password</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        autoComplete="new-password"
                        className="mt-1 block w-full"
                        autoFocus
                        placeholder="Password"
                        passwordrules={passwordRules}
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
                        autoComplete="new-password"
                        className="mt-1 block w-full"
                        placeholder="Confirm password"
                        passwordrules={passwordRules}
                    />
                    <InputError
                        message={errors.password_confirmation}
                        className="mt-2"
                    />
                </div>

                <Button
                    type="submit"
                    className="mt-4 w-full"
                    disabled={processing}
                    data-test="reset-password-button"
                >
                    {processing && <Spinner />}
                    Reset password
                </Button>
            </form>
        </>
    );
}

ResetPassword.layout = {
    title: 'Reset password',
    description: 'Please enter your new password below',
};
