<x-layout>
    <x-slot name='slot'>

        @include('posts._header')

        <main class="max-w-6xl mx-auto mt-6 lg:mt-20 space-y-6">
            @if ($posts->count(1))
                <x-posts-grid :posts="$posts" />

                {{ $posts->links() }}
            @else
                <p class="text-center"> No posts yet. Please check back later.</p>
            @endif
        </main>
    </x-slot>
</x-layout>