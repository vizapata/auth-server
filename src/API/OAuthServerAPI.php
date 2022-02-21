<?php

declare(strict_types=1);

namespace App\API;

use App\Application\ConfigLoader;
use App\Repository\AccessTokenRepository;
use App\Repository\AuthCodeRepository;
use App\Repository\ClientRepository;
use App\Repository\RefreshTokenRepository;
use App\Repository\ScopeRepository;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App as SlimApp;

class OAuthServerAPI
{

    private $authServer;
    private $basePath;

    public function __construct($basePath = "")
    {
        $this->basePath = $basePath;
        $settings = ConfigLoader::getInstance();

        $clientRepository = new ClientRepository(); // instance of ClientRepositoryInterface
        $scopeRepository = new ScopeRepository; // instance of ScopeRepositoryInterface
        $accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface
        $authCodeRepository = new AuthCodeRepository(); // instance of AuthCodeRepositoryInterface
        $refreshTokenRepository = new RefreshTokenRepository(); // instance of RefreshTokenRepositoryInterface

        $privateKey = new CryptKey($settings->get('oauth', 'privateKeyPath'), null, false);
        $encryptionKey = $settings->get('oauth', 'encryptionKey');

        // Setup the authorization server
        $this->authServer = new \League\OAuth2\Server\AuthorizationServer(
            $clientRepository,
            $accessTokenRepository,
            $scopeRepository,
            $privateKey,
            $encryptionKey
        );

        $grant = new \League\OAuth2\Server\Grant\AuthCodeGrant(
            $authCodeRepository,
            $refreshTokenRepository,
            new \DateInterval('PT10M') // authorization codes will expire after 10 minutes
        );

        $grant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

        // Enable the authentication code grant on the server
        $this->authServer->enableGrantType(
            $grant,
            new \DateInterval('PT1H') // access tokens will expire after 1 hour
        );
    }

    public function addRequests(SlimApp $app)
    {
        $app->post("{$this->basePath}/access_token", array($this, 'access_token'));
    }

    public function access_token(Request $request, Response $response)
    {
        try {
            return $this->authServer->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        } catch (\Exception $exception) {
            $body = $response->getBody();
            $body->write($exception->getMessage());
            return $response->withStatus(500)->withBody($body);
        }
    }
}
