<!-- ============================================
     Footer — InvoiceFlow
     Brand, description, link columns, copyright
     ============================================ -->
<footer class="site-footer">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <!-- Brand Column -->
            <div class="col-md-4">
                <div class="footer-brand">
                    <span class="logo-icon" aria-hidden="true">
                        <i class="bi bi-check2"></i>
                    </span>
                    InvoiceFlow
                </div>
                <p class="footer-description">
                    Simple, beautiful invoicing for freelancers and small businesses. Get paid on time, every time.
                </p>
            </div>

            <!-- Product Links -->
            <div class="col-6 col-md-2 offset-md-1">
                <h4 class="footer-heading">Product</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('features') }}">Features</a></li>
                    <li><a href="{{ route('home') }}#how">How it works</a></li>
                    <li><a href="{{ route('pricing') }}">Pricing</a></li>
                </ul>
            </div>

            <!-- Company Links -->
            <div class="col-6 col-md-2">
                <h4 class="footer-heading">Company</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('privacy') }}">Privacy</a></li>
                    <li><a href="{{ route('terms') }}">Terms</a></li>
                </ul>
            </div>

            <!-- Support Links -->
            <div class="col-6 col-md-2">
                <h4 class="footer-heading">Support</h4>
                <ul class="footer-links">
                    <li><a href="{{ route('help') }}">Help center</a></li>
                    <li><a href="{{ route('contact') }}">Contact us</a></li>
                </ul>
            </div>
        </div>

        <!-- Copyright Row -->
        <div class="footer-bottom">
            <p class="mb-0">
                &copy; {{ date('Y') }} InvoiceFlow. All rights reserved.
            </p>
        </div>
    </div>
</footer>
