import Button from '../components/Button';
import { apiClient } from '../lib/apiClient';
import { normalizeTitle } from '../domain/normalizeTitle';

const Dashboard = () => {
  const title = normalizeTitle('tiny cms admin');

  const handlePing = async () => {
    await apiClient('/health');
  };

  return (
    <main className="mx-auto flex min-h-screen max-w-4xl flex-col gap-6 px-6 py-10">
      <header className="space-y-2">
        <p className="text-sm uppercase text-slate-400">Admin panel</p>
        <h1 className="text-3xl font-semibold">{title}</h1>
        <p className="text-slate-300">
          Ready for content management features. Connects to the API via a simple fetch wrapper.
        </p>
      </header>
      <div>
        <Button onClick={handlePing}>Ping API</Button>
      </div>
    </main>
  );
};

export default Dashboard;
