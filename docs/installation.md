# Installation

This guide will walk you through installing and configuring the Laravel CMS package with Filament integration.

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- Filament 3.0 or higher

## Installation Steps

### 1. Install the Package

Install the package via Composer:

```bash
composer require feenstra/laravel-cms
```

### 2. Publish Migrations

Publish and run the database migrations:

```bash
php artisan vendor:publish --tag=cms-migrations
php artisan migrate
```

### 3. Publish Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=cms-config
```

This will create a `config/fd-cms.php` file where you can configure the CMS settings.

### 4. Add Plugin to Filament Panel

Register the CMS plugin with your Filament panel in `app/Providers/Filament/AdminPanelProvider.php`:

```php
use Feenstra\CMS\Filament\CMSPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...existing configuration...
        ->plugins([
            CMSPlugin::make(),
            // ...other plugins...
        ]);
}
```

## Troubleshooting

### Permission Issues

Ensure your storage directory is writable:

```bash
chmod -R 755 storage/
```

## Next Steps

- [Internationalization](internationalization.md) - Configure multi-language support
- [Page Builder](pagebuilder.md) - Build dynamic pages with components
- [Media Management](media.md) - Handle file uploads and media library
- [Configuration](configuration.md) - Advanced configuration options
