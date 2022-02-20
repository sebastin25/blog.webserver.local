<header class="max-w-xl mx-auto mt-20 text-center">
    <h1 class="text-4xl">
        Latest <span class="text-blue-500">Laravel From Scratch</span> News
    </h1>
    <div class="space-y-2 lg:space-y-0 lg:space-x-4 mt-4">
        <!--  Category -->
        <div class="relative lg:inline-flex bg-gray-100 rounded-xl">
            <x-dropdown>
                <x-slot name='trigger'>
                    <button class="py-2 pl-3 pr-9 text-sm font-semibold w-full lg:w-32 text-left flex lg:inline-flex">

                        {{ isset($currentCategory) ? ucwords($currentCategory->name) : 'Categories' }}

                        <x-icon name='down-arrow' class=" absolute pointer-events-none" style="right: 12px;" />
                    </button>
                </x-slot>
                <x-dropdown-item href="/" :active="request()->routeIs('home')">All</x-dropdown-item>

                @foreach ($categories as $category)
                    <x-dropdown-item href="/categories/{{ $category->slug }}"
                        :active="request()->is('categories/' . $category->slug )">
                        {{ ucwords($category->name) }}
                    </x-dropdown-item>
                @endforeach
            </x-dropdown>
        </div>
    </div>
</header>
