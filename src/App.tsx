import Navbar from './components/Navbar';
import Hero from './components/Hero';
import Services from './components/Services';
import Features from './components/Features';
import Process from './components/Process';
import Portfolio from './components/Portfolio';
import Clients from './components/Clients';
import Testimonials from './components/Testimonials';
import Statistics from './components/Statistics';
import CTA from './components/CTA';
import Footer from './components/Footer';

function App() {
  return (
    <div className="min-h-screen bg-black">
      <Navbar />
      <Hero />
      <Services />
      <Features />
      <Process />
      <Portfolio />
      <Clients />
      <Testimonials />
      <Statistics />
      <CTA />
      <Footer />
    </div>
  );
}

export default App;
