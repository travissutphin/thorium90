<!-- ==================== HEADER SECTION START ==================== -->
<header class="fixed w-full top-0 z-50 glass-effect">
    <nav class="container mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <!-- Thorium90 Logo -->
                <div class="h-12 rounded-lg flex items-center justify-center">
                    <a href="/"><img 
                        src="/images/logos/header.png" 
                        alt="Thorium90 Logo" 
                        class="h-12 w-auto rounded-lg"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                    /></a>
                </div>
            </div>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="/about" class="text-gray-700 hover:text-gray-900 font-medium transition">About Us</a>
                <a href="/our-team" class="text-gray-700 hover:text-gray-900 font-medium transition">Our Team</a>
                <a href="/contact" class="text-gray-700 hover:text-gray-900 font-medium transition">Contact Us</a>
				<a href="/login" class="text-gray-700 hover:text-gray-900 font-medium transition">Admin</a>
                <button 
                    onclick="toggleDarkMode()"
                    class="theme-toggle"
                    aria-label="Toggle dark mode"
                >
                    <svg id="sun-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg id="moon-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
            </div>
            
            <!-- Mobile Menu Button -->
            <div class="md:hidden flex items-center space-x-3">
                <button 
                    onclick="toggleDarkMode()"
                    class="theme-toggle"
                    aria-label="Toggle dark mode"
                >
                    <svg id="sun-icon-mobile" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg id="moon-icon-mobile" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
                <button 
                    class="p-2"
                    onclick="setMobileMenuOpen(!mobileMenuOpen)"
                >
                    <svg id="menu-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="mobile-menu md:hidden bg-black bg-opacity-90 shadow-xl">
            <!-- Close Button -->
            <div class="flex justify-end p-4">
                <button 
                    onclick="setMobileMenuOpen(false)"
                    class="p-2 text-white hover:text-gray-300 rounded-lg hover:bg-gray-800 transition-colors"
                    aria-label="Close menu"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="px-6 pb-6 space-y-6">
                <a href="/about" class="block text-white hover:text-gray-300 font-medium">About Us</a>
                <a href="/our-team" class="block text-white hover:text-gray-300 font-medium">Our Team</a>
                <a href="/contact" class="block text-white hover:text-gray-300 font-medium">Contact Us</a>
                <div class="pt-4 border-t border-gray-600">
                    <button 
                        onclick="toggleDarkMode()"
                        class="flex items-center space-x-3 text-white hover:text-gray-300 font-medium w-full"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <span id="dark-mode-text">Dark Mode</span>
                    </button>
                </div>
                <a href="login"><button class="btn-primary w-full py-3 rounded-lg">
                   Admin
                </button></a>
            </div>
        </div>
    </nav>
</header>
<!-- ==================== HEADER SECTION END ==================== -->