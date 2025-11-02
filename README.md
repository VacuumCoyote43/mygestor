# MyGestor 🏆

Sistema de gestión integral para equipos deportivos que permite administrar jugadores, gastos, pagos y proveedores de manera eficiente y automatizada.

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Uso](#-uso)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Tecnologías Utilizadas](#-tecnologías-utilizadas)
- [Desarrollo](#-desarrollo)
- [Contribuir](#-contribuir)
- [Licencia](#-licencia)

## ✨ Características

### 👥 Gestión de Jugadores
- CRUD completo de jugadores
- Importación masiva desde archivos Excel
- Gestión de información personal (DNI, fecha de nacimiento, dorsal)
- Gestión de tallas de uniformes (camiseta, pantalón, medias)
- Cálculo automático de saldos por jugador
- Visualización detallada de historial de gastos y pagos

### 💰 Gestión de Gastos
- Registro de gastos con diferentes tipos
- Asignación a proveedores
- **Sistema de reparto inteligente:**
  - Reparto equitativo entre jugadores
  - Reparto personalizado por importe
  - Reparto por reglas personalizadas
- Validación automática del total repartido
- Historial completo de gastos del equipo

### 💳 Gestión de Pagos
- Registro de pagos individuales por jugador
- Importación masiva de pagos desde Excel
- Actualización automática de saldos
- Conceptos personalizados por pago
- Historial completo de pagos

### 🏢 Gestión de Proveedores
- CRUD completo de proveedores
- Relación con gastos realizados
- Historial de transacciones por proveedor

### 📊 Dashboard y Estadísticas
- Vista general del estado financiero del equipo
- Estadísticas en tiempo real
- Gráficos y visualizaciones interactivas
- Exportación de reportes a PDF y Excel
- Asistente de contabilidad con IA

### 👤 Autenticación y Roles
- Sistema de autenticación completo
- Roles de usuario (Admin, Usuario)
- Panel de administración exclusivo para administradores
- Gestión de usuarios desde el panel admin

### 📥 Importación y Exportación
- Importación de jugadores desde Excel
- Importación de pagos desde Excel
- Plantillas descargables para importación
- Exportación de estadísticas a PDF y Excel

## 📦 Requisitos

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.x y **npm** o **yarn**
- **Base de datos** (MySQL, PostgreSQL, SQLite)
- **Servidor web** (Apache, Nginx) o Laragon/XAMPP

## 🚀 Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/VacuumCoyote43/mygestor.git
cd mygestor
```

### 2. Instalar dependencias de PHP

```bash
composer install
```

### 3. Instalar dependencias de Node.js

```bash
npm install
# o
yarn install
```

### 4. Configurar el entorno

Copia el archivo de ejemplo y genera la clave de aplicación:

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configurar la base de datos

Edita el archivo `.env` y configura tus credenciales de base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mygestor
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 6. Ejecutar migraciones

```bash
php artisan migrate
```

### 7. Compilar assets

Para desarrollo:

```bash
npm run dev
# o
yarn dev
```

Para producción:

```bash
npm run build
# o
yarn build
```

### 8. Iniciar el servidor de desarrollo

```bash
php artisan serve
```

La aplicación estará disponible en `http://localhost:8000`

## ⚙️ Configuración

### Variables de Entorno Importantes

Asegúrate de configurar correctamente estas variables en tu archivo `.env`:

```env
APP_NAME="MyGestor"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mygestor
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Configuración Personalizada

El archivo `config/custom.php` contiene configuraciones del template Vuexy:
- Layout (vertical/horizontal)
- Tema (default/bordered/semi-dark)
- Estilo (light/dark)
- Soporte RTL

## 📖 Uso

### Primeros Pasos

1. **Registrar un usuario administrador:**
   - Ve a `/register` y crea tu cuenta
   - Asigna el rol de admin desde la base de datos o usando el comando de artisan

2. **Acceder al sistema:**
   - Inicia sesión en `/login`
   - Serás redirigido al dashboard

### Gestión de Jugadores

1. **Crear un jugador:**
   - Navega a `Jugadores` → `Crear Nuevo`
   - Completa el formulario con la información del jugador
   - Guarda los datos

2. **Importar jugadores:**
   - Ve a `Jugadores` → `Importar`
   - Descarga la plantilla Excel
   - Completa la plantilla con los datos
   - Sube el archivo completado

### Gestión de Gastos

1. **Crear un gasto:**
   - Ve a `Gastos` → `Crear Nuevo`
   - Selecciona el tipo de gasto y proveedor
   - Ingresa el importe total
   - Guarda el gasto

2. **Repartir un gasto:**
   - Accede al detalle del gasto
   - Elige el método de reparto:
     - **Equitativo**: Divide el gasto entre todos los jugadores seleccionados
     - **Personalizado**: Asigna importes específicos a cada jugador
     - **Por regla**: Utiliza reglas personalizadas para el reparto
   - Verifica que el total asignado coincida con el importe del gasto

### Gestión de Pagos

1. **Registrar un pago:**
   - Ve a `Pagos` → `Crear Nuevo`
   - Selecciona el jugador
   - Ingresa el importe y concepto
   - El saldo del jugador se actualizará automáticamente

2. **Importar pagos:**
   - Ve a `Pagos` → `Importar`
   - Descarga y completa la plantilla Excel
   - Sube el archivo para importar múltiples pagos

### Dashboard y Estadísticas

- **Dashboard Principal**: Vista general con estadísticas clave
- **Estadísticas Financieras**: Gráficos detallados y análisis
- **Exportar Reportes**: Genera reportes en PDF o Excel

## 📁 Estructura del Proyecto

```
mygestor/
├── app/
│   ├── Helpers/           # Funciones helper personalizadas
│   ├── Http/
│   │   ├── Controllers/   # Controladores de la aplicación
│   │   └── Middleware/    # Middlewares personalizados
│   ├── Imports/           # Clases para importación Excel
│   ├── Models/            # Modelos Eloquent
│   └── Providers/         # Service providers
├── config/                # Archivos de configuración
├── database/
│   ├── migrations/        # Migraciones de base de datos
│   └── seeders/          # Seeders para datos de prueba
├── public/                # Archivos públicos (assets, favicon)
├── resources/
│   ├── assets/           # Assets fuente (JS, CSS)
│   ├── js/               # JavaScript compilado
│   ├── css/              # Estilos CSS
│   └── views/            # Vistas Blade
├── routes/
│   └── web.php           # Rutas de la aplicación
└── storage/              # Archivos de almacenamiento
```

## 🛠️ Tecnologías Utilizadas

### Backend
- **Laravel 11** - Framework PHP
- **PHP 8.2** - Lenguaje de programación
- **MySQL/PostgreSQL** - Base de datos

### Frontend
- **Vuexy Admin Template** - Template administrativo
- **Bootstrap 5** - Framework CSS
- **jQuery** - Librería JavaScript
- **DataTables** - Tablas interactivas
- **Chart.js** - Gráficos y visualizaciones
- **Vite** - Build tool para assets

### Librerías y Paquetes
- **maatwebsite/excel** - Importación/Exportación Excel
- **Laravel Tinker** - REPL interactivo
- **Laravel Pint** - Code style fixer

## 💻 Desarrollo

### Comandos Útiles

```bash
# Ejecutar tests
php artisan test

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Compilar assets en modo desarrollo
npm run dev

# Compilar assets para producción
npm run build

# Ejecutar migraciones
php artisan migrate

# Rollback de migraciones
php artisan migrate:rollback

# Crear migración
php artisan make:migration nombre_migracion

# Crear modelo
php artisan make:model NombreModelo

# Crear controlador
php artisan make:controller NombreController

# Iniciar Tinker
php artisan tinker
```

### Estructura de Base de Datos

**Tablas principales:**
- `users` - Usuarios del sistema
- `jugadores` - Jugadores del equipo
- `proveedores` - Proveedores
- `gastos` - Gastos del equipo
- `gasto_jugador` - Tabla pivote para reparto de gastos
- `pagos_jugadores` - Pagos realizados por jugadores

### Modelos y Relaciones

- **Jugador** ↔ **Gasto** (muchos a muchos con `importe_asignado`)
- **Jugador** → **PagoJugador** (uno a muchos)
- **Gasto** → **Proveedor** (muchos a uno)
- **User** → Roles y permisos

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Estándares de Código

- Sigue las convenciones de código de Laravel
- Ejecuta `php artisan pint` antes de hacer commit
- Escribe tests para nuevas funcionalidades cuando sea posible
- Documenta tu código cuando sea necesario

## 📝 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.

## 👨‍💻 Autor

Desarrollado con ❤️ para la gestión eficiente de equipos deportivos.

---

**Nota**: Este es un proyecto en desarrollo activo. Si encuentras algún problema o tienes sugerencias, por favor abre un issue en el repositorio.
