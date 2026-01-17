import { Routes, Route, Navigate } from 'react-router-dom';
import { Layout } from '@/components/Layout/Layout';
import { Dashboard } from '@/pages/Dashboard';
import { Login } from '@/pages/Login';
import { ForceChangePassword } from '@/pages/ChangePassword';
import { RequestPasswordReset, SetNewPassword } from '@/pages/PasswordReset';
import { CreateUser } from '@/pages/UserManagement';
import { authService } from '@/domain/auth';

function ProtectedRoute({ children }: { children: React.ReactNode }) {
  if (!authService.isAuthenticated()) {
    return <Navigate to="/login" replace />;
  }
  return <>{children}</>;
}

function App() {
  return (
    <Routes>
      {/* Public routes */}
      <Route path="/login" element={<Login />} />
      <Route path="/password-reset" element={<RequestPasswordReset />} />
      <Route path="/password-reset/confirm" element={<SetNewPassword />} />
      <Route path="/auth/reset-password" element={<SetNewPassword />} />
      <Route path="/change-password" element={<ForceChangePassword />} />

      {/* Protected routes */}
      <Route
        path="/"
        element={
          <ProtectedRoute>
            <Layout />
          </ProtectedRoute>
        }
      >
        <Route index element={<Dashboard />} />
        <Route path="users/create" element={<CreateUser />} />
      </Route>
    </Routes>
  );
}

export default App;
