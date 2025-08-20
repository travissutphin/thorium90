@extends('public.layouts.thorium90-template')

@section('content')
<main class="min-h-screen">
    <!-- Page Header -->
    <section class="bg-gradient-to-r from-purple-600 to-pink-600 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ $page->title }}</h1>
                @if($page->excerpt)
                    <p class="text-xl opacity-90">{{ $page->excerpt }}</p>
                @endif
            </div>
        </div>
    </section>

    <!-- Page Content -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            {!! $page->content !!}
        </div>
    </section>
</main>
@endsection