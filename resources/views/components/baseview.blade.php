@include('layouts.partials.demo-header')
<div class="px-4">
    <div class="flex items-center w-full mb-2 text-2xl font-semibold">
        <p>{{ $title ?? 'List' }}</p>
        <div class="mx-auto"></div>
        @if ($showNew ?? false)
            @if (isset($link))
                <x-buttons.link title="{{ $actionTitle ?? '' }}" :link="$link" newTab="{{ $newTab ?? false }}" />
            @else
                <x-buttons.new title="{{ $actionTitle ?? '' }}" />
            @endif
        @endif
        <div>
            {{ $newBtn ?? '' }}
        </div>

    </div>
    @if ($newInfo ?? false)
        <p class="mb-4 text-xs font-light">{{ __('Note: Please login as vendor manager to be able create new data') }}
        </p>
    @endif
    {{-- list --}}
    {{ $slot }}


</div>
{{-- loading --}}
<x-loading />
