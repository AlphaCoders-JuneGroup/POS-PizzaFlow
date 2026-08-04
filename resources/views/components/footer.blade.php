{{-- Footer --}}
<footer class="pf-footer" id="contact">
    <div class="container">
        <div class="row g-4 gy-5">
            {{-- Brand --}}
            <div class="col-lg-4" data-aos="fade-up">
                <a class="pf-brand footer-brand d-inline-flex align-items-center mb-3" href="{{ route('home') }}">
                    <span class="pf-brand-icon"><i class="bi bi-pie-chart-fill"></i></span>
                    <span class="pf-brand-text">Pizza<span>Flow</span></span>
                </a>
                <p class="pf-footer-about">
                    PizzaFlow brings authentic Italian flavors to your doorstep.
                    Fresh ingredients, expert chefs, and lightning-fast delivery.
                </p>
                <div class="pf-social d-flex gap-2 mt-3">
                    <a href="#" class="pf-social-link" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="pf-social-link" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="pf-social-link" aria-label="Twitter / X"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="pf-social-link" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                    <a href="#" class="pf-social-link" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="80">
                <h4 class="pf-footer-heading">Quick Links</h4>
                <ul class="pf-footer-links list-unstyled">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#menu">Menu</a></li>
                    <li><a href="#offers">Offers</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>

            {{-- Contact Information --}}
            <div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="120">
                <h4 class="pf-footer-heading">Contact</h4>
                <ul class="pf-footer-contact list-unstyled">
                    <li>
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>42 Galle Road, Colombo 03, Sri Lanka</span>
                    </li>
                    <li>
                        <i class="bi bi-telephone-fill"></i>
                        <a href="tel:+94112233445">+94 11 223 3445</a>
                    </li>
                    <li>
                        <i class="bi bi-envelope-fill"></i>
                        <a href="mailto:hello@pizzaflow.com">hello@pizzaflow.com</a>
                    </li>
                </ul>
            </div>

            {{-- Opening Hours --}}
            <div class="col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="160">
                <h4 class="pf-footer-heading">Opening Hours</h4>
                <ul class="pf-footer-hours list-unstyled">
                    <li>
                        <span>Mon – Thu</span>
                        <strong>10:00 AM – 11:00 PM</strong>
                    </li>
                    <li>
                        <span>Fri – Sat</span>
                        <strong>10:00 AM – 12:00 AM</strong>
                    </li>
                    <li>
                        <span>Sunday</span>
                        <strong>11:00 AM – 10:00 PM</strong>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="pf-footer-divider">

        <div class="pf-footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <p class="mb-0">&copy; {{ date('Y') }} PizzaFlow. All rights reserved.</p>
            <div class="d-flex gap-3 pf-footer-legal">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>
