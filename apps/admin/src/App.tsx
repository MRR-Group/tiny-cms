import { Routes, Route, Navigate } from 'react-router-dom';
import { Layout } from '@/components/Layout/Layout';
import { Dashboard } from '@/pages/Dashboard';
import { Login } from '@/pages/Login';
import { ForceChangePassword } from '@/pages/ChangePassword';
import { RequestPasswordReset, SetNewPassword } from '@/pages/PasswordReset';
import { CreateUser } from '@/pages/UserManagement';
import { SitesPage } from '@/pages/admin/SitesPage';
import { SiteDetailsPage } from '@/pages/admin/SiteDetailsPage';
import { SiteSectionPage } from '@/pages/admin/SiteSectionPage';
import { createAuthService } from '@/domain/auth';

function ProtectedRoute({ children }: { children: React.ReactNode }) {
  if (!createAuthService().isAuthenticated()) {
    return <Navigate to="/login" replace />;
  }
  return <>{children}</>;
}

function DefaultRoute() {
  const userRole = createAuthService().getUserRole();

  if (userRole === 'admin') {
    return <Navigate to="/admin/sites" replace />;
  }

  return <Dashboard />;
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
        <Route index element={<DefaultRoute />} />
        <Route path="users/create" element={<CreateUser />} />
        <Route path="admin/sites" element={<SitesPage />} />
        <Route path="admin/sites/:id" element={<SiteDetailsPage />} />
        <Route path="admin/sites/:id/sections/:sectionId" element={<SiteSectionPage />} />
      </Route>
    </Routes>
  );
}

export default App;
