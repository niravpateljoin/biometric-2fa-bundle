<?php

namespace Biometric2FA\EventSubscriber;

use Biometric2FA\Security\BiometricUserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;

readonly class BiometricAuthSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private RedirectController $redirectController,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => ['onLoginSuccess', 4],
            KernelEvents::CONTROLLER => ['onKernelController', 4],
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        $request = $event->getRequest();

        if (!$user instanceof BiometricUserInterface) {
            return;
        }

        if ($user->isBiometric2FAEnabled()) {
            $request->getSession()->set('biometric_verification', false);
        }
    }

    public function onControllerEvent(ControllerEvent $event): void
    {
        $currentUser = $this->tokenStorage->getToken()?->getUser();
        $request = $event->getRequest();

        if (!$currentUser instanceof BiometricUserInterface) {
            return;
        }

        /** @var BiometricUserInterface $user */
        $session = $request->getSession();
        $biometricVerification = $session->get('biometric_verification');

        $allowedRoutes = [
            'app_biometrics_auth',
            'app_biometrics_check_biometric_registration',
            'bio_metrics_get_args',
            'bio_metrics_create_args',
            'bio_metrics_process_create',
            'app_logout',
        ];

        if ($currentUser->isBiometric2FAEnabled()
            && !$biometricVerification
            && !in_array($request->attributes->get('_route'), $allowedRoutes, true)) {
            $response = $this->redirectController->redirect($request, 'app_biometrics_auth');
            $event->setController(fn() => $response);
        }
    }
}
