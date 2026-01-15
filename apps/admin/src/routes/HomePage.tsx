import { Button } from '../components/Button'

export function HomePage() {
  return (
    <section className="space-y-3">
      <h1 className="text-2xl font-bold">Welcome</h1>
      <p className="text-slate-700">Your content workspace is under construction.</p>
      <Button label="Health check" onClick={() => window.alert('Placeholder action')} />
    </section>
  )
}
