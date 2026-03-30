@php
  $title = trim($__env->yieldContent('title')) ?: 'CMS';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title }}</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  
  <!-- Preconnect to external domains -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://cdn.jsdelivr.net">
  <link rel="dns-prefetch" href="https://fonts.googleapis.com">
  <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"></noscript>
  
  <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">
  <style>
    html,
    body {
      margin: 0;
      padding: 0;
      min-height: 100%;
      font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
    }

    /* Browser compatibility for text-size-adjust */
    html,
    body {
      -webkit-text-size-adjust: 100%;
      text-size-adjust: 100%;
    }

    /* Layout: make footer stick to bottom */
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    main {
      flex: 1 0 auto;
    }

    /* Global Footer Styling - Same for all pages */
    footer {
      text-align: center !important;
      width: 100% !important;
      display: block !important;
      position: relative !important;
    }

    footer .container {
      text-align: center !important;
      margin: 0 auto !important;
    }

    footer p {
      text-align: center !important;
      margin: 0 auto !important;
    }
  </style>
  @stack('styles')
</head>

<body>
  @include('frontend.layouts.navbar')

  <main>
    @yield('content')
  </main>

  @include('frontend.layouts.footer')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.29.2/dist/feather.min.js" integrity="sha384-qEqAs1VsN9WH2myXDbiP2wGGIttL9bMRZBKCl54ZnzpDlVqbYANP9vMaoT/wvQcf" crossorigin="anonymous" defer></script>
  <script>
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
      feather.replace();
    }

    // Fix for "Page Expired" (CSRF) issues when using back button or after logout
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
  </script>
  @stack('scripts')
</body>

</html>
