{{-- @extends('layout')

@section('banner')
    <h1>My blog</h1>
@endsection --}}

{{-- @section('content') --}}
<x-layout>
    <x-slot name='content'>
        @foreach ($posts as $post)
            <article class="{{ $loop->even ? 'foobar' : '' }}">
                <h1>
                    <a href="/posts/{{ $post->slug }}">
                        {{ $post->title }}
                    </a>
                </h1>

                <p>
                    <a href="#">{{ $post->category->name }}</a>
                </p>

                <div>
                    {!! $post->excerpt !!}
                </div>
            </article>
        @endforeach
        {{-- @endsection --}}
    </x-slot>
</x-layout>
