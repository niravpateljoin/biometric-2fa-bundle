document.addEventListener('DOMContentLoaded', function () {
    const registerBtn = document.getElementById('register-device');

    if (registerBtn) {
        registerBtn.addEventListener('click', async () => {
            if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
                alert('WebAuthn not supported in this browser.');
                return;
            }

            try {
                const response = await fetch('/biometric/create-args', { method: 'POST' });
                const data = await response.json();
                if (!data.success) throw new Error(data.error);

                const publicKey = recursiveBase64StrToArrayBuffer(data.createdArgs);
                const cred = await navigator.credentials.create(publicKey);

                const payload = {
                    clientDataJSON: arrayBufferToBase64(cred.response.clientDataJSON),
                    attestationObject: arrayBufferToBase64(cred.response.attestationObject),
                };

                const saveResp = await fetch('/biometric/process-create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });

                const saveResult = await saveResp.json();
                if (saveResult.success) {
                    alert('Device registered!');
                    location.href = '/dashboard'; // change if needed
                } else {
                    alert('Registration failed: ' + saveResult.error);
                }
            } catch (err) {
                alert('Error during registration: ' + err.message);
            }
        });
    }
});
