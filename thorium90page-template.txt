import React from 'react';
import { 
    Zap, 
    Shield, 
    Rocket, 
    CheckCircle, 
    ArrowRight, 
    Star,
    Users,
    Globe,
    ChevronRight,
    Menu,
    X,
    Sun,
    Moon,
    Code,
    Layers,
    Gauge,
    Puzzle,
    Database,
    Smartphone,
    Clock,
    Target
} from 'lucide-react';

interface TemplateProps {
    content?: {
        title?: string;
        excerpt?: string;
        content?: string;
    };
    theme?: string;
    config?: {
        custom_class?: string;
        page_scripts?: string;
    };
}

/**
 * Thorium90 Home Page Template
 * 
 * Marketing site for Thorium90 - Laravel 12 rapid development framework
 * Positions as a supercharged boilerplate for client projects
 */
export const HomePage: React.FC<TemplateProps> = ({
    content = {},
    theme = 'default',
    config = {}
}) => {
    const [mobileMenuOpen, setMobileMenuOpen] = React.useState(false);
    const [darkMode, setDarkMode] = React.useState(false);

    React.useEffect(() => {
        // Check for saved theme preference or default to light mode
        const savedTheme = localStorage.getItem('thorium90-theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            setDarkMode(true);
            document.documentElement.classList.add('dark');
        }
    }, []);

    const toggleDarkMode = () => {
        const newDarkMode = !darkMode;
        setDarkMode(newDarkMode);
        
        if (newDarkMode) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('thorium90-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('thorium90-theme', 'light');
        }
    };

    return (
        <>
            {/* ==================== CUSTOM STYLES START ==================== */}
            <style dangerouslySetInnerHTML={{
                __html: `
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
                    background-color: #1a1a2e;
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
                }
                
                .mobile-menu.open {
                    transform: translateX(0);
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
                `
            }} />
            {/* ==================== CUSTOM STYLES END ==================== */}

            <div className={`home-template ${config?.custom_class || ''}`} data-theme={theme}>
                
                {/* ==================== HEADER SECTION START ==================== */}
                <header className="fixed w-full top-0 z-50 glass-effect">
                    <nav className="container mx-auto px-4 py-4">
                        <div className="flex justify-between items-center">
                            <div className="flex items-center space-x-3">
                                {/* Thorium90 Logo */}
                                <div className="w-10 h-10 rounded-lg flex items-center justify-center">
                                    <img 
                                        src="/images/thorium90-logo.png" 
                                        alt="Thorium90 Logo" 
                                        className="w-10 h-10 rounded-lg"
                                        onError={(e) => {
                                            e.currentTarget.style.display = 'none';
                                            e.currentTarget.nextElementSibling.style.display = 'flex';
                                        }}
                                    />
                                    <div className="w-10 h-10 gradient-bg rounded-lg items-center justify-center hidden">
                                        <span className="text-white font-bold text-sm">T90</span>
                                    </div>
                                </div>
                                <div>
                                    <h1 className="text-xl font-bold gradient-text">Thorium90</h1>
                                    <p className="text-xs text-gray-500 -mt-1">Laravel 12 Rapid Development</p>
                                </div>
                            </div>
                            
                            {/* Desktop Navigation */}
                            <div className="hidden md:flex items-center space-x-8">
                                <a href="#features" className="text-gray-700 hover:text-gray-900 font-medium transition">Features</a>
                                <a href="#tech-stack" className="text-gray-700 hover:text-gray-900 font-medium transition">Tech Stack</a>
                                <a href="#packages" className="text-gray-700 hover:text-gray-900 font-medium transition">Packages</a>
                                <a href="#showcase" className="text-gray-700 hover:text-gray-900 font-medium transition">Showcase</a>
                                <button 
                                    onClick={toggleDarkMode}
                                    className="theme-toggle"
                                    aria-label="Toggle dark mode"
                                >
                                    {darkMode ? <Sun size={20} /> : <Moon size={20} />}
                                </button>
                                <button className="btn-primary px-6 py-2 rounded-lg">
                                    View Demo
                                </button>
                            </div>
                            
                            {/* Mobile Menu Button */}
                            <div className="md:hidden flex items-center space-x-3">
                                <button 
                                    onClick={toggleDarkMode}
                                    className="theme-toggle"
                                    aria-label="Toggle dark mode"
                                >
                                    {darkMode ? <Sun size={20} /> : <Moon size={20} />}
                                </button>
                                <button 
                                    className="p-2"
                                    onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                                >
                                    {mobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
                                </button>
                            </div>
                        </div>
                        
                        {/* Mobile Navigation */}
                        <div className={`mobile-menu md:hidden fixed inset-y-0 right-0 w-64 bg-white shadow-xl ${mobileMenuOpen ? 'open' : ''}`}>
                            <div className="p-6 pt-16 space-y-6">
                                <a href="#features" className="block text-gray-700 hover:text-gray-900 font-medium">Features</a>
                                <a href="#tech-stack" className="block text-gray-700 hover:text-gray-900 font-medium">Tech Stack</a>
                                <a href="#packages" className="block text-gray-700 hover:text-gray-900 font-medium">Packages</a>
                                <a href="#showcase" className="block text-gray-700 hover:text-gray-900 font-medium">Showcase</a>
                                <div className="pt-4 border-t border-gray-200">
                                    <button 
                                        onClick={toggleDarkMode}
                                        className="flex items-center space-x-3 text-gray-700 hover:text-gray-900 font-medium w-full"
                                    >
                                        {darkMode ? <Sun size={20} /> : <Moon size={20} />}
                                        <span>{darkMode ? 'Light Mode' : 'Dark Mode'}</span>
                                    </button>
                                </div>
                                <button className="btn-primary w-full py-3 rounded-lg">
                                    View Demo
                                </button>
                            </div>
                        </div>
                    </nav>
                </header>
                {/* ==================== HEADER SECTION END ==================== */}

                {/* ==================== HERO SECTION START ==================== */}
                <section className="hero-pattern pt-24 pb-16 md:pt-32 md:pb-24 overflow-hidden">
                    <div className="container mx-auto px-4">
                        <div className="max-w-4xl mx-auto text-center">
                            <div className="animate-fade-in">
                                <h1 className="hero-title text-4xl md:text-6xl font-bold mb-6 text-gray-900">
                                    Laravel 12 <span className="gradient-text">Supercharged</span>
                                    <br />
                                    for Rapid Development
                                </h1>
                            </div>
                            
                            <div className="animate-fade-in animate-delay-1">
                                <p className="hero-subtitle text-lg md:text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                                    Thorium90 is the ultimate Laravel 12 boilerplate - a production-ready CMS framework designed for agencies and developers who build custom solutions for clients. Ship faster, scale smarter.
                                </p>
                            </div>
                            
                            <div className="animate-fade-in animate-delay-2">
                                <div className="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                                    <button className="btn-primary px-8 py-4 rounded-lg text-lg flex items-center justify-center space-x-2 group">
                                        <span>Explore Demo</span>
                                        <ArrowRight size={20} className="group-hover:translate-x-1 transition-transform" />
                                    </button>
                                    <button className="btn-secondary px-8 py-4 rounded-lg text-lg">
                                        GitHub Repository
                                    </button>
                                </div>
                            </div>
                            
                            <div className="animate-fade-in animate-delay-3">
                                <div className="flex flex-wrap justify-center items-center gap-8 text-sm text-gray-500">
                                    <div className="flex items-center space-x-2">
                                        <CheckCircle size={16} className="text-green-500" />
                                        <span>Laravel 12 Ready</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <CheckCircle size={16} className="text-green-500" />
                                        <span>Modular Architecture</span>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <CheckCircle size={16} className="text-green-500" />
                                        <span>Production Tested</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                {/* ==================== HERO SECTION END ==================== */}

                {/* ==================== FEATURES SECTION START ==================== */}
                <section id="features" className="py-16 md:py-24 bg-gray-50">
                    <div className="container mx-auto px-4">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl md:text-4xl font-bold mb-4 text-gray-900">
                                Built for <span className="gradient-text">Professional Development</span>
                            </h2>
                            <p className="text-xl text-gray-600 max-w-3xl mx-auto">
                                Everything you need to build modern web applications and websites for your clients. 
                                From simple brochure sites to complex SaaS platforms.
                            </p>
                        </div>
                        
                        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {/* Feature 1 */}
                            <div className="bg-white rounded-2xl p-8 shadow-sm hover-lift">
                                <div className="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mb-6">
                                    <Rocket className="w-8 h-8 text-white" />
                                </div>
                                <h3 className="text-xl font-bold mb-4 text-gray-900">Rapid Deployment</h3>
                                <p className="text-gray-600 mb-4">
                                    Launch client projects in hours, not weeks. Pre-configured with authentication, 
                                    admin panel, and essential features ready to go.
                                </p>
                                <div className="flex items-center space-x-2 text-sm text-gray-500">
                                    <Clock size={16} />
                                    <span>80% faster project setup</span>
                                </div>
                            </div>
                            
                            {/* Feature 2 */}
                            <div className="bg-white rounded-2xl p-8 shadow-sm hover-lift">
                                <div className="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mb-6">
                                    <Puzzle className="w-8 h-8 text-white" />
                                </div>
                                <h3 className="text-xl font-bold mb-4 text-gray-900">Modular Architecture</h3>
                                <p className="text-gray-600 mb-4">
                                    Add features as needed with our plugin system. Blog, ecommerce, 
                                    multi-tenancy - install only what each client requires.
                                </p>
                                <div className="flex items-center space-x-2 text-sm text-gray-500">
                                    <Target size={16} />
                                    <span>Lean & optimized builds</span>
                                </div>
                            </div>
                            
                            {/* Feature 3 */}
                            <div className="bg-white rounded-2xl p-8 shadow-sm hover-lift">
                                <div className="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mb-6">
                                    <Shield className="w-8 h-8 text-white" />
                                </div>
                                <h3 className="text-xl font-bold mb-4 text-gray-900">Enterprise Ready</h3>
                                <p className="text-gray-600 mb-4">
                                    Security-first design with role-based permissions, 2FA, 
                                    and compliance features built-in for demanding clients.
                                </p>
                                <div className="flex items-center space-x-2 text-sm text-gray-500">
                                    <Shield size={16} />
                                    <span>SOC2 compliant foundation</span>
                                </div>
                            </div>

                            {/* Feature 4 */}
                            <div className="bg-white rounded-2xl p-8 shadow-sm hover-lift">
                                <div className="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mb-6">
                                    <Smartphone className="w-8 h-8 text-white" />
                                </div>
                                <h3 className="text-xl font-bold mb-4 text-gray-900">Multi-Frontend Support</h3>
                                <p className="text-gray-600 mb-4">
                                    React, Vue, or Livewire frontends. Build websites, web apps, 
                                    or headless APIs. One backend, infinite possibilities.
                                </p>
                                <div className="flex items-center space-x-2 text-sm text-gray-500">
                                    <Layers size={16} />
                                    <span>Flexible architecture</span>
                                </div>
                            </div>

                            {/* Feature 5 */}
                            <div className="bg-white rounded-2xl p-8 shadow-sm hover-lift">
                                <div className="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mb-6">
                                    <Database className="w-8 h-8 text-white" />
                                </div>
                                <h3 className="text-xl font-bold mb-4 text-gray-900">Content Management</h3>
                                <p className="text-gray-600 mb-4">
                                    Intuitive admin interface with drag-drop page builder, 
                                    media management, and SEO optimization built-in.
                                </p>
                                <div className="flex items-center space-x-2 text-sm text-gray-500">
                                    <Code size={16} />
                                    <span>Developer & client friendly</span>
                                </div>
                            </div>

                            {/* Feature 6 */}
                            <div className="bg-white rounded-2xl p-8 shadow-sm hover-lift">
                                <div className="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center mb-6">
                                    <Gauge className="w-8 h-8 text-white" />
                                </div>
                                <h3 className="text-xl font-bold mb-4 text-gray-900">Performance Optimized</h3>
                                <p className="text-gray-600 mb-4">
                                    Redis caching, queue management, CDN integration, 
                                    and database optimization out of the box.
                                </p>
                                <div className="flex items-center space-x-2 text-sm text-gray-500">
                                    <Gauge size={16} />
                                    <span>Sub-second load times</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                {/* ==================== FEATURES SECTION END ==================== */}

                {/* ==================== TECH STACK SECTION START ==================== */}
                <section id="tech-stack" className="py-16 md:py-24">
                    <div className="container mx-auto px-4">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl md:text-4xl font-bold mb-4 text-gray-900">
                                Modern <span className="gradient-text">Tech Stack</span>
                            </h2>
                            <p className="text-xl text-gray-600 max-w-3xl mx-auto">
                                Built on Laravel 12 with the latest technologies and best practices. 
                                Production-ready packages carefully selected for reliability and performance.
                            </p>
                        </div>

                        <div className="grid md:grid-cols-2 gap-12 items-center">
                            <div>
                                <h3 className="text-2xl font-bold mb-6 text-gray-900">Core Foundation</h3>
                                <div className="space-y-4">
                                    <div className="flex items-center space-x-4">
                                        <div className="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                            <Code className="w-6 h-6 text-red-600" />
                                        </div>
                                        <div>
                                            <h4 className="font-semibold text-gray-900">Laravel 12</h4>
                                            <p className="text-gray-600">Latest PHP framework with built-in features</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <Database className="w-6 h-6 text-blue-600" />
                                        </div>
                                        <div>
                                            <h4 className="font-semibold text-gray-900">Filament Admin</h4>
                                            <p className="text-gray-600">Modern admin panel with CRUD generation</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                            <Shield className="w-6 h-6 text-green-600" />
                                        </div>
                                        <div>
                                            <h4 className="font-semibold text-gray-900">Spatie Ecosystem</h4>
                                            <p className="text-gray-600">Permissions, settings, media library & more</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-4">
                                        <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                            <Layers className="w-6 h-6 text-purple-600" />
                                        </div>
                                        <div>
                                            <h4 className="font-semibold text-gray-900">Frontend Flexibility</h4>
                                            <p className="text-gray-600">React, Vue, or Livewire - your choice</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="bg-gray-900 rounded-2xl p-8">
                                <h3 className="text-xl font-bold text-white mb-4">Quick Start Example</h3>
                                <div className="code-block">
                                    <div className="text-green-400"># Clone and setup Thorium90</div>
                                    <div className="text-blue-400">git clone thorium90-cms my-project</div>
                                    <div className="text-blue-400">cd my-project</div>
                                    <div className="text-blue-400">./install.sh</div>
                                    <br />
                                    <div className="text-green-400"># Add blog functionality</div>
                                    <div className="text-blue-400">php artisan thorium:install blog</div>
                                    <br />
                                    <div className="text-green-400"># Deploy to production</div>
                                    <div className="text-blue-400">php artisan thorium:deploy</div>
                                    <br />
                                    <div className="text-yellow-400"># Client-ready in minutes! 🚀</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                {/* ==================== TECH STACK SECTION END ==================== */}

                {/* ==================== PACKAGES SECTION START ==================== */}
                <section id="packages" className="py-16 md:py-24 bg-gray-50">
                    <div className="container mx-auto px-4">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl md:text-4xl font-bold mb-4 text-gray-900">
                                Plugin <span className="gradient-text">Ecosystem</span>
                            </h2>
                            <p className="text-xl text-gray-600 max-w-3xl mx-auto">
                                Modular packages that you can mix and match for each client project. 
                                Install only what you need to keep your applications lean and optimized.
                            </p>
                        </div>

                        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {/* Core Package */}
                            <div className="bg-white rounded-2xl p-8 shadow-sm hover-lift border-2 border-pink-200">
                                <div className="flex items-center justify-between mb-4">
                                    <div className="w-16 h-16 gradient-bg rounded-2xl flex items-center justify-center">
                                        <Zap className="w-8 h-8 text-white" />
                                    </div>
                                    <span className="bg-pink-100 text-pink-800 px-3 py-1 rounded-full text-sm font-semibold">CORE</span>
                                </div>
                                <h3 className="text-xl font-bold mb-4 text-gray-900">Thorium90 Core</h3>
                                <p className="text-gray-600 mb-4">
                                    Base CMS with authentication, admin panel, user management, 
                                    and content pages. The foundation for every project.
                                </p>
                                <ul className="text-sm text-gray-500 space-y-2">
                                    <li>• Multi-role authentication</li>
                                    <li>• Filament admin interface</li>
                                    <li>• Page & content management</li>
                                    <li>• SEO optimization tools</li>
                                </ul>
                            </div>

                            {/* Blog Package */}
                            <div className="bg-white rounded-2xl p-8 shadow-sm hover-lift">
                                <div className="flex items-center justify-between mb-4">
                                    <div className="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center">
                                        <Users className="w-8 h-8 text-white" />
                                    </div>
                                    <span className="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">PLUGIN</span>
                                </div>
                                <h3 className="text-xl font-bold mb-4 text-gray-900">Blog Module</h3>
                                <p className="text-gray-600 mb-4">
                                    Complete blogging solution with categories, tags, comments, 
                                    and social sharing. Perfect for content marketing sites.
                                </p>
                                <ul className="text-sm text-gray-500 space-y-2">
                                    <li>• Rich text editor</li>
                                    <li>• Category management</li>
                                    <li>• Comment system</li>
                                    <li>• RSS feeds</li>
                                </ul>
                            </div>

                            {/* Ecommerce Package */}
                            <div className="bg-white rounded-2xl p-8 shadow-sm hover-lift">
                                <div className="flex items-center justify-between mb-4">
                                    <div className="w-16 h-16 bg-green-600 rounded-2xl flex items-center justify-center">
                                        <Globe className="w-8 h-8 text-white" />
                                    </div>
                                    <span className="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">PLUGIN</span>
                                </div>
                                <h3 className="text-xl font-bold mb-4 text-gray-900">Ecommerce</h3>
                                <p className="text-gray-600 mb-4">
                                    Full-featured online store with inventory management, 
                                    payment processing, and order fulfillment.
                                </p>
                                <ul className="text-sm text-gray-500 space-y-2">
                                    <li>• Product catalog</li>
                                    <li>• Shopping cart & checkout</li>
                                    <li>• Payment integrations</li>
                                    <li>• Order management</li>
                                </ul>
                            </div>

                            {/* Multi-Tenancy Package */}
                            <div className="bg-white rounded-2xl p-8 shadow-sm hover-lift">
                                <div className="flex items-center justify-between mb-4">
                                    <div className="w-16 h-16 bg-purple-600 rounded-2xl flex items-center justify-center">
                                        <Layers className="w-8 h-8 text-white" />
                                    </div>
                                    <span className="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-semibold">PLUGIN</span>
                                </div>
                                <h3 className="text-xl font-bold mb-4 text-gray-900">Multi-Tenancy</h3>
                                <p className="text-gray-600 mb-4">
                                    Build SaaS applications with isolated tenant data, 
                                    subdomain routing, and subscription management.
                                </p>
                                <ul className="text-sm text-gray-500 space-y-2">
                                    <li>• Tenant isolation</li>
                                    <li>• Subdomain routing</li>
                                    <li>• Subscription billing</li>
                                    <li>• Usage analytics</li>
                                </ul>
                            </div>

                            {/* Forms Package */}
                            <div className="bg-white rounded-2xl p-8 shadow-sm hover-lift">
                                <div className="flex items-center justify-between mb-4">
                                    <div className="w-16 h-16 bg-orange-600 rounded-2xl flex items-center justify-center">
                                        <Code className="w-8 h-8 text-white" />
                                    </div>
                                    <span className="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-semibold">PLUGIN</span>
                                </div>
                                <h3 className="text-xl font-bold mb-4 text-gray-900">Advanced Forms</h3>
                                <p className="text-gray-600 mb-4">
                                    Drag-and-drop form builder with conditional logic, 
                                    file uploads, and integration capabilities.
                                </p>
                                <ul className="text-sm text-gray-500 space-y-2">
                                    <li>• Visual form builder</li>
                                    <li>• Conditional fields</li>
                                    <li>• File upload handling</li>
                                    <li>• Email notifications</li>
                                </ul>
                            </div>

                            {/* Analytics Package */}
                            <div className="bg-white rounded-2xl p-8 shadow-sm hover-lift">
                                <div className="flex items-center justify-between mb-4">
                                    <div className="w-16 h-16 bg-teal-600 rounded-2xl flex items-center justify-center">
                                        <Gauge className="w-8 h-8 text-white" />
                                    </div>
                                    <span className="bg-teal-100 text-teal-800 px-3 py-1 rounded-full text-sm font-semibold">PLUGIN</span>
                                </div>
                                <h3 className="text-xl font-bold mb-4 text-gray-900">Analytics Pro</h3>
                                <p className="text-gray-600 mb-4">
                                    Comprehensive analytics dashboard with user tracking, 
                                    conversion funnels, and custom reporting.
                                </p>
                                <ul className="text-sm text-gray-500 space-y-2">
                                    <li>• Real-time analytics</li>
                                    <li>• Conversion tracking</li>
                                    <li>• Custom dashboards</li>
                                    <li>• Automated reports</li>
                                </ul>
                            </div>
                        </div>

                        <div className="text-center mt-12">
                            <p className="text-gray-600 mb-6">
                                More packages in development: Events, Bookings, Learning Management, 
                                Real Estate, Directory, and Custom Business Solutions.
                            </p>
                            <button className="btn-primary px-8 py-3 rounded-lg">
                                Request Custom Package
                            </button>
                        </div>
                    </div>
                </section>
                {/* ==================== PACKAGES SECTION END ==================== */}

                {/* ==================== SHOWCASE SECTION START ==================== */}
                <section id="showcase" className="py-16 md:py-24">
                    <div className="container mx-auto px-4">
                        <div className="text-center mb-16">
                            <h2 className="text-3xl md:text-4xl font-bold mb-4 text-gray-900">
                                One Framework, <span className="gradient-text">Infinite Possibilities</span>
                            </h2>
                            <p className="text-xl text-gray-600 max-w-3xl mx-auto">
                                See how the same Thorium90 foundation adapts to completely different business models. 
                                Mix and match packages to create exactly what your clients need.
                            </p>
                        </div>

                        {/* Industry Examples in Alternating Layout */}
                        <div className="space-y-16">
                            {/* Row 1 - Corporate & SaaS */}
                            <div className="grid md:grid-cols-2 gap-12 items-center">
                                <div className="order-2 md:order-1">
                                    <div className="bg-white rounded-2xl p-8 shadow-xl hover-lift border-l-4 border-blue-500">
                                        <div className="flex items-start space-x-4">
                                            <div className="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                                                <Globe className="w-8 h-8 text-white" />
                                            </div>
                                            <div>
                                                <h3 className="text-2xl font-bold mb-3 text-gray-900">Corporate Websites</h3>
                                                <p className="text-gray-600 mb-4">
                                                    Professional business sites with elegant design, team showcases, 
                                                    service pages, and integrated contact systems.
                                                </p>
                                                <div className="flex flex-wrap gap-2 mb-4">
                                                    <span className="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">Core CMS</span>
                                                    <span className="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">Forms</span>
                                                    <span className="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">Analytics</span>
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    ⚡ <strong>Setup time:</strong> 2-4 hours • <strong>Perfect for:</strong> Professional services, consultancies, law firms
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className="order-1 md:order-2">
                                    <div className="bg-white rounded-2xl p-8 shadow-xl hover-lift border-l-4 border-purple-500">
                                        <div className="flex items-start space-x-4">
                                            <div className="w-16 h-16 bg-purple-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                                                <Rocket className="w-8 h-8 text-white" />
                                            </div>
                                            <div>
                                                <h3 className="text-2xl font-bold mb-3 text-gray-900">SaaS Platforms</h3>
                                                <p className="text-gray-600 mb-4">
                                                    Multi-tenant applications with user dashboards, subscription billing, 
                                                    usage tracking, and comprehensive admin controls.
                                                </p>
                                                <div className="flex flex-wrap gap-2 mb-4">
                                                    <span className="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">Core CMS</span>
                                                    <span className="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">Multi-Tenancy</span>
                                                    <span className="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-medium">Analytics Pro</span>
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    ⚡ <strong>Setup time:</strong> 1-2 days • <strong>Perfect for:</strong> B2B tools, productivity apps, management systems
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Row 2 - E-commerce & Publishing */}
                            <div className="grid md:grid-cols-2 gap-12 items-center">
                                <div>
                                    <div className="bg-white rounded-2xl p-8 shadow-xl hover-lift border-l-4 border-green-500">
                                        <div className="flex items-start space-x-4">
                                            <div className="w-16 h-16 bg-green-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                                                <Database className="w-8 h-8 text-white" />
                                            </div>
                                            <div>
                                                <h3 className="text-2xl font-bold mb-3 text-gray-900">E-commerce Stores</h3>
                                                <p className="text-gray-600 mb-4">
                                                    Complete online stores with product catalogs, inventory management, 
                                                    payment processing, and order fulfillment systems.
                                                </p>
                                                <div className="flex flex-wrap gap-2 mb-4">
                                                    <span className="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">Core CMS</span>
                                                    <span className="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">E-commerce</span>
                                                    <span className="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">Analytics</span>
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    ⚡ <strong>Setup time:</strong> 4-6 hours • <strong>Perfect for:</strong> Retail stores, product brands, marketplace sellers
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div className="bg-white rounded-2xl p-8 shadow-xl hover-lift border-l-4 border-orange-500">
                                        <div className="flex items-start space-x-4">
                                            <div className="w-16 h-16 bg-orange-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                                                <Users className="w-8 h-8 text-white" />
                                            </div>
                                            <div>
                                                <h3 className="text-2xl font-bold mb-3 text-gray-900">Content Publishers</h3>
                                                <p className="text-gray-600 mb-4">
                                                    News sites, magazines, and blogs with advanced content management, 
                                                    author profiles, subscriber systems, and monetization features.
                                                </p>
                                                <div className="flex flex-wrap gap-2 mb-4">
                                                    <span className="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">Core CMS</span>
                                                    <span className="bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm font-medium">Blog Pro</span>
                                                    <span className="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">Forms</span>
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    ⚡ <strong>Setup time:</strong> 3-5 hours • <strong>Perfect for:</strong> Media companies, content creators, news organizations
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Row 3 - Internal Tools & Portfolios */}
                            <div className="grid md:grid-cols-2 gap-12 items-center">
                                <div className="order-2 md:order-1">
                                    <div className="bg-white rounded-2xl p-8 shadow-xl hover-lift border-l-4 border-teal-500">
                                        <div className="flex items-start space-x-4">
                                            <div className="w-16 h-16 bg-teal-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                                                <Code className="w-8 h-8 text-white" />
                                            </div>
                                            <div>
                                                <h3 className="text-2xl font-bold mb-3 text-gray-900">Internal Tools</h3>
                                                <p className="text-gray-600 mb-4">
                                                    Employee portals, project management systems, HR platforms, 
                                                    and business process automation tools for internal operations.
                                                </p>
                                                <div className="flex flex-wrap gap-2 mb-4">
                                                    <span className="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">Core CMS</span>
                                                    <span className="bg-teal-100 text-teal-800 px-3 py-1 rounded-full text-sm font-medium">Advanced Forms</span>
                                                    <span className="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">Custom Modules</span>
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    ⚡ <strong>Setup time:</strong> 6-8 hours • <strong>Perfect for:</strong> Large enterprises, government, educational institutions
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div className="order-1 md:order-2">
                                    <div className="bg-white rounded-2xl p-8 shadow-xl hover-lift border-l-4 border-pink-500">
                                        <div className="flex items-start space-x-4">
                                            <div className="w-16 h-16 bg-pink-600 rounded-2xl flex items-center justify-center flex-shrink-0">
                                                <Star className="w-8 h-8 text-white" />
                                            </div>
                                            <div>
                                                <h3 className="text-2xl font-bold mb-3 text-gray-900">Creative Portfolios</h3>
                                                <p className="text-gray-600 mb-4">
                                                    Stunning portfolio sites with media galleries, project showcases, 
                                                    client testimonials, and integrated contact systems.
                                                </p>
                                                <div className="flex flex-wrap gap-2 mb-4">
                                                    <span className="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">Core CMS</span>
                                                    <span className="bg-pink-100 text-pink-800 px-3 py-1 rounded-full text-sm font-medium">Media Pro</span>
                                                    <span className="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">Forms</span>
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    ⚡ <strong>Setup time:</strong> 2-3 hours • <strong>Perfect for:</strong> Designers, photographers, agencies, freelancers
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Call to Action */}
                        <div className="text-center mt-16">
                            <div className="bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-8">
                                <h3 className="text-2xl font-bold mb-4 text-gray-900">
                                    Can't Find Your Use Case?
                                </h3>
                                <p className="text-gray-600 mb-6 max-w-2xl mx-auto">
                                    Thorium90's modular architecture means you can create custom combinations 
                                    for any industry or business model. Start with the core and build exactly what you need.
                                </p>
                                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                                    <button className="btn-primary px-6 py-3 rounded-lg">
                                        Explore All Packages
                                    </button>
                                    <button className="btn-secondary px-6 py-3 rounded-lg">
                                        Request Custom Solution
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                {/* ==================== SHOWCASE SECTION END ==================== */}

                {/* ==================== DEVELOPMENT STATS SECTION START ==================== */}
                <section className="py-16 gradient-bg-subtle">
                    <div className="container mx-auto px-4">
                        <div className="text-center mb-12">
                            <h2 className="text-2xl md:text-3xl font-bold mb-4 text-gray-900">
                                Why Agencies Choose Thorium90
                            </h2>
                        </div>
                        
                        <div className="grid md:grid-cols-4 gap-8 text-center">
                            <div>
                                <div className="text-4xl font-bold gradient-text mb-2">80%</div>
                                <p className="text-gray-600">Faster Project Setup</p>
                                <p className="text-sm text-gray-500 mt-1">vs. building from scratch</p>
                            </div>
                            <div>
                                <div className="text-4xl font-bold gradient-text mb-2">50+</div>
                                <p className="text-gray-600">Pre-built Components</p>
                                <p className="text-sm text-gray-500 mt-1">Ready to customize</p>
                            </div>
                            <div>
                                <div className="text-4xl font-bold gradient-text mb-2">24/7</div>
                                <p className="text-gray-600">Production Ready</p>
                                <p className="text-sm text-gray-500 mt-1">Enterprise-grade reliability</p>
                            </div>
                            <div>
                                <div className="text-4xl font-bold gradient-text mb-2">∞</div>
                                <p className="text-gray-600">Scalability</p>
                                <p className="text-sm text-gray-500 mt-1">From startup to enterprise</p>
                            </div>
                        </div>
                    </div>
                </section>
                {/* ==================== DEVELOPMENT STATS SECTION END ==================== */}

                {/* ==================== CTA SECTION START ==================== */}
                <section className="py-16 md:py-24 gradient-bg">
                    <div className="container mx-auto px-4 text-center">
                        <div className="max-w-3xl mx-auto">
                            <h2 className="text-3xl md:text-4xl font-bold text-white mb-6">
                                Ready to Accelerate Your Development?
                            </h2>
                            <p className="text-xl text-white/90 mb-8">
                                Join the growing community of developers and agencies using Thorium90 
                                to deliver exceptional client projects faster than ever before.
                            </p>
                            <div className="flex flex-col sm:flex-row gap-4 justify-center">
                                <button className="bg-white text-gray-900 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 transition flex items-center justify-center space-x-2 group">
                                    <span>Download Thorium90</span>
                                    <ArrowRight size={20} className="group-hover:translate-x-1 transition-transform" />
                                </button>
                                <button className="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-white hover:text-gray-900 transition">
                                    View Documentation
                                </button>
                            </div>
                            <p className="text-sm text-white/75 mt-6">
                                Open source framework • MIT License • Commercial use allowed
                            </p>
                        </div>
                    </div>
                </section>
                {/* ==================== CTA SECTION END ==================== */}

                {/* ==================== FOOTER SECTION START ==================== */}
                <footer className="bg-gray-900 text-white">
                    <div className="container mx-auto px-4 py-16">
                        <div className="grid md:grid-cols-4 gap-8">
                            <div className="md:col-span-2">
                                <div className="flex items-center space-x-3 mb-4">
                                    <div className="w-10 h-10 rounded-lg flex items-center justify-center">
                                        <img 
                                            src="/images/thorium90-logo.png" 
                                            alt="Thorium90 Logo" 
                                            className="w-10 h-10 rounded-lg"
                                            onError={(e) => {
                                                e.currentTarget.style.display = 'none';
                                                e.currentTarget.nextElementSibling.style.display = 'flex';
                                            }}
                                        />
                                        <div className="w-10 h-10 gradient-bg rounded-lg items-center justify-center hidden">
                                            <span className="text-white font-bold text-sm">T90</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 className="text-xl font-bold">Thorium90</h3>
                                        <p className="text-sm text-gray-400">Laravel 12 Rapid Development</p>
                                    </div>
                                </div>
                                <p className="text-gray-300 mb-6 max-w-md">
                                    The ultimate Laravel 12 framework for agencies and developers. 
                                    Build exceptional client projects with unprecedented speed and quality.
                                </p>
                                <div className="flex space-x-4">
                                    <a href="#" className="text-gray-400 hover:text-white transition">
                                        <Globe size={20} />
                                    </a>
                                    <a href="#" className="text-gray-400 hover:text-white transition">
                                        <Users size={20} />
                                    </a>
                                    <a href="#" className="text-gray-400 hover:text-white transition">
                                        <Star size={20} />
                                    </a>
                                </div>
                            </div>
                            
                            <div>
                                <h4 className="font-semibold mb-4">Framework</h4>
                                <ul className="space-y-3 text-gray-300">
                                    <li><a href="#" className="hover:text-white transition">Documentation</a></li>
                                    <li><a href="#" className="hover:text-white transition">GitHub Repository</a></li>
                                    <li><a href="#" className="hover:text-white transition">Plugin Marketplace</a></li>
                                    <li><a href="#" className="hover:text-white transition">API Reference</a></li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 className="font-semibold mb-4">Community</h4>
                                <ul className="space-y-3 text-gray-300">
                                    <li><a href="#" className="hover:text-white transition">Discord Community</a></li>
                                    <li><a href="#" className="hover:text-white transition">Developer Blog</a></li>
                                    <li><a href="#" className="hover:text-white transition">Showcase Gallery</a></li>
                                    <li><a href="#" className="hover:text-white transition">Contributing Guide</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <div className="border-t border-gray-700 mt-12 pt-8">
                            <div className="flex flex-col md:flex-row justify-between items-center">
                                <p className="text-gray-400 text-sm">
                                    © 2025 Thorium90. Open source framework under MIT License.
                                </p>
                                <div className="flex space-x-6 mt-4 md:mt-0 text-sm">
                                    <a href="#" className="text-gray-400 hover:text-white transition">MIT License</a>
                                    <a href="#" className="text-gray-400 hover:text-white transition">Contributing</a>
                                    <a href="#" className="text-gray-400 hover:text-white transition">Changelog</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
                {/* ==================== FOOTER SECTION END ==================== */}

            </div>

            {/* ==================== CUSTOM SCRIPTS START ==================== */}
            {config?.page_scripts && (
                <script dangerouslySetInnerHTML={{ __html: config.page_scripts }} />
            )}
            {/* ==================== CUSTOM SCRIPTS END ==================== */}
        </>
    );
};

export default HomePage;