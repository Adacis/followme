(() => {
    'use strict';
    const form = document.getElementById('followme-admin-form');
    if (!form) return;
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = form.querySelector('[type="submit"]');
        const status = document.getElementById('followme-admin-status');
        button.disabled = true;
        status.textContent = 'Enregistrement…';
        try {
            const response = await fetch(OC.generateUrl('/apps/followme/param'), {
                method: 'POST', headers: { 'Content-Type': 'application/json', requesttoken: OC.requestToken },
                body: JSON.stringify(Object.fromEntries(new FormData(form))),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Enregistrement impossible.');
            status.textContent = data.message;
            form.querySelectorAll('[type="password"]').forEach((field) => { field.value = ''; });
        } catch (error) { status.textContent = error.message; }
        finally { button.disabled = false; }
    });
})();
