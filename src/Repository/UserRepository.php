<?php

namespace App\Repository;


use App\Application\PasswordHelper;
use App\Model\PagedResult;
use App\Model\UserEntity;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

class UserRepository extends EntityRepository implements UserRepositoryInterface
{

    public function __construct()
    {
        parent::__construct(UserEntity::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        $user = new UserEntity();
        $user->setActive(true);
        $user->setId(1);
        $user->setFirstName("Testing");
        $user->setLastName("Testing");
        $user->setEmail("testuser@example.com");
        $user->setCreated("2022-02-02");
        return $user;

        
        
        /*
        $user = $this->findOneBy(array('email' => $username));
        // TODO: Enable look for user by credentials
        if ($user !== null && PasswordHelper::verify($password, $user->getPassword())) {
            return $user;
        }

        return;
        */
    }
}
