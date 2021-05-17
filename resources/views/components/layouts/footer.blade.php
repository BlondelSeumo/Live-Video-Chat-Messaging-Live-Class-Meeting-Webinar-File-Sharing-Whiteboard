  <!-- ======= Footer ======= -->
  <footer id="footer">

    <div class="footer-top">
      <div class="container">
        <div class="row">

          <div class="col-lg-3 col-md-6 footer-contact">
            <h3 class="mb-3"><a href="/"><img src="{{config('config.assets.logo')}}" alt="Connect - Live Chat, Live Class, Meeting, Webinar, Video & Audio Conference"></a></h3>
            <p>
              KM105 Charlie Street <br>
              New York, NY 525012<br>
              United States <br><br>
              <strong>Phone:</strong> +1 1111 22222 33<br>
              <strong>Email:</strong> info@example.com<br>
            </p>
          </div>

          <div class="col-lg-2 col-md-6 footer-links">
            <h4>Useful Links</h4>
            <ul>
              <li><i class="bx bx-chevron-right"></i> <a href="/about">About</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="/faq">FAQs</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="/contact">Contact</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="/pages/terms">Terms of service</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="/pages/privacy">Privacy policy</a></li>
            </ul>
          </div>

          <div class="col-lg-3 col-md-6 footer-links">
            <h4>Meeting Types</h4>
            <ul>
              <li><i class="bx bx-chevron-right"></i> <a href="#highlights">Video Conference</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#highlights">Webinar</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#highlights">Live Class</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#highlights">Audio Conference</a></li>
              <li><i class="bx bx-chevron-right"></i> <a href="#highlights">Podcast</a></li>
            </ul>
          </div>

          <div class="col-lg-4 col-md-6 footer-newsletter">
            <h4>Join Our Newsletter</h4>
            <p>Tamen quem nulla quae legam multos aute sint culpa legam noster magna</p>
            
            @livewire('newsletter')

          </div>

        </div>
      </div>
    </div>

    <div class="container d-md-flex py-5">

      <div class="mr-md-auto text-center text-md-left">
        <div class="copyright">
          &copy; Copyright <strong><span>Connect</span></strong>. All Rights Reserved
        </div>
      </div>
      <div class="social-links text-center text-md-right pt-3 pt-md-0">

        @if (config('config.social.facebook_link'))
          <a href="{{ config('config.social.facebook_link') }}" class="facebook" target="_blank"><i class="bx bxl-facebook"></i></a>
        @endif

        @if (config('config.social.twitter_link'))
          <a href="{{ config('config.social.twitter_link') }}" class="twitter" target="_blank"><i class="bx bxl-twitter"></i></a>
        @endif
        
        @if (config('config.social.instagram_link'))
          <a href="{{ config('config.social.instagram_link') }}" class="instagram" target="_blank"><i class="bx bxl-instagram"></i></a>
        @endif
        
        @if (config('config.social.linkedin_link'))
          <a href="{{ config('config.social.linkedin_link') }}" class="linkedin" target="_blank"><i class="bx bxl-linkedin"></i></a>
        @endif
        
        @if (config('config.social.youtube_link'))
          <a href="{{ config('config.social.youtube_link') }}" class="youtube" target="_blank"><i class="bx bxl-youtube"></i></a>
        @endif
        
        @if (config('config.social.skype_link'))
          <a href="{{ config('config.social.skype_link') }}" class="skype" target="_blank"><i class="bx bxl-skype"></i></a>
        @endif
      </div>
    </div>
  </footer><!-- End Footer -->