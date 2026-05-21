<?php

return [
    'temporary_file_upload' => [
        'rules' => ['file', 'mimes:jpeg,jpg,png', 'mimetypes:image/jpeg,image/png', 'max:4096'],
        'max_upload_time' => 5,
    ],
];
