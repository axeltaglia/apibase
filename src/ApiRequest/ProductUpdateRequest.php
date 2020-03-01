<?php

namespace App\ApiRequest;

class ProductUpdateRequest extends ApiRequest
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