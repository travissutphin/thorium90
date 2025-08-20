<!-- ==================== FOOTER SECTION START ==================== -->
<footer class="bg-gray-900 text-white">
    <div class="container mx-auto px-4 py-16">
        <div class="grid md:grid-cols-4 gap-8">
            <div class="md:col-span-2">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center">
                        <img 
                            src="/images/thorium90-logo.png" 
                            alt="Thorium90 Logo" 
                            class="w-10 h-10 rounded-lg"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        />
                        <div class="w-10 h-10 gradient-bg rounded-lg items-center justify-center hidden">
                            <span class="text-white font-bold text-sm">T90</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">{{ config('app.name') }}</h3>
                        <p class="text-sm text-gray-400">Laravel 12 Rapid Development</p>
                    </div>
                </div>
                <p class="text-gray-300 mb-6 max-w-md">
                    The ultimate Laravel 12 framework for agencies and developers. 
                    Build exceptional client projects with unprecedented speed and quality.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0 0V3"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <div>
                <h4 class="font-semibold mb-4">Framework</h4>
                <ul class="space-y-3 text-gray-300">
                    <li><a href="#" class="hover:text-white transition">Documentation</a></li>
                    <li><a href="#" class="hover:text-white transition">GitHub Repository</a></li>
                    <li><a href="#" class="hover:text-white transition">Plugin Marketplace</a></li>
                    <li><a href="#" class="hover:text-white transition">API Reference</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="font-semibold mb-4">Community</h4>
                <ul class="space-y-3 text-gray-300">
                    <li><a href="#" class="hover:text-white transition">Discord Community</a></li>
                    <li><a href="#" class="hover:text-white transition">Developer Blog</a></li>
                    <li><a href="#" class="hover:text-white transition">Contributing Guide</a></li>
					<li><a href="the-team" class="hover:text-white transition">The Team</a></li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-gray-700 mt-12 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">
                    © {{ date('Y') }} {{ config('app.name') }}. Open source framework under MIT License.
                </p>
                <div class="flex space-x-6 mt-4 md:mt-0 text-sm">
                    <a href="#" class="text-gray-400 hover:text-white transition">MIT License</a>
                    <a href="#" class="text-gray-400 hover:text-white transition">Contributing</a>
                    <a href="#" class="text-gray-400 hover:text-white transition">Changelog</a>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- ==================== FOOTER SECTION END ==================== -->