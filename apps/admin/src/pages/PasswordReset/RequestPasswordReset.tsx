import { useState, FormEvent } from 'react';
import { Link } from 'react-router-dom';
import { AuthLayout } from '@/components/AuthLayout';
import { Button } from '@/components/Button/Button';
import { authService } from '@/domain/auth';

export function RequestPasswordReset() {
    const [email, setEmail] = useState('');
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);
    const [isLoading, setIsLoading] = useState(false);

    const handleSubmit = async (e: FormEvent) => {
        e.preventDefault();
        setError('');
        setIsLoading(true);

        try {
            await authService.requestPasswordReset({ email });
            setSuccess(true);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Failed to request password reset');
        } finally {
            setIsLoading(false);
        }
    };

    if (success) {
        return (
            <AuthLayout
                title="Check Your Email"
                subtitle="Password reset instructions sent"
            >
                <div className="text-center space-y-4">
                    <div className="bg-green-500/20 border border-green-500/50 text-green-200 px-4 py-3 rounded-lg text-sm">
                        We've sent password reset instructions to <strong>{email}</strong>
                    </div>
                    <p className="text-secondary-300 text-sm">
                        Please check your inbox and follow the instructions to reset your password.
                    </p>
                    <Link to="/login">
                        <Button variant="secondary" className="w-full">
                            Back to Login
                        </Button>
                    </Link>
                </div>
            </AuthLayout>
        );
    }

    return (
        <AuthLayout
            title="Reset Password"
            subtitle="Enter your email to receive reset instructions"
        >
            <form onSubmit={handleSubmit} className="space-y-6">
                {error && (
                    <div className="bg-red-500/20 border border-red-500/50 text-red-200 px-4 py-3 rounded-lg text-sm">
                        {error}
                    </div>
                )}

                <div>
                    <label htmlFor="email" className="block text-sm font-medium text-white mb-2">
                        Email
                    </label>
                    <input
                        id="email"
                        type="email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                        className="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-secondary-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                        placeholder="your@email.com"
                    />
                </div>

                <Button
                    type="submit"
                    variant="primary"
                    disabled={isLoading}
                    className="w-full"
                >
                    {isLoading ? 'Sending...' : 'Send Reset Instructions'}
                </Button>

                <div className="text-center">
                    <Link
                        to="/login"
                        className="text-sm text-secondary-300 hover:text-white transition-colors"
                    >
                        Back to Login
                    </Link>
                </div>
            </form>
        </AuthLayout>
    );
}
