<?php

namespace App\Repository;

use App\Application\ConfigLoader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

class EntityManagerFactory
{

    /**
     * @var EntityManager
     */
    private static $entityManager = null;

    private function __construct()
    {
    }

    private static function createEntityManager()
    {
        $settings = ConfigLoader::getInstance();
        $config = Setup::createAnnotationMetadataConfiguration(
            array(__DIR__ . "/src/Model/User.php"),
            $settings->get('emsettings', 'isDevMode'),
            $settings->get('emsettings', 'proxyDir'),
            $settings->get('emsettings', 'cache'),
            $settings->get('emsettings', 'useSimpleAnnotationReader')
        );

        $conn = \Doctrine\DBAL\DriverManager::getConnection($settings->getSettings('database'));
        self::$entityManager = EntityManager::create($conn, $config);
    }

    public static function getEntityManager(): EntityManager
    {
        if (self::$entityManager === null) self::createEntityManager();
        return self::$entityManager;
    }
}
