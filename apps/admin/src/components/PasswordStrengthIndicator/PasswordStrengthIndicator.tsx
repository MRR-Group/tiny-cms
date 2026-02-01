interface PasswordStrengthIndicatorProps {
  password: string;
}

export function PasswordStrengthIndicator({ password }: PasswordStrengthIndicatorProps) {
  const getStrength = (pwd: string): { score: number; label: string; color: string } => {
    if (pwd.length === 0) {
      return { score: 0, label: '', color: '' };
    }

    let score = 0;

    // Length check
    if (pwd.length >= 8) score++;
    if (pwd.length >= 12) score++;

    // Character variety checks
    if (/[a-z]/.test(pwd)) score++;
    if (/[A-Z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;
    if (/[^a-zA-Z0-9]/.test(pwd)) score++;

    if (score <= 2) {
      return { score, label: 'Weak', color: 'bg-red-500' };
    } else if (score <= 4) {
      return { score, label: 'Medium', color: 'bg-yellow-500' };
    } else {
      return { score, label: 'Strong', color: 'bg-green-500' };
    }
  };

  const strength = getStrength(password);

  if (!password) return null;

  return (
    <div className="mt-2">
      <div className="flex gap-1 mb-1">
        {[1, 2, 3, 4, 5, 6].map((i) => (
          <div
            key={i}
            className={`h-1 flex-1 rounded-full transition-colors ${
              i <= strength.score ? strength.color : 'bg-secondary-700'
            }`}
          />
        ))}
      </div>
      {strength.label && (
        <p
          className={`text-xs ${strength.score <= 2 ? 'text-red-400' : strength.score <= 4 ? 'text-yellow-400' : 'text-green-400'}`}
        >
          Password strength: {strength.label}
        </p>
      )}
    </div>
  );
}
