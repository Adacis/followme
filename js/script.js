(() => {
    'use strict';
    const root = document.querySelector('.followme-app');
    if (!root) return;
    const byId = (id) => document.getElementById(id);
    const dialog = byId('followme-dialog');
    const form = byId('edit_followme');
    const feed = byId('actufollowme');
    const status = byId('followme-status');
    const categories = ['News', 'Vulnérabilité', 'Menace', 'Protection des données', 'Outil', 'Guide', 'Vidéos', 'Numérique Responsable', 'IA'];
    let refreshSequence = 0;
    let refreshPending = false;
    const dateInput = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    const now = new Date();
    byId('intervaldebut').value = dateInput(new Date(now.getFullYear(), now.getMonth(), 1));
    byId('intervalfin').value = dateInput(new Date(now.getFullYear(), now.getMonth() + 1, 0));
    byId('topposter').value = String(now.getFullYear());

    async function request(path, data) {
        const response = await fetch(OC.generateUrl(`/apps/followme/${path}`), {
            method: data === undefined ? 'GET' : 'POST',
            headers: { 'Content-Type': 'application/json', requesttoken: OC.requestToken },
            ...(data === undefined ? {} : { body: JSON.stringify(data) }),
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || `La requête a échoué (${response.status}).`);
        return result;
    }
    function interval() {
        const start = byId('intervaldebut').value;
        const end = byId('intervalfin').value;
        if (!start || !end || start > end) throw new Error('Choisissez une période valide.');
        return { intervaldebut: String(Math.floor(new Date(`${start}T00:00:00`).getTime() / 1000)),
            intervalfin: String(Math.floor(new Date(`${end}T23:59:59`).getTime() / 1000)) };
    }
    function element(tag, text, className) {
        const node = document.createElement(tag);
        if (text !== undefined) node.textContent = String(text);
        if (className) node.className = className;
        return node;
    }
    function articleCard(article) {
        const card = element('article', undefined, 'followme-card');
        card.dataset.id = article.id;
        const header = element('div', undefined, 'followme-card-header');
        header.append(element('span', article.categorie, 'followme-tag'));
        const time = element('time', new Date(Number(article.date) * 1000).toLocaleDateString('fr-FR'));
        time.dateTime = new Date(Number(article.date) * 1000).toISOString();
        header.append(time);
        const actions = element('div', undefined, 'followme-card-actions');
        for (const [action, label] of [['edit', 'Modifier'], ['delete', 'Supprimer']]) {
            const button = element('button', label);
            button.type = 'button'; button.dataset.action = action;
            button.setAttribute('aria-label', `${label} : ${article.title || 'actualité'}`);
            actions.append(button);
        }
        header.append(actions);
        const heading = element('h2');
        const link = element('a', article.title || article.lien);
        // Legacy records may contain non-HTTP links: never render executable URLs.
        try {
            const url = new URL(article.lien);
            if (['http:', 'https:'].includes(url.protocol)) { link.href = url.href; link.target = '_blank'; link.rel = 'noopener noreferrer'; }
        } catch { /* Keep invalid legacy links as plain text. */ }
        heading.append(link);
        const footer = element('div', `Partagé par ${article.utilisateur}`, 'followme-card-footer');
        card.append(header, heading, element('p', article.description, 'followme-card-description'), footer);
        return card;
    }
    async function ranking() {
        const data = await request('getNbArticleByUser', { year: byId('topposter').value });
        const body = byId('getNbArticleByUser').querySelector('tbody');
        body.replaceChildren();
        if (!data.length) {
            const cell = element('td', 'Aucun partage cette année.'); cell.colSpan = 2;
            const row = element('tr'); row.append(cell); body.append(row);
        }
        data.forEach((entry, index) => {
            const row = element('tr');
            const author = element('td'); author.append(element('span', index + 1, 'followme-rank'), document.createTextNode(entry.utilisateur));
            row.append(author, element('td', entry.count)); body.append(row);
        });
    }
    async function refresh() {
        const sequence = ++refreshSequence;
        feed.setAttribute('aria-busy', 'true');
        try {
            const data = await request('showActu', interval());
            if (sequence !== refreshSequence) return;
            feed.replaceChildren(...data.map(articleCard));
            if (!data.length) feed.append(element('p', 'Aucune actualité sur cette période. Partagez le premier lien !', 'followme-empty'));
            byId('article-count').textContent = `${data.length} actualité${data.length > 1 ? 's' : ''}`;
            status.textContent = '';
            await ranking();
        } catch (error) { if (sequence === refreshSequence) status.textContent = error.message; }
        finally { if (sequence === refreshSequence) feed.setAttribute('aria-busy', 'false'); }
    }
    function showModal(article) {
        form.reset();
        form.dataset.mode = article ? 'edition' : 'ajout';
        form.dataset.idArticle = article ? article.id : '';
        byId('followme-dialog-title').textContent = article ? 'Modifier l’actualité' : 'Nouvelle actualité';
        form.querySelector('[type="submit"]').textContent = article ? 'Enregistrer les modifications' : 'Partager l’actualité';
        byId('titlefollowme').value = article?.title || '';
        byId('datefollowme').value = dateInput(article ? new Date(Number(article.date) * 1000) : new Date());
        byId('lienfollowme').value = article?.lien || '';
        byId('descriptionfollowme').value = article?.description || '';
        byId('categoriefollowme').value = article?.categorie || 'News';
        byId('nbCaracteres').textContent = byId('descriptionfollowme').value.length;
        byId('followme-form-error').textContent = '';
        dialog.showModal(); // Native top layer, focus trap, Escape, focus restoration.
    }
    byId('afficher_ajout_modal').addEventListener('click', () => showModal());
    dialog.querySelectorAll('.fermer_modal').forEach((button) => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('click', (event) => {
        const rect = dialog.getBoundingClientRect();
        if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) dialog.close();
    });
    byId('descriptionfollowme').addEventListener('input', () => { byId('nbCaracteres').textContent = byId('descriptionfollowme').value.length; });
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = form.querySelector('[type="submit"]'); button.disabled = true;
        try {
            const data = { date: String(Math.floor(new Date(`${byId('datefollowme').value}T12:00:00`).getTime() / 1000)),
                lien: byId('lienfollowme').value, description: byId('descriptionfollowme').value,
                categorie: byId('categoriefollowme').value, title: byId('titlefollowme').value, idArticle: form.dataset.idArticle };
            const result = await request(form.dataset.mode === 'edition' ? 'updateActu' : 'insertActu', data);
            dialog.close(); await refresh();
            status.textContent = result.warning || 'Actualité enregistrée.';
        } catch (error) { byId('followme-form-error').textContent = error.message; }
        finally { button.disabled = false; }
    });
    feed.addEventListener('click', async (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const id = button.closest('[data-id]').dataset.id;
        button.disabled = true;
        try {
            if (button.dataset.action === 'edit') { showModal((await request(`findActu?id=${encodeURIComponent(id)}`)).actu); }
            else if (window.confirm('Supprimer définitivement cette actualité ?')) { await request('delActu', { id }); await refresh(); }
        } catch (error) { status.textContent = error.message; }
        finally { button.disabled = false; }
    });
    const settingsButton = byId('app-settings-header').querySelector('button');
    settingsButton.addEventListener('click', () => {
        const content = byId('app-settings-content'); content.hidden = !content.hidden;
        settingsButton.setAttribute('aria-expanded', String(!content.hidden));
    });
    byId('GenerateNews').addEventListener('click', refresh);
    byId('topposter').addEventListener('change', () => ranking().catch((error) => { status.textContent = error.message; }));
    byId('GenerateToHugo').addEventListener('click', async () => {
        const sequence = ++refreshSequence; // Invalidate pending feed requests before showing the export.
        try {
            const data = await request('postActuInterval', interval());
            if (sequence !== refreshSequence) return;
            const groups = new Map();
            data.forEach((item) => { if (!groups.has(item.categorie)) groups.set(item.categorie, []); groups.get(item.categorie).push(item); });
            const quote = (value) => JSON.stringify(String(value ?? ''));
            const lines = ['content:', `  title: ${quote('Top news du mois de ' + new Date(`${byId('intervalfin').value}T12:00:00`).toLocaleString('fr-FR', { month: 'long' }))}`];
            for (let index = 1; index <= 3; index++) {
                for (const suffix of ['', '_image', '_description', '_link']) lines.push(`  top_news${index}${suffix}: ""`);
            }
            for (const [category, items] of [...groups].sort(([a], [b]) => a.localeCompare(b))) {
                const slug = category.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '') || 'autres';
                lines.push(`  items_${slug}:`);
                items.forEach((item) => lines.push(`    - date: ${quote(new Date(Number(item.date) * 1000).toLocaleDateString('fr-FR'))}`, `      title: ${quote(item.title)}`, `      link: ${quote(item.lien)}`, `      description: ${quote(item.description)}`));
            }
            const output = element('pre', lines.join('\n')); output.id = 'formatNews'; feed.replaceChildren(output);
            feed.setAttribute('aria-busy', 'false'); status.textContent = 'Export prêt à copier dans Hugo. Les trois actualités à la une restent à compléter.';
        } catch (error) { status.textContent = error.message; }
    });
    request('findCategorie').then((rows) => {
        const options = new Set([...categories, ...rows.map((row) => row.categorie)]);
        byId('followme-categories').replaceChildren(...[...options].map((name) => { const option = element('option'); option.value = name; return option; }));
    }).catch(() => { byId('followme-categories').replaceChildren(...categories.map((name) => { const option = element('option'); option.value = name; return option; })); });
    refresh();
    window.setInterval(async () => {
        if (document.hidden || dialog.open || byId('formatNews') || refreshPending) return;
        refreshPending = true;
        try { await refresh(); } finally { refreshPending = false; }
    }, 60000);
})();
