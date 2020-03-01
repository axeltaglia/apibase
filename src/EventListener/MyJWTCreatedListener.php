<?php
namespace App\EventListener;
use App\Services\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class MyJWTCreatedListener extends JWTCreatedEvent
{
    private $requestStack;
    private $userService;

    public function __construct(RequestStack $requestStack, UserService $userService)
    {
        $this->requestStack = $requestStack;
        $this->userService = $userService;
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $payload = $event->getData();

        // expiration
        $expiration = new \DateTime('+1 month');
        $expiration->setTime(2, 0, 0);
        $payload['exp'] = $expiration->getTimestamp();

        $event->setData($payload);
        $header = $event->getHeader();
        $header['cty'] = 'JWT';

        $event->setHeader($header);
    }
}