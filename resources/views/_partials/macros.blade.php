<svg width="32" height="{{ $height }}" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
  {{-- Escudo principal con gradiente --}}
  <defs>
    <linearGradient id="shieldGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#696cff;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#5e60ff;stop-opacity:1" />
    </linearGradient>
    <linearGradient id="accentGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#28c76f;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#22b863;stop-opacity:1" />
    </linearGradient>
  </defs>
  
  {{-- Fondo del escudo --}}
  <path d="M16 2L6 7V16C6 22.5 16 30 16 30S26 22.5 26 16V7L16 2Z" fill="@if(isset($withbg))#fff @else url(#shieldGradient) @endif" @if(isset($withbg))style="{{ $withbg }}" @endif />
  
  {{-- Borde del escudo --}}
  <path d="M16 2L6 7V16C6 22.5 16 30 16 30S26 22.5 26 16V7L16 2Z" stroke="{{ isset($withbg) ? '#fff' : 'rgba(255,255,255,0.1)' }}" stroke-width="0.5" fill="none"/>
  
  {{-- Gráfico de gestión (líneas ascendentes) --}}
  <g opacity="0.9">
    <line x1="11" y1="20" x2="11" y2="18" stroke="@if(isset($withbg))#696cff @else white @endif" stroke-width="1.5" stroke-linecap="round"/>
    <line x1="13" y1="20" x2="13" y2="16" stroke="@if(isset($withbg))#696cff @else white @endif" stroke-width="1.5" stroke-linecap="round"/>
    <line x1="15" y1="20" x2="15" y2="14" stroke="@if(isset($withbg))#696cff @else white @endif" stroke-width="1.5" stroke-linecap="round"/>
    <line x1="17" y1="20" x2="17" y2="12" stroke="@if(isset($withbg))#696cff @else white @endif" stroke-width="1.5" stroke-linecap="round"/>
    <line x1="19" y1="20" x2="19" y2="15" stroke="@if(isset($withbg))#696cff @else white @endif" stroke-width="1.5" stroke-linecap="round"/>
    <line x1="21" y1="20" x2="21" y2="17" stroke="@if(isset($withbg))#696cff @else white @endif" stroke-width="1.5" stroke-linecap="round"/>
    
    {{-- Base del gráfico --}}
    <line x1="10.5" y1="20" x2="21.5" y2="20" stroke="@if(isset($withbg))#696cff @else white @endif" stroke-width="2" stroke-linecap="round"/>
  </g>
  
  {{-- Símbolo de equipo/deportivo (personas) --}}
  <g opacity="0.85">
    {{-- Persona 1 --}}
    <circle cx="11" cy="11" r="2" fill="@if(isset($withbg))#696cff@else white @endif"/>
    <path d="M11 13C11 13 9 14.5 9 16.5V20H13V16.5C13 14.5 11 13 11 13Z" fill="@if(isset($withbg))#696cff@else white @endif"/>
    
    {{-- Persona 2 (central, más grande) --}}
    <circle cx="16" cy="10" r="2.2" fill="url(#accentGradient)"/>
    <path d="M16 12.2C16 12.2 13.5 14 13.5 16.2V20H18.5V16.2C18.5 14 16 12.2 16 12.2Z" fill="url(#accentGradient)"/>
    
    {{-- Persona 3 --}}
    <circle cx="21" cy="11" r="2" fill="@if(isset($withbg))#696cff@else white @endif"/>
    <path d="M21 13C21 13 23 14.5 23 16.5V20H19V16.5C19 14.5 21 13 21 13Z" fill="@if(isset($withbg))#696cff@else white @endif"/>
  </g>
  
  {{-- Estrella o badge de calidad en la parte superior --}}
  <circle cx="16" cy="5.5" r="1.5" fill="url(#accentGradient)"/>
</svg>
