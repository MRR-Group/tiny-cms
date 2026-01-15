import { render, screen } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';
import App from './App';

const renderApp = () => {
  render(
    <BrowserRouter>
      <App />
    </BrowserRouter>,
  );
};

describe('App', () => {
  it('renders dashboard', () => {
    renderApp();

    expect(screen.getByRole('heading', { name: /tiny cms admin/i })).toBeInTheDocument();
  });
});
