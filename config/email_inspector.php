<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Disposable Email Domains
    |--------------------------------------------------------------------------
    | A non-exhaustive list of known disposable/temporary mail providers.
    */
    'disposable_domains' => [
        'mailinator.com',
        'trashmail.com',
        '10minutemail.com',
        'temp-mail.org',
        'yopmail.com',
        'guerrillamail.com',
        'fakeinbox.com',
        'maildrop.cc',
        'dispostable.com',
        'sharklasers.com',
        'spamgourmet.com',
        'throwawaymail.com',
        'tempmail.net',
        'getnada.com',
        'mintemail.com',
        'disposablemail.com',
        'emailondeck.com',
        'mailnesia.com',
        'spambox.org',
        'temp-mail.io',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role-based Local Parts
    |--------------------------------------------------------------------------
    | Addresses like admin@, support@, postmaster@, etc. denote roles, not individuals.
    */
    'role_local_parts' => [
        'admin',
        'administrator',
        'support',
        'help',
        'info',
        'sales',
        'billing',
        'postmaster',
        'webmaster',
        'abuse',
        'security',
        'noc',
        'office',
        'contact',
        'no-reply',
        'noreply',
        'root',
        'smtp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Common Domains
    |--------------------------------------------------------------------------
    | Popular email providers (global + Switzerland + Germany) for typo suggestions.
    */
    'common_domains' => [
        // Global majors
        'gmail.com',
        'googlemail.com',
        'yahoo.com',
        'ymail.com',
        'hotmail.com',
        'live.com',
        'outlook.com',
        'icloud.com',
        'aol.com',
        'msn.com',
        'protonmail.com',
        'zoho.com',
        'mail.com',

        // German providers (.de)
        'gmx.de',
        'web.de',
        't-online.de',
        'freenet.de',
        'posteo.de',
        'gmx.net',        // also used in Germany
        'outlook.de',

        // Swiss providers (.ch)
        'gmx.ch',
        'bluewin.ch',
        'sunrise.ch',
        'mail.ch',
        'hin.ch',
        'swissmail.org',
        'blueillusion.ch',
        'quickline.ch',

        // Other regional/common
        'yandex.com',
        'yandex.ru',
        'qq.com',
    ],

];
