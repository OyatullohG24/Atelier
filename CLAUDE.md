# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application using Vue 3 + Inertia.js for the frontend. The project is called "Atelier" and uses:
- **Backend**: Laravel 12, PHP 8.2+, Laravel Fortify for authentication, Pest for testing
- **Frontend**: Vue 3, TypeScript, Inertia.js, Tailwind CSS v4, reka-ui components
- **Build Tools**: Vite, Laravel Wayfinder (for type-safe routing)

## Development Commands

### Setup
```bash
composer setup  # Full setup: install dependencies, copy .env, generate key, migrate, build assets
```

### Development Server
```bash
composer dev    # Runs concurrently: Laravel server, queue worker, logs (Pail), and Vite dev server
composer dev:ssr  # Same as above but with SSR support using Inertia SSR
```

### Frontend
```bash
npm run dev           # Start Vite dev server
npm run build         # Build assets for production
npm run build:ssr     # Build with SSR support
npm run lint          # Run ESLint with auto-fix
npm run format        # Format code with Prettier
npm run format:check  # Check code formatting
```

### Backend
```bash
php artisan serve           # Start Laravel development server
php artisan queue:listen    # Run queue worker
php artisan pail            # View logs in real-time
php artisan migrate         # Run database migrations
php artisan tinker          # Laravel REPL
```

### Testing
```bash
composer test        # Run Pest test suite (clears config cache first)
php artisan test     # Direct Pest test runner
```

### Code Quality
```bash
vendor/bin/pint      # Run Laravel Pint (PHP code formatter)
```

## Architecture

### Route Management with Wayfinder

This project uses **Laravel Wayfinder** to generate type-safe TypeScript route definitions from Laravel routes.

- Laravel routes are defined in `routes/web.php` and `routes/settings.php`
- Wayfinder automatically generates TypeScript route helpers in `resources/js/routes/index.ts`
- **Generated files**: The entire `resources/js/routes/` directory is auto-generated - do not manually edit these files
- To add new routes: Define them in PHP route files, then rebuild with `npm run build` or restart `npm run dev`
- Usage in Vue components:
  ```typescript
  import { home, dashboard, login } from '@/routes'

  // Generate URL
  home.url()  // returns '/'

  // Use with Inertia router
  router.visit(dashboard.url())
  ```

### Frontend Structure

- **Pages**: `resources/js/pages/` - Inertia.js page components
  - Auth pages: `auth/` (login, register, password reset, verification)
  - Settings pages: `settings/` (profile, password, appearance, 2FA)
  - Main pages: `Dashboard.vue`, `Welcome.vue`, `clothes.vue`
- **Layouts**: `resources/js/layouts/` - Layout wrappers
  - `AppLayout.vue` - Main authenticated layout
  - `AuthLayout.vue` - Authentication pages layout
  - Nested layout components in `app/`, `auth/`, `settings/` subdirectories
- **Components**: `resources/js/components/` - Reusable Vue components
  - UI components: App shell, header, sidebar, navigation
  - Form components: Input error handling, alerts
  - Feature components: User deletion, 2FA, appearance settings
- **Composables**: `resources/js/composables/` - Vue composables for shared logic
  - Theme/appearance management via `useAppearance.ts`
- **Actions**: `resources/js/actions/` - Client-side action handlers
- **Entry Points**:
  - `resources/js/app.ts` - Main SPA entry point
  - `resources/js/ssr.ts` - SSR entry point

### Backend Structure

- **Models**: `app/Models/` - Eloquent models
- **Controllers**: `app/Http/Controllers/`
  - Main controllers in root
  - `Settings/` subdirectory for user settings (Profile, Password, 2FA)
- **Middleware**: `app/Http/Middleware/`
  - `HandleInertiaRequests.php` - Shares global data to all Inertia pages (auth user, app name, quote, sidebar state)
  - `HandleAppearance.php` - Manages appearance/theme preferences
- **Actions**: `app/Actions/Fortify/` - Laravel Fortify actions for authentication
  - User registration, password validation, password reset
- **Providers**: `app/Providers/`
  - `AppServiceProvider.php` - Application service provider
  - `FortifyServiceProvider.php` - Fortify authentication configuration

### Authentication

Uses **Laravel Fortify** for authentication features:
- Registration, login, logout
- Password reset
- Email verification
- Two-factor authentication (2FA)
- Profile management

All auth routes are automatically registered by Fortify. Custom auth views use Inertia.js.

### Shared Inertia Props

Global props available on all pages (defined in `HandleInertiaRequests.php`):
- `name` - Application name
- `quote` - Random inspiring quote with author
- `auth.user` - Currently authenticated user
- `sidebarOpen` - Sidebar state preference

### Database

- Default connection: SQLite (`:memory:` for tests)
- Migrations in `database/migrations/`
- Test configuration in `phpunit.xml`

## Important Notes

- The `resources/js/routes/` directory is auto-generated by Wayfinder - never edit manually
- When adding new routes, define them in Laravel route files, not in TypeScript
- The project uses Pest for testing, not PHPUnit syntax
- Tailwind CSS v4 is configured via Vite plugin (`@tailwindcss/vite`)
- Component library: reka-ui (headless UI components)
- Icons: lucide-vue-next
