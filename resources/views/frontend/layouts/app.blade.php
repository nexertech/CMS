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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">
  <style>
    html,
    body {
      margin: 0;
      padding: 0;
      height: 100%;
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.29.2/dist/feather.min.js"></script>
  <script>
    // Initialize feather icons
    if (typeof feather !== 'undefined') {
      feather.replace();
    }
  </script>
  @stack('scripts')
</body>

</html>
