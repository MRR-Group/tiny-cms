import { useState, FormEvent } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { AuthLayout } from '@/components/AuthLayout';
import { Button } from '@/components/Button/Button';
import { Input } from '@/components/Input/Input';
import { authService } from '@/domain/auth';

export function Login() {
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError('');
    setIsLoading(true);

    try {
      const response = await authService.login({ email, password });

      if (response.requirePasswordChange) {
        navigate('/change-password');
      } else {
        navigate('/');
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Login failed');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <AuthLayout subtitle="Administrative Gateway">
      <form onSubmit={handleSubmit} className="space-y-5">
        {error && (
          <div className="bg-red-50 border border-red-100 text-red-500 px-4 py-2.5 rounded-xl text-xs font-medium animate-in fade-in zoom-in duration-300">
            {error}
          </div>
        )}

        <Input
          id="email"
          label="Email Address"
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
          placeholder="name@example.com"
        />

        <Input
          id="password"
          label="Password"
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
          placeholder="••••••••"
        />

        <div className="flex items-center justify-end px-1">
          <Link
            to="/password-reset"
            className="text-[11px] font-medium text-slate-400 hover:text-primary transition-colors duration-300"
          >
            Forgot password?
          </Link>
        </div>

        <Button
          type="submit"
          variant="primary"
          size="md"
          disabled={isLoading}
          className="w-full rounded-xl py-3.5 text-sm"
        >
          {isLoading ? 'Authenticating...' : 'Sign In'}
        </Button>
      </form>
    </AuthLayout>
  );
}
