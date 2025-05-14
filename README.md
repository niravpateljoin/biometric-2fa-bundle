# 🔐 Biometric2FABundle

A Symfony bundle to enable secure biometric-based 2FA using WebAuthn (FIDO2).  
Supports fingerprint, Face ID, and other platform authenticators across modern browsers.

---

## 🚀 Features

- WebAuthn-based 2FA (FIDO2)
- Register multiple biometric devices per user
- Seamless post-login verification
- Easily toggle biometric 2FA per user
- Works with Symfony 6.3+ and 7.x

---

## 📦 Installation

```bash
composer require vivan/biometric-2fa-bundle
```

Then enable the bundle (if using Symfony without Flex):

```php
// config/bundles.php
return [
    Biometric2FABundle\Biometric2FABundle::class => ['all' => true],
];
```

---

## ⚙️ Configuration

Add this config file to your app:

```yaml
# config/packages/biometric_2fa.yaml
biometric_2fa:
  rp_id: "yourdomain.com"
  rp_name: "Your App Name"
  attestation_formats: ["packed", "fido-u2f"]
```

---

## 🗺️ Routes

Import all routes provided by the bundle:

```yaml
# config/routes.yaml
biometric_2fa:
  resource: '@Biometric2FABundle/Resources/config/routes.yaml'
```

---

## 🧩 Setup in User Entity

Your `User` class must implement the provided interface and use the trait:

```php
use Biometric2FABundle\Security\BiometricUserInterface;
use Biometric2FABundle\Security\BiometricUserTrait;

class User implements BiometricUserInterface
{
    use BiometricUserTrait;
}
```

Then run a migration to add the `biometric2FAEnabled` field.

---

## 💻 WebAuthn UI

### Register Device

Use the `/biometric/register` route to let users register a new fingerprint device.

### Authenticate

Once registered, users are redirected to `/biometric/auth` for biometric login after entering credentials.

---

## 📁 Files Included

- `Entity/UserDevice` – stores WebAuthn credentials
- `Helper/UserDeviceHelper` – handles registration and verification logic
- `BiometricController` – provides REST endpoints
- `BiometricAuthSubscriber` – enforces post-login biometric check
- Views and JS for:
    - `biometrics_auth.html.twig`
    - `register_device.html.twig`
    - `settings.html.twig`

---

## 🔐 Security Flow

1. User logs in (normal password)
2. If biometric 2FA is enabled:
    - Redirects to `/biometric/auth`
    - Verifies using browser credentials
    - Access granted after success

---

## 📚 Resources

- WebAuthn PHP Library: [lbuchs/webauthn](https://github.com/lbuchs/WebAuthn)
- WebAuthn Guide: [https://webauthn.guide](https://webauthn.guide)

---

## 📃 License

MIT © Vivan – Free to use and modify.
