<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// PHPUnit charge ce bootstrap AVANT d'appliquer les `<env force="true">` de
// phpunit.xml. On force `APP_ENV=test` ici pour que Symfony/Dotenv charge
// `.env.test` et pour que le kernel de test soit sélectionné dès la première
// instanciation. Le nom de base test (`devzair_test`) est appliqué par
// `dbname_suffix: '_test'` dans `config/packages/doctrine.yaml` (bloc @test).
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
putenv('APP_ENV=test');

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
