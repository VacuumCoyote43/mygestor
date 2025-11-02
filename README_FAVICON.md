# Generación de Favicons

El favicon SVG ya está creado en `public/assets/img/favicon/favicon.svg`.

## Generar versiones PNG e ICO

Para generar las versiones PNG e ICO necesarias, puedes usar cualquiera de estas opciones:

### Opción 1: Herramienta Online (Recomendado)
1. Visita https://realfavicongenerator.net/
2. Sube el archivo `public/assets/img/favicon/favicon.svg`
3. Genera y descarga el paquete de favicons
4. Coloca los archivos generados en `public/assets/img/favicon/`:
   - `favicon-16x16.png`
   - `favicon-32x32.png`
   - `apple-touch-icon.png`
   - `favicon.ico`

### Opción 2: ImageMagick (si está instalado)
```bash
magick convert public/assets/img/favicon/favicon.svg -resize 16x16 public/assets/img/favicon/favicon-16x16.png
magick convert public/assets/img/favicon/favicon.svg -resize 32x32 public/assets/img/favicon/favicon-32x32.png
magick convert public/assets/img/favicon/favicon.svg -resize 180x180 public/assets/img/favicon/apple-touch-icon.png
magick convert public/assets/img/favicon/favicon.svg -resize 32x32 public/assets/img/favicon/favicon.ico
```

### Opción 3: Node.js con sharp-cli
```bash
npm install -g sharp-cli
sharp -i public/assets/img/favicon/favicon.svg -o public/assets/img/favicon/favicon-16x16.png --resize 16 16
sharp -i public/assets/img/favicon/favicon.svg -o public/assets/img/favicon/favicon-32x32.png --resize 32 32
sharp -i public/assets/img/favicon/favicon.svg -o public/assets/img/favicon/apple-touch-icon.png --resize 180 180
```

### Opción 4: Usar el favicon SVG directamente
El favicon SVG ya está configurado y funcionará en navegadores modernos. Las versiones PNG e ICO son para compatibilidad con navegadores antiguos.

## Archivos necesarios
- ✅ `favicon.svg` - Ya creado
- ⏳ `favicon-16x16.png` - Por generar
- ⏳ `favicon-32x32.png` - Por generar
- ⏳ `apple-touch-icon.png` - Por generar
- ⏳ `favicon.ico` - Por generar

Los archivos HTML ya están configurados en `resources/views/layouts/commonMaster.blade.php`.
