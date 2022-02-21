<?php

declare(strict_types=1);

namespace App\API;

use App\Application\ConfigLoader;
use App\Model\UserEntity;
use App\Repository\AccessTokenRepository;
use App\Repository\AuthCodeRepository;
use App\Repository\ClientRepository;
use App\Repository\RefreshTokenRepository;
use App\Repository\ScopeRepository;
use App\Repository\UserRepository;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App as SlimApp;

class AuthServerAPI
{

    private $authServer;
    private $basePath;

    public function __construct($basePath = "")
    {
        $this->basePath = $basePath;
        $settings = ConfigLoader::getInstance();

        // Init our repositories
        $clientRepository = new ClientRepository(); // instance of ClientRepositoryInterface
        $scopeRepository = new ScopeRepository(); // instance of ScopeRepositoryInterface
        $accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface
        $userRepository = new UserRepository(); // instance of UserRepositoryInterface
        $refreshTokenRepository = new RefreshTokenRepository(); // instance of RefreshTokenRepositoryInterface
        $authCodeRepository = new AuthCodeRepository(); // instance of AuthCodeRepositoryInterface

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

        $passwordGrant = new \League\OAuth2\Server\Grant\PasswordGrant(
            $userRepository,
            $refreshTokenRepository
        );

        $passwordGrant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

        // Enable the password grant on the server
        $this->authServer->enableGrantType(
            $passwordGrant,
            new \DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        $autoCodeGrant = new \League\OAuth2\Server\Grant\AuthCodeGrant(
            $authCodeRepository,
            $refreshTokenRepository,
            new \DateInterval('PT10M') // authorization codes will expire after 10 minutes
        );

        $autoCodeGrant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

        // Enable the authentication code grant on the server
        $this->authServer->enableGrantType(
            $autoCodeGrant,
            new \DateInterval('PT1H') // access tokens will expire after 1 hour
        );
    }

    public function addRequests(SlimApp $app)
    {
        $app->post("{$this->basePath}/access_token", array($this, 'access_token'));
        $app->get("{$this->basePath}/authorize", array($this, 'startAuthCodeFlow'));
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

    public function startAuthCodeFlow(Request $request, Response $response)
    {
        try {
            // Validate the HTTP request and return an AuthorizationRequest object.
            // The auth request object can be serialized into a user's session
            $authRequest = $this->authServer->validateAuthorizationRequest($request);

            // Once the user has logged in set the user on the AuthorizationRequest
            $authRequest->setUser($this->testing_user());

            // Once the user has approved or denied the client update the status
            // (true = approved, false = denied)
            $authRequest->setAuthorizationApproved(true);

            // Return the HTTP redirect response
            return $this->authServer->completeAuthorizationRequest($authRequest, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        } catch (\Exception $exception) {
            $body = $response->getBody();
            $body->write($exception->getMessage());
            return $response->withStatus(500)->withBody($body);
        }
    }

    private function testing_user()
    {
        $user = new UserEntity();
        $user->setActive(true);
        $user->setId(1);
        $user->setFirstName("Testing");
        $user->setLastName("Testing");
        $user->setEmail("testuser@example.com");
        $user->setCreated("2022-02-02");
        return $user;
    }
}
