<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    | Les chemins pour lesquels le CORS doit être appliqué. Exemple :
    | 'api/*' pour toutes les routes API.
    |
    */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    |
    | Les méthodes HTTP autorisées. '*' signifie toutes les méthodes.
    |
    */
    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Les origines autorisées à faire des requêtes. '*' pour toutes les origines.
    | En production, remplacez '*' par votre domaine exact pour plus de sécurité.
    |
    */
    'allowed_origins' => ['http://localhost:5173'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins Patterns
    |--------------------------------------------------------------------------
    |
    | Motifs regex pour autoriser des origines dynamiques.
    |
    */
    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | Les headers autorisés dans les requêtes CORS. '*' autorise tous les headers.
    |
    */
    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | Les headers exposés côté client.
    |
    */
    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    |
    | Durée en secondes pendant laquelle les résultats pré-vol sont mis en cache.
    |
    */
    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | Indique si les cookies et headers d’authentification sont autorisés.
    |
    */
    'supports_credentials' => false,

];
