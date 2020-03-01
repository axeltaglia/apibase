<?php

namespace App\DataFixtures;

use App\Services\UserService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function load(ObjectManager $manager)
    {
        $this->userService->create([
            'name' => 'admin',
            'surname' => 'admin',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'roles' => ['ADMIN'],
            'password' => 'admin'
        ]);
    }
}
