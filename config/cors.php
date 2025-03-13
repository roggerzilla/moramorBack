<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Rutas a las que se aplicará CORS
    'allowed_methods' => ['*'], // Métodos HTTP permitidos
    'allowed_origins' => ['http://localhost:4200'], // Orígenes permitidos
    'allowed_origins_patterns' => [], // Patrones de orígenes permitidos
    'allowed_headers' => ['*'], // Cabeceras permitidas
    'exposed_headers' => [], // Cabeceras expuestas
    'max_age' => 0, // Tiempo de caché para las respuestas CORS
    'supports_credentials' => true, // Permitir credenciales (cookies, encabezados de autenticación, etc.)
];