# Internationalization (I18n)

This guide covers the multi-language features of the Laravel CMS package.

## Defining available locales

The available locales can be defined by creating them in the [FilamentPHP admin panel](http://localhost:8000/admin/fd-cms-locales).

### Setting a default locale

The default locale should match the original language of the site.

### Setting up machine translations

Machine translations can be enabled per-language. For more information on setting up machine translations, see Setting up Google Cloud Translate.

## Making Models Translatable

To make your models translatable, implement the `TranslatableInterface` and use the `Translatable` trait:

```php
use Feenstra\CMS\I18n\Interfaces\TranslatableInterface;
use Feenstra\CMS\I18n\Traits\Translatable;

class Article extends Model implements TranslatableInterface
{
    use Translatable;

    // Define translatable attributes
    protected $translate = [
        'title' => true,     // Disable machine translation (manual only)
        'content' => true,   // Enable machine translation
    ];
}
```

## The $translate property

The `$translate` property can be configured in two ways:

### Simple array

```php
// Enables machine translation for all listed attributes
protected $translate = ['title', 'content'];
```

### Associative array

```php
// Control machine translation per attribute
protected $translate = [
    'title' => true,     // Disable machine translation (manual only)
    'content' => true,   // Enable automatic machine translation
];
```

## Setting up Google Cloud Translate

### Creating a Service Account

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create or select a project
3. Enable the Cloud Translation API:
   - Go to "APIs & Services" > "Library"
   - Search for "Cloud Translation API"
   - Click on it and press "Enable"
4. Create a Service Account:
   - Go to "IAM & Admin" > "Service Accounts"
   - Click "Create Service Account"
   - Give it a name (e.g., "translation-service")
   - Assign the "Cloud Translate API User" role
   - Click "Done"
5. Generate a JSON Key:
   - Click on your newly created service account
   - Go to the "Keys" tab
   - Click "Add Key" > "Create new key"
   - Select "JSON" format
   - Download the JSON file

### Installing the Service Account Key

1. Place the downloaded JSON file in your Laravel project's `storage/` directory
2. Set the absolute path in your `.env` file:

```env
# Example paths:
# Linux/Mac: /var/www/html/your-app/storage/credentials/google-service-account.json
# Windows: C:\\path\\to\\your\\app\\storage\\credentials\\google-service-account.json
FD_CMS_GOOGLE_APPLICATION_CREDENTIALS=/absolute/path/to/your/app/storage/credentials/google-service-account.json
FD_CMS_GOOGLE_PROJECT_ID=your-google-cloud-project-id
```

### Security Considerations

- **Never commit the JSON key file to version control**
- Add `storage/credentials/google-service-account.json` to your `.gitignore` file
- Ensure the `storage/credentials/` directory has proper permissions (readable by your web server)
- Use environment-specific service accounts for production vs development

## Translation Management

### Manual Translation

Users can manually override machine translations through the admin interface.

### Automatic Translation

When enabled, the system automatically generates translations for new content using Google Translate.

### Fallback Behavior

If a translation doesn't exist for the requested locale, the system falls back to the default locale.
