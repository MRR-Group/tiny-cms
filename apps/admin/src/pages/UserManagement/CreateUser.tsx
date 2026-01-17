import { useState, FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { Button } from '@/components/Button/Button';
import { Input } from '@/components/Input/Input';
import { Select } from '@/components/Select/Select';
import { PasswordStrengthIndicator } from '@/components/PasswordStrengthIndicator';
import { authService } from '@/domain/auth';

export function CreateUser() {
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [role, setRole] = useState('user');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError('');
    setIsLoading(true);

    try {
      await authService.createUser({ email, password, role });
      setSuccess(true);
      setTimeout(() => navigate('/'), 2000);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to create user');
    } finally {
      setIsLoading(false);
    }
  };

  const roleOptions = [
    { value: 'user', label: 'Standard User' },
    { value: 'admin', label: 'Administrator' },
  ];

  return (
    <div className="animate-in fade-in slide-in-from-left-4 duration-500">
      <div className="mb-10">
        <h1 className="text-3xl font-serif font-semibold text-slate-900 mb-1">Create User</h1>
        <p className="text-slate-500 text-sm">Provision a new administrative account.</p>
      </div>

      <div className="bg-white border border-slate-100 rounded-2xl p-8 max-w-xl shadow-xl shadow-slate-100/50">
        {success && (
          <div className="mb-6 bg-emerald-50 border border-emerald-100 text-emerald-600 px-4 py-3 rounded-xl text-sm font-medium animate-in zoom-in duration-300">
            User created successfully! Redirecting...
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-6">
          {error && (
            <div className="bg-red-50 border border-red-100 text-red-500 px-4 py-3 rounded-xl text-sm font-medium">
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
            placeholder="user@example.com"
          />

          <div className="space-y-1.5">
            <Input
              id="password"
              label="Temporary Password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              placeholder="••••••••"
            />
            <PasswordStrengthIndicator password={password} />
          </div>

          <Select
            id="role"
            label="Account Role"
            value={role}
            onChange={(e) => setRole(e.target.value)}
            options={roleOptions}
          />

          <div className="flex gap-3 pt-2">
            <Button
              type="submit"
              variant="primary"
              size="md"
              disabled={isLoading || success}
              className="flex-1"
            >
              {isLoading ? 'Creating...' : 'Create User'}
            </Button>
            <Button
              type="button"
              variant="secondary"
              size="md"
              onClick={() => navigate('/')}
              className="px-6"
            >
              Cancel
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
}
