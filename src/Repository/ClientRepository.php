<?php

namespace App\Repository;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use App\Model\ClientEntity;
use App\Application\PasswordHelper;

class ClientRepository implements ClientRepositoryInterface
{
    const CLIENT_NAME = 'Some client name'; // TODO: Read from environment
    const REDIRECT_URI = 'http://localhost:8080/'; // TODO: Read from environment

    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier)
    {
        $client = new ClientEntity();

        $client->setIdentifier($clientIdentifier);
        $client->setName(self::CLIENT_NAME);
        $client->setRedirectUri(self::REDIRECT_URI);
        $client->setConfidential();

        return $client;
    }

    /**
     * {@inheritdoc}
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        $clients = [
            'some-client-name' => [
                'secret'          => PasswordHelper::hash('abc123'), // TODO: Read secret from environment
                'name'            => self::CLIENT_NAME,
                'redirect_uri'    => self::REDIRECT_URI,
                'is_confidential' => true,
            ],
        ];

        // Check if client is registered
        if (array_key_exists($clientIdentifier, $clients) === false) {
            return false;
        }

        if (
            $clients[$clientIdentifier]['is_confidential'] === true
            && PasswordHelper::verify($clientSecret, $clients[$clientIdentifier]['secret']) === false
        ) {
            return false;
        }

        return true;
    }
}
