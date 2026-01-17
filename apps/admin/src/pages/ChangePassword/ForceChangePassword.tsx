import { useState, FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { AuthLayout } from '@/components/AuthLayout';
import { Button } from '@/components/Button/Button';
import { Input } from '@/components/Input/Input';
import { PasswordStrengthIndicator } from '@/components/PasswordStrengthIndicator';
import { authService } from '@/domain/auth';

export function ForceChangePassword() {
  const navigate = useNavigate();
  const [oldPassword, setOldPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setError('');

    if (newPassword !== confirmPassword) {
      setError('Passwords do not match');
      return;
    }

    if (newPassword.length < 8) {
      setError('Password must be at least 8 characters long');
      return;
    }

    setIsLoading(true);

    try {
      await authService.changePassword({ oldPassword, newPassword });
      navigate('/');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to change password');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <AuthLayout subtitle="Security Update Required">
      <div className="mb-6 bg-primary/5 border border-primary/10 text-primary px-4 py-3 rounded-xl text-xs font-medium italic">
        For security reasons, you must change your temporary password before continuing.
      </div>

      <form onSubmit={handleSubmit} className="space-y-5">
        {error && (
          <div className="bg-red-50 border border-red-100 text-red-500 px-4 py-2.5 rounded-xl text-xs font-medium animate-in fade-in zoom-in duration-300">
            {error}
          </div>
        )}

        <Input
          id="oldPassword"
          label="Current Password"
          type="password"
          value={oldPassword}
          onChange={(e) => setOldPassword(e.target.value)}
          required
          placeholder="••••••••"
        />

        <div className="space-y-2">
          <Input
            id="newPassword"
            label="New Password"
            type="password"
            value={newPassword}
            onChange={(e) => setNewPassword(e.target.value)}
            required
            placeholder="••••••••"
          />
          <PasswordStrengthIndicator password={newPassword} />
        </div>

        <Input
          id="confirmPassword"
          label="Confirm New Password"
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
          className="w-full rounded-xl py-3.5 text-sm mt-2"
        >
          {isLoading ? 'Updating password...' : 'Update Password'}
        </Button>
      </form>
    </AuthLayout>
  );
}
