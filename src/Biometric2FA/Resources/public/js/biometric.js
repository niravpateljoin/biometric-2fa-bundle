document.addEventListener('DOMContentLoaded', function () {
    const loginBtn = document.getElementById('fingerprint-auth');

    if (!loginBtn || !navigator.credentials || !navigator.credentials.get) {
        console.warn('Biometric login not supported.');
        return;
    }

    loginBtn.addEventListener('click', async () => {
        try {
            const res = await fetch('/biometric/get-args', { method: 'POST' });
            const data = await res.json();
            if (!data.success) throw new Error(data.error);

            const getOptions = recursiveBase64StrToArrayBuffer(data.getArgs);
            const assertion = await navigator.credentials.get(getOptions);

            const payload = {
                id: arrayBufferToBase64(assertion.rawId),
                clientDataJSON: arrayBufferToBase64(assertion.response.clientDataJSON),
                authenticatorData: arrayBufferToBase64(assertion.response.authenticatorData),
                signature: arrayBufferToBase64(assertion.response.signature),
            };

            const verifyRes = await fetch('/biometric/verify', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const verifyData = await verifyRes.json();
            if (verifyData.success) {
                window.location.href = '/dashboard'; // customize as needed
            } else {
                alert('Verification failed: ' + verifyData.error);
            }
        } catch (err) {
            alert('Biometric error: ' + err.message);
        }
    });

    function arrayBufferToBase64(buffer) {
        return btoa(String.fromCharCode(...new Uint8Array(buffer)));
    }

    function recursiveBase64StrToArrayBuffer(obj) {
        if (Array.isArray(obj)) return obj.map(recursiveBase64StrToArrayBuffer);
        if (typeof obj === 'object' && obj !== null) {
            const result = {};
            for (const key in obj) result[key] = recursiveBase64StrToArrayBuffer(obj[key]);
            return result;
        }
        if (typeof obj === 'string' && /^[A-Za-z0-9+/=]+$/.test(obj)) {
            return Uint8Array.from(atob(obj), c => c.charCodeAt(0)).buffer;
        }
        return obj;
    }

});
