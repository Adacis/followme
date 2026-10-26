<dialog id="followme-dialog" aria-labelledby="followme-dialog-title">
    <form id="edit_followme" data-mode="ajout">
        <header class="followme-dialog-header"><div><p class="followme-eyebrow">PARTAGER AVEC L’ÉQUIPE</p><h2 id="followme-dialog-title">Nouvelle actualité</h2></div><button type="button" class="fermer_modal followme-close" aria-label="Fermer la fenêtre">×</button></header>
        <div class="followme-dialog-body">
            <label for="titlefollowme">Titre</label><input required maxlength="255" type="text" id="titlefollowme" placeholder="Ce qui mérite d’être partagé" autofocus>
            <div class="followme-form-row"><div><label for="datefollowme">Date</label><input required type="date" id="datefollowme"></div><div><label for="categoriefollowme">Catégorie</label><input required maxlength="64" type="text" id="categoriefollowme" list="followme-categories" placeholder="News, Outil, Guide…"><datalist id="followme-categories"></datalist></div></div>
            <label for="lienfollowme">Lien de l’article</label><input required type="url" id="lienfollowme" placeholder="https://…">
            <label for="descriptionfollowme">Pourquoi le partager ?</label><textarea required id="descriptionfollowme" rows="5" maxlength="500" placeholder="Résumez l’essentiel en quelques mots…"></textarea>
            <p class="followme-counter"><span id="nbCaracteres">0</span> / 500 caractères</p><p id="followme-form-error" role="alert"></p>
        </div>
        <footer class="followme-dialog-footer"><button type="button" class="fermer_modal">Annuler</button><button type="submit" class="primary">Partager l’actualité</button></footer>
    </form>
</dialog>
