<x-layouts.master>
    <x-slot name="title">
        {{$title ?? 'FAQs' }}
    </x-slot>

    <x-layouts.header activePage="faq" />

    <x-layouts.subheader heading="Frequently Asked Questions" />

    <main id="main">

        <x-pages.faq />

        <x-pages.cta />

    </main><!-- End #main -->

    <x-layouts.footer />

</x-layouts.master>