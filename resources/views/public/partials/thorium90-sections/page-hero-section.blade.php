<!-- ==================== PAGE HERO SECTION START ==================== -->
<section class="hero-pattern pt-24 pb-16 md:pt-32 md:pb-24 overflow-hidden">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="animate-fade-in">
                <h1 class="hero-title text-4xl md:text-6xl font-bold mb-6 text-gray-900">
                   {{ $page->title }}
                </h1>
            </div>
            
            @if($page->meta_description)
            <div class="animate-fade-in animate-delay-1">
                <p class="hero-subtitle text-lg md:text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    {{ $page->meta_description }}
                </p>
            </div>
            @endif
            
            @if($page->content && trim(strip_tags($page->content)))
            <div class="animate-fade-in animate-delay-2">
                <div class="prose prose-lg max-w-none text-left">
                    {!! $page->content !!}
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
<!-- ==================== PAGE HERO SECTION END ==================== -->