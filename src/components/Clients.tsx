import { motion } from 'framer-motion';

export default function Clients() {
  const clients = [
    'TechCorp', 'InnovateLab', 'CloudScale', 'DataFlow', 'NexGen',
    'FutureAI', 'CodeMasters', 'DigiSphere', 'WebVision', 'AppForge',
    'ByteWave', 'SmartSync', 'ProDev', 'NetCore', 'SkyLabs'
  ];

  return (
    <section className="relative py-20 bg-gradient-to-b from-black via-[#0b0b0b] to-black overflow-hidden">
      <div className="absolute inset-0">
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl"></div>
      </div>

      <div className="relative z-10 max-w-7xl mx-auto px-6">
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.8 }}
          className="text-center mb-12"
        >
          <h2 className="text-3xl md:text-4xl font-bold text-white mb-4">
            Trusted by Leading <span className="bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">Companies</span>
          </h2>
        </motion.div>

        <div className="relative overflow-hidden">
          <div className="flex animate-marquee">
            {[...clients, ...clients].map((client, index) => (
              <div
                key={index}
                className="flex-shrink-0 mx-8 px-8 py-6 bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl hover:bg-white/10 transition-all duration-300"
              >
                <span className="text-xl font-bold text-white whitespace-nowrap">{client}</span>
              </div>
            ))}
          </div>
        </div>
      </div>

      <style>{`
        @keyframes marquee {
          0% {
            transform: translateX(0);
          }
          100% {
            transform: translateX(-50%);
          }
        }
        .animate-marquee {
          animation: marquee 30s linear infinite;
        }
        .animate-marquee:hover {
          animation-play-state: paused;
        }
      `}</style>
    </section>
  );
}
