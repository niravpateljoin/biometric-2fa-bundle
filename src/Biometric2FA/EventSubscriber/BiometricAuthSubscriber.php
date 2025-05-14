<?php

namespace Biometric2FA\EventSubscriber;

use Biometric2FA\Security\BiometricUserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

readonly class BiometricAuthSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private RequestStack $requestStack,
        private RouterInterface $router,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            InteractiveLoginEvent::class => 'onLoginSuccess',
            ControllerEvent::class => 'onControllerEvent',
        ];
    }

    public function onLoginSuccess(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof BiometricUserInterface && $user->isBiometric2FAEnabled()) {
            $event->getRequest()->getSession()->set('biometric_check_pending', true);
        }
    }

    public function onControllerEvent(ControllerEvent $event): void
    {
        $token = $this->tokenStorage->getToken();
        $request = $this->requestStack->getCurrentRequest();

        if (!$token || !$token->getUser() instanceof BiometricUserInterface || !$request) {
            return;
        }

        /** @var BiometricUserInterface $user */
        $user = $token->getUser();
        $session = $request->getSession();

        $allowedRoutes = [
            'app_biometrics_auth',
            'app_biometrics_check_biometric_registration',
            'bio_metrics_get_args',
            'bio_metrics_create_args',
            'bio_metrics_process_create',
            'app_logout',
        ];

        if ($user->isBiometric2FAEnabled()
            && $session->get('biometric_check_pending', false)
            && !in_array($request->attributes->get('_route'), $allowedRoutes, true)) {
            $event->setController(fn() => new RedirectResponse($this->router->generate('app_biometrics_auth')));
        }
    }
}
