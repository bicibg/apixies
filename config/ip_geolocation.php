<?php

return [
    /*
    |--------------------------------------------------------------------------
    | IP Geolocation Service Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the IP Geolocation service.
    |
    */

    // Cache duration in hours
    'cache_duration' => env('IP_GEOLOCATION_CACHE_DURATION', 24),

    // Rate limiting for IP Geolocation API calls (per minute)
    'rate_limit' => env('IP_GEOLOCATION_RATE_LIMIT', 45),

    // IP API service URL - we use the free service by default
    // For production, consider using a paid service like ipinfo.io, ipstack, etc.
    'api_url' => env('IP_GEOLOCATION_API_URL', 'http://ip-api.com/json/'),

    // Include language data
    'include_languages' => env('IP_GEOLOCATION_INCLUDE_LANGUAGES', true),

    // Language data source
    // Options: 'internal', 'rest_countries', 'database'
    'language_data_source' => env('IP_GEOLOCATION_LANGUAGE_SOURCE', 'internal'),

    // API URL for REST Countries if that's the chosen source
    'rest_countries_url' => env('REST_COUNTRIES_API_URL', 'https://restcountries.com/v3.1/alpha/'),

    // Additional fields to include in the response
    'fields' => [
        'country',
        'countryCode',
        'region',
        'regionName',
        'city',
        'zip',
        'lat',
        'lon',
        'timezone',
        'isp',
        'org',
        'as',
    ],

    /*
    |--------------------------------------------------------------------------
    | Language Mapping
    |--------------------------------------------------------------------------
    |
    | This maps country codes to their spoken languages.
    | Each language entry includes:
    | - code: ISO language code
    | - name: English name of the language
    | - native_name: Name in the language's own script
    | - official: Whether it's an official language of the country
    |
    */
    'language_map' => [
        'US' => [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'official' => true],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'official' => false]
        ],
        'CA' => [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'official' => true],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'official' => true]
        ],
        'MX' => [
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'official' => true],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'official' => false]
        ],
        'GB' => [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'official' => true],
            ['code' => 'cy', 'name' => 'Welsh', 'native_name' => 'Cymraeg', 'official' => true],
            ['code' => 'gd', 'name' => 'Scottish Gaelic', 'native_name' => 'Gàidhlig', 'official' => true]
        ],
        'DE' => [
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'official' => true]
        ],
        'FR' => [
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'official' => true]
        ],
        'ES' => [
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'official' => true],
            ['code' => 'ca', 'name' => 'Catalan', 'native_name' => 'Català', 'official' => true],
            ['code' => 'eu', 'name' => 'Basque', 'native_name' => 'Euskara', 'official' => true],
            ['code' => 'gl', 'name' => 'Galician', 'native_name' => 'Galego', 'official' => true]
        ],
        'IT' => [
            ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano', 'official' => true]
        ],
        'CH' => [
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'official' => true],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'official' => true],
            ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano', 'official' => true],
            ['code' => 'rm', 'name' => 'Romansh', 'native_name' => 'Rumantsch', 'official' => true]
        ],
        'JP' => [
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語', 'official' => true]
        ],
        'CN' => [
            ['code' => 'zh', 'name' => 'Chinese (Mandarin)', 'native_name' => '中文', 'official' => true],
            ['code' => 'ug', 'name' => 'Uyghur', 'native_name' => 'ئۇيغۇرچە', 'official' => false],
            ['code' => 'bo', 'name' => 'Tibetan', 'native_name' => 'བོད་སྐད་', 'official' => false]
        ],
        'IN' => [
            ['code' => 'hi', 'name' => 'Hindi', 'native_name' => 'हिन्दी', 'official' => true],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'official' => true],
            ['code' => 'bn', 'name' => 'Bengali', 'native_name' => 'বাংলা', 'official' => false],
            ['code' => 'te', 'name' => 'Telugu', 'native_name' => 'తెలుగు', 'official' => false],
            ['code' => 'mr', 'name' => 'Marathi', 'native_name' => 'मराठी', 'official' => false]
        ],
        'RU' => [
            ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский', 'official' => true]
        ],
        'BR' => [
            ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Português', 'official' => true]
        ],
        'AU' => [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'official' => true]
        ],
        'ZA' => [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'official' => true],
            ['code' => 'af', 'name' => 'Afrikaans', 'native_name' => 'Afrikaans', 'official' => true],
            ['code' => 'zu', 'name' => 'Zulu', 'native_name' => 'isiZulu', 'official' => true],
            ['code' => 'xh', 'name' => 'Xhosa', 'native_name' => 'isiXhosa', 'official' => true]
        ],
        'EG' => [
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'official' => true]
        ],
        'NG' => [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'official' => true],
            ['code' => 'ha', 'name' => 'Hausa', 'native_name' => 'هَوُسَ', 'official' => false],
            ['code' => 'yo', 'name' => 'Yoruba', 'native_name' => 'Yorùbá', 'official' => false],
            ['code' => 'ig', 'name' => 'Igbo', 'native_name' => 'Igbo', 'official' => false]
        ],
        // More countries
        'NL' => [
            ['code' => 'nl', 'name' => 'Dutch', 'native_name' => 'Nederlands', 'official' => true],
            ['code' => 'fy', 'name' => 'Frisian', 'native_name' => 'Frysk', 'official' => true]
        ],
        'BE' => [
            ['code' => 'nl', 'name' => 'Dutch', 'native_name' => 'Nederlands', 'official' => true],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'official' => true],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'official' => true]
        ],
        'PT' => [
            ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Português', 'official' => true]
        ],
        'SE' => [
            ['code' => 'sv', 'name' => 'Swedish', 'native_name' => 'Svenska', 'official' => true]
        ],
        'NO' => [
            ['code' => 'no', 'name' => 'Norwegian', 'native_name' => 'Norsk', 'official' => true]
        ],
        'DK' => [
            ['code' => 'da', 'name' => 'Danish', 'native_name' => 'Dansk', 'official' => true]
        ],
        'FI' => [
            ['code' => 'fi', 'name' => 'Finnish', 'native_name' => 'Suomi', 'official' => true],
            ['code' => 'sv', 'name' => 'Swedish', 'native_name' => 'Svenska', 'official' => true]
        ],
        'PL' => [
            ['code' => 'pl', 'name' => 'Polish', 'native_name' => 'Polski', 'official' => true]
        ],
        'CZ' => [
            ['code' => 'cs', 'name' => 'Czech', 'native_name' => 'Čeština', 'official' => true]
        ],
        'GR' => [
            ['code' => 'el', 'name' => 'Greek', 'native_name' => 'Ελληνικά', 'official' => true]
        ],
        'IL' => [
            ['code' => 'he', 'name' => 'Hebrew', 'native_name' => 'עברית', 'official' => true],
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'official' => true]
        ],
        'TR' => [
            ['code' => 'tr', 'name' => 'Turkish', 'native_name' => 'Türkçe', 'official' => true]
        ],
        'TH' => [
            ['code' => 'th', 'name' => 'Thai', 'native_name' => 'ไทย', 'official' => true]
        ],
        'VN' => [
            ['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'official' => true]
        ],
        'KR' => [
            ['code' => 'ko', 'name' => 'Korean', 'native_name' => '한국어', 'official' => true]
        ],
        'ZW' => [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'official' => true],
            ['code' => 'sn', 'name' => 'Shona', 'native_name' => 'chiShona', 'official' => true],
            ['code' => 'nd', 'name' => 'Northern Ndebele', 'native_name' => 'isiNdebele', 'official' => true]
        ],
    ],
];
