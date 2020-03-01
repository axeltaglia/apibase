<?php

namespace App\Services;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService extends ResourceService
{
    private $encoder;
    private $tokenStorage;
    private $currentUser;

    public function __construct(LoggerInterface $logger, ObjectManager $entityManager, ValidatorInterface $validator, ObjectPopulatorService $populator, SerializerInterface $serializer, Security $security, TokenStorageInterface $tokenStorage, UserPasswordEncoderInterface $encoder)
    {
        parent::__construct($logger, $entityManager, $validator, $populator, $serializer, $security);
        $this->encoder = $encoder;
        $this->tokenStorage = $tokenStorage;
    }

    public function _findByUsername($username)
    {
        $this->findByUsername($username);
        if ($this->resource) return $this->resource;
        throw new Exception("User not found.");
    }

    public function findByUsername($username)
    {
        return $this->setResource($this->repository
            ->select('u')
            ->where('u.username', $username)
            ->getOneObject());
    }

    public function generateUserToken($user, $jwtService): string
    {
        $token = $jwtService
            ->encode([
                'username' => $user->getUsername(),
                'exp' => time() + 36000
            ]);
        return $token;
    }

    public function _currentUser()
    {
        if ($this->currentUser()) return $this->currentUser;
        throw new Exception("Token Expired.");
    }

    public function currentUser()
    {
        if ($this->tokenStorage->getToken()) {
            $this->currentUser = $this->tokenStorage->getToken()->getUser();
            $this->resource = $this->currentUser;
            return $this->currentUser;
        } else {
            return null;
        }
    }

    protected function beforeCreatePopulation()
    {
        $this->setPassword();
    }

    private function setPassword()
    {
        if ($this->getData('password')) $this->setData('password', $this->encoder->encodePassword($this->resource, $this->getData('password')));
    }

    protected function beforeUpdatePopulation()
    {
        $this->setPassword();
    }

    protected function getNewResource()
    {
        return new User();
    }

    protected function getResourceClassName(): string
    {
        return "User";
    }
}
