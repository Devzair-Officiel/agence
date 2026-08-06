<?php

use App\Kernel;

// Serveur PHP intégré (cli-server) : quand la stack dev tourne via
// `php -S 0.0.0.0:8000 -t public/ public/index.php` (voir apps/api/Dockerfile.dev),
// TOUTES les requêtes — y compris les assets statiques du dossier public/ —
// sont routées vers ce script. Sans court-circuit, un `<link rel="stylesheet"
// href="/admin/assets/admin.css">` reçoit une réponse `Content-Type: text/html`,
// que Chrome refuse d'appliquer comme CSS (mécanisme strict MIME activé par
// défaut sur les documents HTML5). Retourner `false` demande au serveur
// intégré de servir le fichier tel quel, avec le bon Content-Type.
//
// La garde est strictement scopée : on ne renvoie `false` que pour les
// fichiers RÉELS du dossier `public/` (donc jamais pour les routes Symfony)
// et uniquement quand on tourne sous `cli-server`. En prod (php-fpm derrière
// Caddy/nginx), la ligne est un no-op — le serveur amont sert les statiques
// avant d'atteindre PHP.
if (PHP_SAPI === 'cli-server') {
    $requestPath = urldecode((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH));
    if ($requestPath !== '' && $requestPath !== '/' && is_file(__DIR__.$requestPath)) {
        return false;
    }
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
