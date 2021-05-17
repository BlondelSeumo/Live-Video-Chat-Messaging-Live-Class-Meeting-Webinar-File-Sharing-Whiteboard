<x-layouts.master>
    <x-slot name="title">
        {{$title ?? 'Home' }}
    </x-slot>

    <x-layouts.header />
    <x-layouts.hero />

    <main id="main">

        <x-pages.highlights />

        <x-pages.reviews />

        <x-pages.cases />

        <x-pages.cta />

    </main><!-- End #main -->

    <x-layouts.footer />

</x-layouts.master>