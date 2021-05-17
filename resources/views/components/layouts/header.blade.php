@props(['activePage' => 'home'])
  
  <!-- ======= Header ======= -->
  <header id="header" class="fixed-top">
    <div class="container d-flex align-items-center">

      <h1 class="logo mr-auto"><a href="/"><img src="{{config('config.assets.logo')}}" alt="Connect - Live Chat, Live Class, Meeting, Webinar, Video & Audio Conference"></a></h1>
      <!-- Uncomment below if you prefer to use an image logo -->
      <!-- <a href="index.html" class="logo mr-auto"><img src="/site/img/logo.png" alt="" class="img-fluid"></a>-->

      <nav class="nav-menu d-none d-lg-block">
        <ul>
          <li class="{{ $activePage == 'home' ? 'active' : '' }}"><a href="/#home">Home</a></li>
          <li><a href="/about">About</a></li>
          <li><a href="/faq">FAQs</a></li>
          <li><a href="/contact">Contact</a></li>
        </ul>
      </nav><!-- .nav-menu -->

      @if (\Auth::check())
        <a href="/app/panel/dashboard" class="get-started-btn scrollto">Dashboard</a>
      @else
        <a href="/app/login" class="get-started-btn scrollto">Login</a>
      @endif

    </div>
  </header><!-- End Header -->