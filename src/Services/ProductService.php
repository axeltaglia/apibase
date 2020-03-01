<?php

namespace App\Services;

use App\Entity\Product;

class ProductService extends ResourceService
{
    protected function getNewResource()
    {
        return new Product();
    }

    protected function getResourceClassName(): string
    {
        return 'Product';
    }
}
