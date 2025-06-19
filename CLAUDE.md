# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Start Development Environment
```bash
composer dev  # Starts Laravel server, queue worker, logs, and Vite dev server
npm run dev   # Vite development server only
```

### Testing
```bash
composer test  # Run Pest test suite
pest           # Run specific test files
```

### Code Quality
```bash
npm run lint    # ESLint for JavaScript/TypeScript
npm run format  # Prettier formatting
./vendor/bin/pint  # Laravel Pint for PHP formatting
```

### Building
```bash
npm run build      # Production build
npm run build:ssr  # Build with Server-Side Rendering
composer dev:ssr   # Development with SSR
```

## Architecture Overview

**GuitarPrime** is a Laravel-Vue.js SPA using Inertia.js for seamless full-stack integration. The application is built around guitar education with role-based access control.

### Tech Stack
- **Backend**: Laravel 12, Pest testing, MySQL/SQLite
- **Frontend**: Vue.js 3, TypeScript, Tailwind CSS, shadcn-vue component library for UI
- **Integration**: Inertia.js 2.0, Ziggy (route helpers)
- **Build**: Vite 6.2 with HMR

### Domain Model
The application centers around a guitar education platform:
- **Users** with roles (admin, coach, student) 
- **Themes** → **Courses** → **Modules** → **Attachments**
- Courses and Modules have many-to-many relations
- Course approval workflow (coaches create, admins approve)
- Role-based authorization via Laravel Policies

### Key Directories
- `app/Models/` - Domain models (User, Theme, Course, Module, Attachment)
- `app/Policies/` - Authorization logic for domain objects
- `resources/js/pages/` - Vue page components organized by feature
- `resources/js/components/ui/` - Complete UI component library
- `tests/Feature/` - Integration tests using Pest framework

### Frontend Architecture
- Component-based Vue.js with TypeScript
- Tailwind CSS for styling with Reka UI headless components
- Composables in `resources/js/composables/` for shared logic
- Inertia.js pages receive props from Laravel controllers
- Route helpers via Ziggy for frontend navigation

### Testing Strategy
- **Pest framework** for all tests (preferred over PHPUnit)
- Feature tests cover authentication, CRUD operations, and policies
- SQLite in-memory database for testing
- GitHub Actions CI runs tests and linting

### Development Patterns
- Follow Laravel conventions for backend (controllers, models, policies)
- Use Vue Composition API with TypeScript for frontend components
- Inertia.js props for data flow from Laravel to Vue
- Role-based middleware and policies for access control
- Social authentication via Laravel Socialite

## Current State

The project is actively developing theme management functionality with CRUD operations, policies, and comprehensive testing coverage.