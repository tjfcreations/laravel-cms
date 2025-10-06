# Configuration

This document covers the configuration options available in the Laravel CMS package.

## Configuration File

The main configuration file is located at `config/fd-cms.php` after publishing.

## Translation Configuration

### Google Translate API

Configure automatic translation settings:

```php
'i18n' => [
    'google_translate_api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
    'google_project_id' => env('GOOGLE_PROJECT_ID'),
],
```

### Model Translation Settings

Define translatable attributes in your models:

```php
// Simple array - enables machine translation for all
protected $translate = ['title', 'content', 'excerpt'];

// Detailed configuration
protected $translate = [
    'title' => true,     // Auto-translate
    'content' => true,   // Auto-translate
    'slug' => false,     // Manual only
];
```

## Filament Plugin Configuration

Customize the Filament plugin registration:

```php
CMSPlugin::make()
    ->navigationGroup('Content Management')
    ->navigationIcon('heroicon-o-document-text')
```

## Locales

Configure available locales in `config/app.php`:

```php
'available_locales' => ['en', 'nl', 'fr', 'de', 'es'],
'locale' => 'en',
'fallback_locale' => 'en',
```
