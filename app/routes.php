<?php

declare(strict_types=1);

use App\API\OAuthServerAPI;
use App\API\PasswordGrantOAuthServerAPI;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App as SlimApp;

return function (SlimApp $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    // Password grant
    // $oauthServer = new OAuthServerAPI("/oauth");
    // $oauthServer->addRequests($app);

    $oauthServer = new PasswordGrantOAuthServerAPI("/oauth");
    $oauthServer->addRequests($app);
};
