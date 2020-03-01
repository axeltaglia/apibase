<?php

namespace App\EntityType;

class UserType extends EntityType
{
    protected function buildFields()
    {
        $this
            ->config('snake_case')
            ->field('name')
            ->field('surname')
            ->field('username')
            ->field('email')
        ;
    }
}


