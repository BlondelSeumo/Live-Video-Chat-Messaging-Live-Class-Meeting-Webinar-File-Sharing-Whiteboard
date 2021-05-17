<x-layouts.master>
    <x-slot name="title">
        {{$title ?? 'Contact' }}
    </x-slot>

    <x-layouts.header activePage="contact" />

    <x-layouts.subheader heading="Get in touch with us!" />

    <main id="main">

        @livewire('contact')

        <x-pages.cta />

    </main><!-- End #main -->

    <x-layouts.footer />

</x-layouts.master>