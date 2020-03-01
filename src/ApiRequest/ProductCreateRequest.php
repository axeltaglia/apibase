<?php

namespace App\ApiRequest;

class ProductCreateRequest extends ApiRequest
{

    protected function buildParameters()
    {
        $this
            ->add('name', [
                'required' => true
            ])
            ->add('description', [
                'required' => true
            ]);
    }
}