import { useState, FormEvent } from 'react';
import { Link } from 'react-router-dom';
import { AuthLayout } from '@/components/AuthLayout';
import { Button } from '@/components/Button/Button';
import { Input } from '@/components/Input/Input';
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
      <AuthLayout subtitle="Email Sent Successfully">
        <div className="text-center space-y-6">
          <div className="bg-emerald-50 border border-emerald-100 text-emerald-600 px-4 py-3 rounded-xl text-xs font-medium italic">
            Instructions to reset your password have been sent to <strong>{email}</strong>.
          </div>
          <p className="text-slate-400 text-sm italic">
            Please check your inbox and follow the link to securely update your credentials.
          </p>
          <Link to="/login" className="block">
            <Button variant="secondary" size="md" className="w-full">
              Return to Login
            </Button>
          </Link>
        </div>
      </AuthLayout>
    );
  }

  return (
    <AuthLayout subtitle="Password Recovery">
      <form onSubmit={handleSubmit} className="space-y-6">
        {error && (
          <div className="bg-red-50 border border-red-100 text-red-500 px-4 py-2.5 rounded-xl text-xs font-medium animate-in fade-in zoom-in duration-300">
            {error}
          </div>
        )}

        <Input
          id="email"
          label="Registered Email Address"
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
          placeholder="name@example.com"
        />

        <Button type="submit" variant="primary" size="md" disabled={isLoading} className="w-full">
          {isLoading ? 'Processing...' : 'Send Recovery Link'}
        </Button>

        <div className="text-center">
          <Link
            to="/login"
            className="text-xs font-semibold text-slate-400 hover:text-primary transition-colors duration-300 uppercase tracking-widest"
          >
            Back to Login
          </Link>
        </div>
      </form>
    </AuthLayout>
  );
}
