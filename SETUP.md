# Northwood admin dashboard: setup

## 1. Icons
```bash
composer require blade-ui-kit/blade-heroicons
```

## 2. Copy files into your Laravel project
```
app/Http/Controllers/Admin/DashboardController.php
resources/views/admin/dashboard.blade.php
resources/views/components/layouts/admin.blade.php
resources/views/components/line-chart.blade.php
resources/views/components/stat-card.blade.php
resources/views/components/badge.blade.php
```

## 3. Route (routes/web.php)
```php
use App\Http\Controllers\Admin\DashboardController;

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    // Menu items without a route yet link to "#".
});
```
Add `'role:admin'` to the middleware once Spatie Permission is set up.

## 3b. (Optional) Share the signed-in user with the layout
In `AppServiceProvider::boot()`: `View::share('head', [...]);`
Until then the layout shows "Dr. James Wilson".

## 4. Design tokens (tailwind.config.js)
These colors were estimated by eye from your screenshots. Replace them with the exact
values from Figma (Dev Mode, select an element, copy the color).
```js
import defaultTheme from 'tailwindcss/defaultTheme';

export default {
  content: ['./resources/views/**/*.blade.php', './app/**/*.php'],
  theme: {
    extend: {
      colors: {
        primary: { DEFAULT: '#2456D6', hover: '#1D47B5', soft: '#E8EEFC' },
        ink:     { DEFAULT: '#0F1B3D', soft: '#5B6577', muted: '#8A93A5' },
        navy: '#14275E',
        line: '#E4E8F1',
        canvas: '#F4F6FB',
        success: { DEFAULT: '#15803D', soft: '#E3F5EA' },
        warning: { DEFAULT: '#B7791F', soft: '#FDF0DC' },
        danger:  { DEFAULT: '#C93B3B', soft: '#FDECEC' },
      },
      fontFamily: { sans: ['Inter', ...defaultTheme.fontFamily.sans] },
    },
  },
};
```

## 5. Run
```bash
npm run dev
php artisan serve
```
Open `/admin`.
