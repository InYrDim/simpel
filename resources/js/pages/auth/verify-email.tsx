import { Head } from '@inertiajs/react';
import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

export default function VerifyEmail({ status }: { status?: string }) {
    const { post, processing } = useForm();

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(send.url(), { onError: () => {} });
    };

    return (
        <>
            <Head title="Email verification" />

            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    A new verification link has been sent to the email address
                    you provided during registration.
                </div>
            )}

            <form onSubmit={submit} className="space-y-6 text-center">
                <Button disabled={processing} variant="secondary">
                    {processing && <Spinner />}
                    Resend verification email
                </Button>

                <button
                    type="submit"
                    formAction={logout.url()}
                    className="text-muted-foreground mx-auto block text-sm underline decoration-neutral-300 underline-offset-4"
                >
                    Log out
                </button>
            </form>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Email verification',
    description:
        'Please verify your email address by clicking on the link we just emailed to you.',
};
