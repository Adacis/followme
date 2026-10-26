<div id="app-settings">
    <div id="app-settings-header"><button type="button" class="settings-button" aria-expanded="false" aria-controls="app-settings-content">Paramètres</button></div>
    <div id="app-settings-content" hidden>
        <?php if ($_['isAdmin']): ?>
            <a class="followme-settings-link" href="<?php p($_['adminUrl']); ?>">Administration de Followme ↗</a>
        <?php else: ?>
            <p class="followme-muted">Les intégrations sont gérées par votre administrateur.</p>
        <?php endif; ?>
    </div>
</div>
