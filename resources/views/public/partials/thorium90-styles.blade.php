<style>
:root {
    --thorium-primary: #e91e63;
    --thorium-secondary: #00bcd4;
    --thorium-accent: #9c27b0;
    --thorium-gradient: linear-gradient(135deg, #e91e63 0%, #9c27b0 50%, #00bcd4 100%);
    --thorium-gradient-subtle: linear-gradient(135deg, rgba(233, 30, 99, 0.1) 0%, rgba(156, 39, 176, 0.1) 50%, rgba(0, 188, 212, 0.1) 100%);
}

.home-template {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    line-height: 1.6;
    transition: background-color 0.3s ease, color 0.3s ease;
}

/* Dark mode styles */
.dark .home-template {
    background-color: #0f0f23;
    color: #e2e8f0;
}

.dark .glass-effect {
    backdrop-filter: blur(12px);
    background: rgba(15, 15, 35, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.dark .bg-white {
    background-color: #1a1a2e !important;
}

.dark .bg-gray-50 {
    background-color: #16213e !important;
}

.dark .bg-gray-900 {
    background-color: #0a0a18 !important;
}

.dark .text-gray-900 {
    color: #f1f5f9 !important;
}

.dark .text-gray-700 {
    color: #cbd5e1 !important;
}

.dark .text-gray-600 {
    color: #94a3b8 !important;
}

.dark .text-gray-500 {
    color: #64748b !important;
}

.dark .text-gray-400 {
    color: #475569 !important;
}

.dark .text-gray-300 {
    color: #cbd5e1 !important;
}

.dark .border-gray-600 {
    border-color: #475569 !important;
}

.dark .border-gray-700 {
    border-color: #334155 !important;
}

.dark .shadow-sm {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.3) !important;
}

.dark .shadow-md {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3) !important;
}

.dark .shadow-xl {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5) !important;
}

.dark .hover-lift:hover {
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.dark .hero-pattern {
    background-image: 
        radial-gradient(circle at 25% 25%, rgba(233, 30, 99, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 75% 75%, rgba(0, 188, 212, 0.15) 0%, transparent 50%);
    background-color: #0f0f23;
}

.dark .gradient-bg-subtle {
    background: linear-gradient(135deg, rgba(233, 30, 99, 0.15) 0%, rgba(156, 39, 176, 0.15) 50%, rgba(0, 188, 212, 0.15) 100%);
    background-color: #16213e;
}

.dark .mobile-menu {
    background: #000000 !important;
    background-color: rgba(0, 0, 0, 0.9) !important;
    z-index: 60;
}

.dark .mobile-menu::before {
    background: #000000 !important;
}

.dark .btn-secondary {
    border-color: var(--thorium-primary);
    color: var(--thorium-primary);
}

.dark .btn-secondary:hover {
    background: var(--thorium-primary);
    color: white;
}

/* Theme toggle button */
.theme-toggle {
    background: transparent;
    border: 2px solid #e2e8f0;
    border-radius: 50%;
    padding: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.dark .theme-toggle {
    border-color: #475569;
}

.theme-toggle:hover {
    border-color: var(--thorium-primary);
    transform: scale(1.1);
}

.dark .theme-toggle:hover {
    border-color: var(--thorium-secondary);
}

.gradient-text {
    background: var(--thorium-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.gradient-bg {
    background: var(--thorium-gradient);
}

.gradient-bg-subtle {
    background: var(--thorium-gradient-subtle);
}

.glass-effect {
    backdrop-filter: blur(12px);
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.btn-primary {
    background: var(--thorium-gradient);
    border: none;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-primary:before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.btn-primary:hover:before {
    left: 100%;
}

.btn-secondary {
    background: transparent;
    border: 2px solid var(--thorium-primary);
    color: var(--thorium-primary);
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: var(--thorium-primary);
    color: white;
    transform: translateY(-2px);
}

.hero-pattern {
    background-image: 
        radial-gradient(circle at 25% 25%, rgba(233, 30, 99, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 75% 75%, rgba(0, 188, 212, 0.1) 0%, transparent 50%);
}

.mobile-menu {
    transform: translateX(100%);
    transition: transform 0.3s ease;
    background: #000000 !important;
    background-color: rgba(0, 0, 0, 0.9) !important;
    z-index: 60;
    position: fixed;
    top: 0;
    right: 0;
    width: 75%;
    height: 100vh;
    overflow-y: auto;
}

.mobile-menu.open {
    transform: translateX(0);
}

/* Ensure background renders in all scenarios */
.mobile-menu::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #000000;
    z-index: -1;
}

@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
        line-height: 1.2;
    }
    
    .hero-subtitle {
        font-size: 1.125rem;
    }
}

.animate-fade-in {
    animation: fadeInUp 0.8s ease forwards;
    opacity: 0;
    transform: translateY(30px);
}

.animate-delay-1 { animation-delay: 0.2s; }
.animate-delay-2 { animation-delay: 0.4s; }
.animate-delay-3 { animation-delay: 0.6s; }

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.code-block {
    background: #1e293b;
    border-radius: 12px;
    padding: 20px;
    color: #e2e8f0;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 14px;
    line-height: 1.6;
    overflow-x: auto;
}

.dark .code-block {
    background: #0f172a;
    border: 1px solid #334155;
}

/* Prose styling for page content */
.prose {
    color: #374151;
    max-width: none;
}

.prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
    color: #111827;
    font-weight: 700;
}

.prose p {
    margin-bottom: 1.25em;
}

.prose a {
    color: var(--thorium-primary);
    text-decoration: none;
    font-weight: 500;
}

.prose a:hover {
    text-decoration: underline;
}

.dark .prose {
    color: #d1d5db;
}

.dark .prose h1, .dark .prose h2, .dark .prose h3, .dark .prose h4, .dark .prose h5, .dark .prose h6 {
    color: #f9fafb;
}

.dark .prose a {
    color: var(--thorium-secondary);
}

/* Fixed header compensation */
body {
    padding-top: 80px;
}

/* Smooth scrolling for anchor links */
html {
    scroll-behavior: smooth;
}
</style>