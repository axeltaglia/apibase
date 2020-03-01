<?php
namespace App\EntityType;

class ProductType extends EntityType
{
    protected function buildFields()
    {
        $this->config('snake_case')
            ->field('id')
            ->field('name')
            ->field('description');
    }
}


