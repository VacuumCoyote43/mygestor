<?php
/**
 * Script para generar favicons desde el SVG
 * 
 * Requiere ImageMagick o puede usarse con herramientas online como:
 * - https://realfavicongenerator.net/
 * - https://www.favicon-generator.org/
 * 
 * O usando Node.js con sharp:
 * npm install -g sharp-cli
 * sharp -i public/assets/img/favicon/favicon.svg -o public/assets/img/favicon/favicon-32x32.png --resize 32
 */

echo "Para generar los favicons PNG e ICO desde el SVG, puedes:\n\n";
echo "Opción 1: Usar herramienta online\n";
echo "  1. Visita: https://realfavicongenerator.net/\n";
echo "  2. Sube el archivo: public/assets/img/favicon/favicon.svg\n";
echo "  3. Genera y descarga los favicons\n";
echo "  4. Coloca los archivos en: public/assets/img/favicon/\n\n";

echo "Opción 2: Usar ImageMagick (si está instalado)\n";
echo "  magick convert public/assets/img/favicon/favicon.svg -resize 32x32 public/assets/img/favicon/favicon-32x32.png\n";
echo "  magick convert public/assets/img/favicon/favicon.svg -resize 16x16 public/assets/img/favicon/favicon-16x16.png\n";
echo "  magick convert public/assets/img/favicon/favicon.svg -resize 180x180 public/assets/img/favicon/apple-touch-icon.png\n";
echo "  magick convert public/assets/img/favicon/favicon.svg -resize 32x32 public/assets/img/favicon/favicon.ico\n\n";

echo "El archivo SVG ya está creado en: public/assets/img/favicon/favicon.svg\n";
echo "Las referencias HTML ya están actualizadas en: resources/views/layouts/commonMaster.blade.php\n";
