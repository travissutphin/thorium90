@extends('blog::layouts.blog-layout', [
    'title' => 'Archive' . ($year ? ' - ' . $year : '') . ($month ? '/' . str_pad($month, 2, '0', STR_PAD_LEFT) : '') . ' - Blog',
    'metaTitle' => 'Blog Archive' . ($year ? ' - ' . $year : '') . ($month ? '/' . str_pad($month, 2, '0', STR_PAD_LEFT) : '') . ' - ' . config('app.name'),
    'metaDescription' => 'Browse our blog archive' . ($year && $month ? ' for ' . \Carbon\Carbon::create($year, $month, 1)->format('F Y') : ($year ? ' for ' . $year : '')) . ' and discover past content.',
    'structuredData' => $structuredData ?? null,
    'openGraphMeta' => $openGraphMeta ?? null,
    'breadcrumbs' => $breadcrumbs ?? null
])

@section('content')
<div class="blog-container">
    <!-- Archive Header -->
    <header class="text-center mb-12">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4 bg-gradient-to-br from-amber-500 to-orange-600">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </div>
        
        <h1 class="blog-title-large gradient-text mb-2">
            @if($year && $month)
                {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }} Archive
            @elseif($year)
                {{ $year }} Archive
            @else
                Blog Archive
            @endif
        </h1>
        
        <p class="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto mb-4">
            @if($posts->total() > 0)
                Showing {{ $posts->total() }} {{ Str::plural('post', $posts->total()) }}
                @if($year && $month)
                    from {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                @elseif($year)
                    from {{ $year }}
                @endif
            @else
                No posts found for this period
            @endif
        </p>
        
        @if($year || $month)
        <div class="flex items-center justify-center space-x-4 text-sm">
            <a href="{{ route('blog.archive') }}" class="text-purple-600 dark:text-purple-400 hover:underline">
                ← View All Archives
            </a>
        </div>
        @endif
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-3">
            @if(!$year && !$month)
                <!-- Archive Navigation -->
                @if($archiveData && count($archiveData) > 0)
                <div class="mb-12">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6">Browse by Date</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($archiveData as $archive)
                        <a href="{{ route('blog.archive', ['year' => $archive['year'], 'month' => $archive['month']]) }}" 
                           class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-amber-300 dark:hover:border-amber-600 transition-colors group">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900 flex items-center justify-center mr-3 group-hover:bg-amber-200 dark:group-hover:bg-amber-800 transition-colors">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900 dark:text-gray-100 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                        {{ $archive['name'] }}
                                    </h3>
                                </div>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                                {{ $archive['count'] }}
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            @endif

            @if($posts->count() > 0)
                <!-- Archive Filters -->
                @if($year)
                <div class="mb-8">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Filter by month:</span>
                        <a href="{{ route('blog.archive', ['year' => $year]) }}" 
                           class="text-sm px-3 py-1 rounded-full {{ !$month ? 'bg-amber-100 text-amber-800' : 'text-gray-600 hover:bg-gray-100' }} transition-colors">
                            All {{ $year }}
                        </a>
                        @for($m = 1; $m <= 12; $m++)
                            @if(isset($availableMonths[$m]) && $availableMonths[$m] > 0)
                            <a href="{{ route('blog.archive', ['year' => $year, 'month' => $m]) }}" 
                               class="text-sm px-3 py-1 rounded-full {{ $month == $m ? 'bg-amber-100 text-amber-800' : 'text-gray-600 hover:bg-gray-100' }} transition-colors">
                                {{ \Carbon\Carbon::create($year, $m, 1)->format('M') }}
                                <span class="ml-1 text-xs opacity-75">({{ $availableMonths[$m] }})</span>
                            </a>
                            @endif
                        @endfor
                    </div>
                </div>
                @endif

                <!-- Posts -->
                <div class="space-y-8">
                    @foreach($posts as $post)
                        @include('blog::partials.post-card-horizontal', ['post' => $post])
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="blog-pagination">
                    {{ $posts->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full mb-4 bg-gray-100">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                        No posts found
                        @if($year && $month)
                            for {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                        @elseif($year)
                            for {{ $year }}
                        @endif
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        @if($year || $month)
                            Try browsing a different time period or check out our latest posts.
                        @else
                            It looks like there are no archived posts yet.
                        @endif
                    </p>
                    <div class="space-x-4">
                        <a href="{{ route('blog.index') }}" class="blog-btn-primary">
                            Latest Posts
                        </a>
                        @if($year || $month)
                        <a href="{{ route('blog.archive') }}" class="blog-btn-secondary">
                            View All Archives
                        </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Archive Navigation Sidebar -->
            @if($year || $month)
            <div class="blog-sidebar mb-8">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Quick Navigation</h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('blog.archive') }}" class="flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            All Archives
                        </a>
                    </li>
                    @if($year && $month)
                    <li>
                        <a href="{{ route('blog.archive', ['year' => $year]) }}" class="flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            All of {{ $year }}
                        </a>
                    </li>
                    @endif
                    <li>
                        <a href="{{ route('blog.index') }}" class="flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Latest Posts
                        </a>
                    </li>
                </ul>
            </div>
            @endif

            @include('blog::partials.sidebar', [
                'categories' => $categories ?? collect(),
                'popularTags' => $popularTags ?? collect(),
                'recentPosts' => $recentPosts ?? collect()
            ])
        </div>
    </div>
</div>
@endsection