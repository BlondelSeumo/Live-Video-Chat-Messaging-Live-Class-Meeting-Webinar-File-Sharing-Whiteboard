<x-layouts.master>
    <x-slot name="title">
        {{$title ?? 'About' }}
    </x-slot>

    <x-layouts.header activePage="about" />

    <x-layouts.subheader heading="About Us" />

    <main id="main">

        <x-pages.about />

        <x-pages.cta />

    </main><!-- End #main -->

    <x-layouts.footer />

</x-layouts.master>