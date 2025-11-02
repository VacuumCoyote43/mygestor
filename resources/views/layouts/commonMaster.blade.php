<!DOCTYPE html>
@php
$menuFixed = ($configData['layout'] === 'vertical') ? ($menuFixed ?? '') : (($configData['layout'] === 'front') ? '' : $configData['headerType']);
$navbarType = ($configData['layout'] === 'vertical') ? ($configData['navbarType'] ?? '') : (($configData['layout'] === 'front') ? 'layout-navbar-fixed': '');
$isFront = ($isFront ?? '') == true ? 'Front' : '';
$contentLayout = (isset($container) ? (($container === 'container-xxl') ? "layout-compact" : "layout-wide") : "");
@endphp

<html lang="{{ session()->get('locale') ?? app()->getLocale() }}" class="{{ $configData['style'] }}-style {{($contentLayout ?? '')}} {{ ($navbarType ?? '') }} {{ ($menuFixed ?? '') }} {{ $menuCollapsed ?? '' }} {{ $menuFlipped ?? '' }} {{ $menuOffcanvas ?? '' }} {{ $footerFixed ?? '' }} {{ $customizerHidden ?? '' }}" dir="{{ $configData['textDirection'] }}" data-theme="{{ $configData['theme'] }}" data-assets-path="{{ asset('/assets') . '/' }}" data-base-url="{{url('/')}}" data-framework="laravel" data-template="{{ $configData['layout'] . '-menu-' . $configData['themeOpt'] . '-' . $configData['styleOpt'] }}" data-style="{{$configData['styleOptVal']}}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>@yield('title') |
    {{ config('variables.templateName') ? config('variables.templateName') : 'TemplateName' }} -
    {{ config('variables.templateSuffix') ? config('variables.templateSuffix') : 'TemplateSuffix' }}
  </title>
  <meta name="description" content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
  <meta name="keywords" content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}">
  <!-- laravel CRUD token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- Canonical SEO -->
  <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}">
  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/favicon/favicon.svg') }}" />
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon/favicon-32x32.png') }}" />
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon/favicon-16x16.png') }}" />
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicon/apple-touch-icon.png') }}" />
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />


  <!-- Include Styles -->
  <!-- $isFront is used to append the front layout styles only on the front layout otherwise the variable will be blank -->
  @include('layouts/sections/styles' . $isFront)

  <!-- Page Loader Styles -->
  <style>
    .page-loader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.95);
      z-index: 9999;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: opacity 0.5s ease, visibility 0.5s ease;
    }

    .page-loader.dark-mode {
      background: rgba(26, 32, 44, 0.95);
    }

    .page-loader.hidden {
      opacity: 0;
      visibility: hidden;
    }

    .loader-content {
      text-align: center;
    }

    .loader-spinner {
      width: 60px;
      height: 60px;
      border: 4px solid #f3f4f6;
      border-top-color: #696cff;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 20px;
    }

    .dark-mode .loader-spinner {
      border-color: #2b2c40;
      border-top-color: #696cff;
    }

    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }

    .loader-text {
      color: #696cff;
      font-size: 16px;
      font-weight: 500;
      margin-top: 15px;
    }

    .dark-mode .loader-text {
      color: #696cff;
    }

    .loader-logo {
      margin-bottom: 20px;
    }

    .loader-logo img {
      max-width: 120px;
      height: auto;
    }
  </style>

  <!-- Include Scripts for customizer, helper, analytics, config -->
  <!-- $isFront is used to append the front layout scriptsIncludes only on the front layout otherwise the variable will be blank -->
  @include('layouts/sections/scriptsIncludes' . $isFront)
</head>

<body>

  <!-- Page Loader Overlay -->
  <div class="page-loader" id="pageLoader">
    <div class="loader-content">
      <div class="loader-spinner"></div>
      <div class="loader-text">Cargando...</div>
    </div>
  </div>

  <!-- Layout Content -->
  @yield('layoutContent')
  <!--/ Layout Content -->

  {{-- remove while creating package --}}
  {{-- remove while creating package end --}}

  <!-- Include Scripts -->
  <!-- $isFront is used to append the front layout scripts only on the front layout otherwise the variable will be blank -->
  @include('layouts/sections/scripts' . $isFront)

  <!-- Page Loader Script -->
  <script>
    (function() {
      // Detectar modo oscuro
      function checkDarkMode() {
        const htmlElement = document.documentElement;
        const loader = document.getElementById('pageLoader');
        
        if (htmlElement.classList.contains('dark-style') || 
            htmlElement.getAttribute('data-theme')?.includes('dark') ||
            document.body.classList.contains('dark-style')) {
          loader.classList.add('dark-mode');
        } else {
          loader.classList.remove('dark-mode');
        }
      }

      // Ocultar loader cuando la página esté completamente cargada
      function hideLoader() {
        const loader = document.getElementById('pageLoader');
        if (loader) {
          // Esperar un mínimo de 300ms para una mejor UX
          setTimeout(function() {
            loader.classList.add('hidden');
            // Remover del DOM después de la animación
            setTimeout(function() {
              loader.style.display = 'none';
            }, 500);
          }, 300);
        }
      }

      // Ejecutar cuando el DOM esté listo
      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
          checkDarkMode();
        });
      } else {
        checkDarkMode();
      }

      // Ocultar loader cuando todo esté cargado
      if (document.readyState === 'complete') {
        hideLoader();
      } else {
        window.addEventListener('load', hideLoader);
      }

      // Ocultar loader también cuando se navega entre páginas (para navegación AJAX/SPA)
      document.addEventListener('DOMContentLoaded', function() {
        // Observar cambios en el body para detectar navegación
        const observer = new MutationObserver(function(mutations) {
          checkDarkMode();
        });
        
        if (document.body) {
          observer.observe(document.body, {
            attributes: true,
            attributeFilter: ['class']
          });
        }
      });

      // Función global para mostrar/ocultar loader manualmente si es necesario
      window.showPageLoader = function() {
        const loader = document.getElementById('pageLoader');
        if (loader) {
          loader.style.display = 'flex';
          loader.classList.remove('hidden');
          checkDarkMode();
        }
      };

      window.hidePageLoader = function() {
        hideLoader();
      };
    })();
  </script>

</body>

</html>
