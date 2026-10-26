<?php style('followme', 'style'); script('followme', 'admin'); ?>
<section id="followme-admin" class="section followme-admin">
    <h2>Followme — Intégrations</h2><p class="followme-muted">Ces paramètres sont réservés aux administrateurs. Les intégrations sont facultatives.</p>
    <form id="followme-admin-form">
        <?php foreach (['WP' => 'WordPress', 'RC' => 'Rocket.Chat'] as $suffix => $label): ?>
        <fieldset><legend><?php p($label); ?></legend>
            <label for="url<?php p($suffix); ?>">Adresse HTTPS</label><input id="url<?php p($suffix); ?>" name="url<?php p($suffix); ?>" type="url" placeholder="https://…" value="<?php p($_['url' . $suffix] ?? ''); ?>">
            <label for="userId<?php p($suffix); ?>">Identifiant</label><input id="userId<?php p($suffix); ?>" name="userId<?php p($suffix); ?>" type="text" autocomplete="off" value="<?php p($_['userId' . $suffix] ?? ''); ?>">
            <label for="password<?php p($suffix); ?>">Mot de passe / mot de passe d’application</label><input id="password<?php p($suffix); ?>" name="password<?php p($suffix); ?>" type="password" autocomplete="new-password" placeholder="Laisser vide pour conserver le secret actuel">
        </fieldset>
        <?php endforeach; ?>
        <label for="roomRC">Salon Rocket.Chat</label><input id="roomRC" name="roomRC" type="text" value="<?php p($_['roomRC'] ?? ''); ?>">
        <p class="followme-muted">Videz l’adresse d’une intégration pour la désactiver. Les secrets sont chiffrés et ne sont jamais renvoyés dans ce formulaire.</p>
        <button type="submit" class="primary">Enregistrer</button><p id="followme-admin-status" role="status" aria-live="polite"></p>
    </form>
</section>
