<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Grading settings editors
    |--------------------------------------------------------------------------
    | Usernames (login name, without the FARMERSCHOICE\ prefix) allowed to open
    | QA > Settings and change grading parameters. Comma separated in .env:
    |   GRADING_SETTINGS_USERS=jdoe,asmith
    | Empty means nobody can edit. Run `php artisan config:clear` after
    | changing it if config is cached.
    */
    'settings_users' => array_values(array_filter(array_map(
        fn ($u) => strtolower(trim($u)),
        explode(',', (string) env('GRADING_SETTINGS_USERS', ''))
    ))),

];
