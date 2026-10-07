<!-- ============================================
     Navbar — InvoiceFlow
     Sticky top nav with desktop links + mobile hamburger.
     Changes links based on guest / authenticated state.
     ============================================ -->
<nav class="navbar navbar-expand-lg navbar-invoiceflow">
    <div class="container">
        <!-- Brand / Logo -->
        <a class="navbar-brand" href="{{ route('home') }}">
            <span class="logo-icon" aria-hidden="true">
                <i class="bi bi-check2"></i>
            </span>
            InvoiceFlow
        </a>

        <!-- Mobile Hamburger Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#invoiceflowNavbar" aria-controls="invoiceflowNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links (collapsible) -->
        <div class="collapse navbar-collapse" id="invoiceflowNavbar">
            <!-- Left / Center Nav Links -->
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#how">How it works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#pricing">Pricing</a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('clients.index') }}">Clients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('invoices.index') }}">Invoices</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('invoice.create') }}">New invoice</a>
                    </li>
                @endguest
            </ul>

            <!-- Right Action Buttons -->
            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                @guest
                    <a class="nav-link login-link" href="{{ route('login') }}">Login</a>
                    <a class="btn btn-ink" href="{{ route('invoice.create') }}">
                        Get started free
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                @else
                    <span class="navbar-text d-none d-lg-inline me-2" title="Signed in as {{ Auth::user()->email }}">
                        <i class="bi bi-person-circle me-1"></i>
                        {{ Auth::user()->name }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline m-0" onsubmit="return confirm('Sign out of InvoiceFlow?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-ink">
                            <i class="bi bi-box-arrow-right me-1"></i>
                            Logout
                        </button>
                    </form>
                @endguest
            </div>
        </div>
    </div>
</nav>
