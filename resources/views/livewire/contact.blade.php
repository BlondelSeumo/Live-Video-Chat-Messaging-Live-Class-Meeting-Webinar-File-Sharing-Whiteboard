<div>
    <!-- ======= Contact Section ======= -->
    <section id="contact" class="contact section-bg">
        <div class="container">

            <div class="section-title">
            <p>Magnam dolores commodi suscipit. Necessitatibus eius consequatur ex aliquid fuga eum quidem. Sit sint consectetur velit. Quisquam quos quisquam cupiditate. Et nemo qui impedit suscipit alias ea. Quia fugiat sit in iste officiis commodi quidem hic quas.</p>
            </div>

            <div class="row">

            <div class="col-lg-6">

                <div class="row">
                <div class="col-md-12">
                    <div class="info-box">
                    <i class="bx bx-map"></i>
                    <h3>Our Address</h3>
                    <p>KM105 Charlie Street, New York, NY 525012</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box mt-4">
                    <i class="bx bx-envelope"></i>
                    <h3>Email Us</h3>
                    <p>info@example.com<br>contact@example.com</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box mt-4">
                    <i class="bx bx-phone-call"></i>
                    <h3>Call Us</h3>
                    <p>+1 1111 22222 33</p>
                    <p>+1 1111 22222 55</p>
                    </div>
                </div>
                </div>

            </div>

            <div class="col-lg-6 mt-4 mt-md-0">
                <form method="post" role="form" wire:submit.prevent="contact" class="php-email-form">
                <div class="form-row">
                    <div class="col-md-6 form-group">
                    <input type="text" name="name" class="form-control" id="name" wire:model.debounce.500ms="name" placeholder="Your Name" data-rule="minlen:4" data-msg="Please enter at least 4 chars" />
                    <div class="validate"></div>
                    @error('name') <p class="text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-md-6 form-group">
                    <input type="text" class="form-control" name="contact_number" id="contact_number" wire:model.debounce.500ms="contact_number" placeholder="Contact Number" />
                    <div class="validate"></div>
                    @error('contact_number') <p class="text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="form-group">
                    <input type="email" class="form-control" name="email" id="email" wire:model.debounce.500ms="email" placeholder="Your Email" data-rule="email" data-msg="Please enter a valid email" />
                    <div class="validate"></div>
                    @error('email') <p class="text-danger">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" name="subject" id="subject" wire:model.debounce.500ms="subject" placeholder="Subject" data-rule="minlen:4" data-msg="Please enter at least 8 chars of subject" />
                    <div class="validate"></div>
                    @error('subject') <p class="text-danger">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <textarea class="form-control" name="message" wire:model.debounce.500ms="message" rows="5" data-rule="required" data-msg="Please write something for us" placeholder="Message"></textarea>
                    <div class="validate"></div>
                    @error('message') <p class="text-danger">{{ $message }}</p> @enderror
                </div>
                <div class="mb-3">
                    @if ($response_message)
                        <p class="{{ $error ? 'text-danger' : 'text-success' }}">{{$response_message}}</p>
                    @endif
                </div>
                <div class="text-center"><button type="submit">Send Message</button></div>
                </form>
            </div>

            </div>

        </div>
    </section><!-- End Contact Section -->
</div>
