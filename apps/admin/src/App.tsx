import { BrowserRouter, Route, Routes } from 'react-router-dom'
import { BaseLayout } from './routes/BaseLayout'
import { HomePage } from './routes/HomePage'

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<BaseLayout />}>
          <Route index element={<HomePage />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}

export default App
