<footer
  style="background: {{ Request::is('/') || Route::is('frontend.home') || Route::is('frontend.forgot-password') ? 'transparent' : '#001f3f' }} !important; border: none !important; box-shadow: none !important; color: #ffffff !important; margin-top: 0 !important; margin-bottom: 0 !important; padding: 15px 0 !important; text-align: center !important; width: 100%; position: fixed; bottom: 0; left: 0; z-index: 999; backdrop-filter: none !important; -webkit-backdrop-filter: none !important;">
  <div class="container py-2"
    style="background: transparent !important; text-align: center !important; margin: 0 auto !important; width: 100%; max-width: 100%;">
    <div class="text-center" style="text-align: center !important; width: 100%;">
      <p class="mb-0"
        style="color: #ffffff !important; font-size: 0.95rem; font-weight: bold !important; text-align: center !important; margin: 0 auto !important; width: 100%; display: block;">
        © {{ date('Y') }} MES All Rights Reserved
      </p>
    </div>
  </div>
</footer>
