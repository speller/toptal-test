<?php
namespace App\Authentication;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Listens for kernel.action events and checks authentication
 */
class AuthenticationListener
{
    /**
     * @var AuthServiceInterface
     */
    private $authService;

    /**
     * AuthenticationListener constructor.
     * @param AuthServiceInterface $authService
     */
    public function __construct(
        AuthServiceInterface $authService
    ) {
        $this->authService = $authService;
    }

    /**
     * kernel.controller action filter
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if (is_array($controller)) {
            $controller = $controller[0];
        }
        if ($controller instanceof RequireAuthenticationInterface) {
            [$userId, $role] = $this->authService->authenticateRequest($event->getRequest());
            $controller->setCurrentUserContext($userId, $role);
        }
    }
}
