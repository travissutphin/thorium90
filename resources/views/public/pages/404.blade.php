@extends('public.layouts.thorium90-template')

@section('content')
<main class="min-h-screen">
    <!-- Page Content - Full Height -->
    <section class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100">
        <div class="container mx-auto px-4">
            {!! $page->content !!}
        </div>
    </section>
</main>
@endsection