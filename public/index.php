<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';

// Charge les variables d'environnement
if (file_exists(dirname(__DIR__).'/.env')) {
    (new Dotenv())->loadEnv(dirname(__DIR__).'/.env');
}

// Active le debug uniquement si APP_DEBUG=1
$debug = ($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? '0') === '1';
if ($debug) {
    umask( 0000 );
    Debug::enable();
}

// Force la création du dossier var/log si nécessaire
$logDir = dirname(__DIR__).'/var/log';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// Crée le kernel Symfony
$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', $debug);

// Crée la requête HTTP
$request = Request::createFromGlobals();

// Traite la requête et envoie la réponse
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
