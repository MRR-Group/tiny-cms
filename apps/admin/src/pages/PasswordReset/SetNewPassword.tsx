import { useState, FormEvent } from 'react';
import { useNavigate, useSearchParams, Link } from 'react-router-dom';
import { AuthLayout } from '@/components/AuthLayout';
import { Button } from '@/components/Button/Button';
import { Input } from '@/components/Input/Input';
import { PasswordStrengthIndicator } from '@/components/PasswordStrengthIndicator';
import { authService } from '@/domain/auth';

export function SetNewPassword() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const token = searchParams.get('token') || '';

  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError('');

    if (password !== confirmPassword) {
      setError('Passwords do not match');
      return;
    }

    if (password.length < 8) {
      setError('Password must be at least 8 characters long');
      return;
    }

    if (!token) {
      setError('Invalid password reset link');
      return;
    }

    setIsLoading(true);

    try {
      await authService.setNewPassword({ token, password });
      navigate('/login');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to reset password');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <AuthLayout subtitle="Secure Credentials Update">
      <form onSubmit={handleSubmit} className="space-y-6">
        {error && (
          <div className="bg-red-50 border border-red-100 text-red-500 px-4 py-2.5 rounded-xl text-xs font-medium animate-in fade-in zoom-in duration-300">
            {error}
          </div>
        )}

        <div className="space-y-2">
          <Input
            id="password"
            label="New Secure Password"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            placeholder="••••••••"
          />
          <PasswordStrengthIndicator password={password} />
        </div>

        <Input
          id="confirmPassword"
          label="Verify New Password"
          type="password"
          value={confirmPassword}
          onChange={(e) => setConfirmPassword(e.target.value)}
          required
          placeholder="••••••••"
        />

        <Button
          type="submit"
          variant="primary"
          size="md"
          disabled={isLoading}
          className="w-full mt-2"
        >
          {isLoading ? 'Updating...' : 'Set New Password'}
        </Button>

        <div className="text-center">
          <Link
            to="/login"
            className="text-xs font-semibold text-slate-400 hover:text-primary transition-colors duration-300 uppercase tracking-widest"
          >
            Return to Login
          </Link>
        </div>
      </form>
    </AuthLayout>
  );
}
