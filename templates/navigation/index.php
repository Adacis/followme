<div id="navigation" class="followme-sidebar">
    <header class="followme-brand"><span class="followme-brand-icon" aria-hidden="true">↗</span><div><h2>Followme</h2><span class="followme-version">Version <?php p($_['version']); ?></span></div></header>
    <button id="afficher_ajout_modal" class="primary followme-add" type="button">＋ Ajouter une actualité</button>
    <section class="followme-ranking" aria-labelledby="ranking-title">
        <div class="followme-ranking-header"><h3 id="ranking-title">Contributeurs</h3><label for="topposter" class="hidden-visually">Année du classement</label><input type="number" id="topposter" min="1970" max="2100" aria-label="Année du classement"></div>
        <p class="followme-muted">Les liens partagés cette année</p>
        <table id="getNbArticleByUser"><caption class="hidden-visually">Classement des contributeurs par nombre d’actualités</caption><thead><tr><th scope="col">Contributeur</th><th scope="col">Partages</th></tr></thead><tbody></tbody></table>
    </section>
    <section class="followme-period"><h3>Période d’affichage</h3><label for="intervaldebut">Du</label><input id="intervaldebut" type="date" required><label for="intervalfin">Au</label><input id="intervalfin" type="date" required><button id="GenerateNews" type="button">Actualiser</button><button id="GenerateToHugo" type="button">Exporter pour Hugo</button></section>
</div>
