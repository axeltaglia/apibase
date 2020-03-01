<?php

namespace App\Repository;

use App\Entity\Sample;
use App\Entity\Product;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ProductRepository extends QueryBuilder
{

    public function __construct(RegistryInterface $registry, LoggerInterface $logger)
    {
        parent::__construct(Product::class, $registry, $logger);
    }

}


