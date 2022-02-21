<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository as ORMEntityRepository;

class EntityRepository extends ORMEntityRepository
{

    public function __construct($class)
    {
        $em = EntityManagerFactory::getEntityManager();
        parent::__construct($em, $em->getClassMetadata($class));
    }

    public function getRepository()
    {
        return $this->getEntityManager()->getRepository($this->_class);
    }
}
