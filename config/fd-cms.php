<?php

return [
    'google_cloud_project' => env('FD_CMS_GOOGLE_CLOUD_PROJECT'),
    'google_application_credentials' => env('FD_CMS_GOOGLE_APPLICATION_CREDENTIALS'),
    
    'media' => [
        'disk_name' => env('FD_CMS_MEDIA_DISK_NAME', 'media'),
        'fallback_url' => 'data:image/gif;base64,R0lGODlhAQABAHAAACwAAAAAAQABAIHu7u4AAAAAAAAAAAACAkQBADs='
    ]
];
