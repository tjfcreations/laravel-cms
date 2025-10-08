<?php

return [
    'google_cloud_project' => env('FD_CMS_GOOGLE_CLOUD_PROJECT'),
    'google_application_credentials' => env('FD_CMS_GOOGLE_APPLICATION_CREDENTIALS'),

    'i18n' => [
        'enabled' => true,
        'user_model' => 'App\Models\User'
    ],

    'media' => [
        'enabled' => true,
        'disk_name' => env('FD_CMS_MEDIA_DISK_NAME', 'media'),
        'fallback_url' => 'data:image/gif;base64,R0lGODlhAQABAHAAACwAAAAAAQABAIHu7u4AAAAAAAAAAAACAkQBADs='
    ],

    'pagebuilder' => [
        'menu' => [
            'max_depth' => 1,
        ],
    ],
];
