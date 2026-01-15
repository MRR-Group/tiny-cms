import { Route, Routes } from 'react-router-dom';
import Dashboard from './routes/Dashboard';
import NotFound from './routes/NotFound';

const App = () => {
  return (
    <div className="min-h-screen bg-slate-950 text-slate-100">
      <Routes>
        <Route path="/" element={<Dashboard />} />
        <Route path="*" element={<NotFound />} />
      </Routes>
    </div>
  );
};

export default App;
