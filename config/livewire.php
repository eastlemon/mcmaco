<?php

return [

    'temporary_file_upload' => [

        /*
        |------------------------------------------------------------------
        | Livewire temporary file uploads
        |------------------------------------------------------------------
        |
        | Livewire stores uploads in a temporary directory before the file
        | is stored permanently. The default rule caps files at 12 MB,
        | which silently rejects product import archives (CSV + ZIP with
        | photos) on the client side — raised to match the PHP CLI
        | upload limits on the stage (upload_max_filesize = 256M).
        |
        */

        'disk' => env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK'),
        'rules' => ['required', 'file', 'max:262144'], // 256 MB, KB units
        'directory' => null, // default: livewire-tmp
        'middleware' => null, // default: throttle:60,1

        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],

        'max_upload_time' => 5,

        'cleanup' => true,
    ],

];