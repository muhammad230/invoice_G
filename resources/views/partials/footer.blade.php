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
                    <li><a href="{{ route('home') }}#features">Features</a></li>
                    <li><a href="{{ route('home') }}#how">How it works</a></li>
                    <li><a href="{{ route('home') }}#pricing">Pricing</a></li>
                </ul>
            </div>

            <!-- Company Links -->
            <div class="col-6 col-md-2">
                <h4 class="footer-heading">Company</h4>
                <ul class="footer-links">
                    <li><a href="#">About</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Careers</a></li>
                </ul>
            </div>

            <!-- Support Links -->
            <div class="col-6 col-md-2">
                <h4 class="footer-heading">Support</h4>
                <ul class="footer-links">
                    <li><a href="#">Help center</a></li>
                    <li><a href="#">Contact us</a></li>
                    <li><a href="#">Privacy</a></li>
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
