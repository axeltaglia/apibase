<?php
namespace App\EntityType;

class ProductsListType extends EntityType
{
    protected function buildFields()
    {
        $this->config('snake_case')
            ->field('products', [
                'type' => [ProductType::class]
            ]);
    }
}


