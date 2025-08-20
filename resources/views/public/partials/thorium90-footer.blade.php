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
            </div>
            
            <div>
                <h4 class="font-semibold mb-4">Framework</h4>
                <ul class="space-y-3 text-gray-300">
                    <li><a href="#" class="hover:text-white transition">Documentation</a></li>
                    <li><a href="https://github.com/travissutphin/thorium90" target="_blank" class="hover:text-white transition">GitHub Repository</a></li>
                    <li><a href="#" class="hover:text-white transition">Plugin Marketplace</a></li>
                    <li><a href="#" class="hover:text-white transition">API Reference</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="font-semibold mb-4">Community</h4>
                <ul class="space-y-3 text-gray-300">
                    <li><a href="#" class="hover:text-white transition">Discord Community</a></li>
                    <li><a href="#" class="hover:text-white transition">Reddit Community</a></li>
					<li><a href="#" class="hover:text-white transition">Developer Blog</a></li>
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