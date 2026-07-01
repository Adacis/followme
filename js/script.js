//Framework Nextcloud
var baseUrl = OC.generateUrl('/apps/followme');

//Var
var actuFollowme = document.getElementById("actufollowme");
var getNbArticleByUser = document.querySelector("#getNbArticleByUser tbody");
var myInterval;

//initialisation des dates : 
var tdate = new Date();
debutmois = (new Date(tdate.getFullYear(), tdate.getMonth(), 1, 1, 1, 1)).toISOString().substr(0, 10);
finmois = (new Date(tdate.getFullYear(), tdate.getMonth() + 1, 0, 23, 59, 59)).toISOString().substr(0, 10);
anneeencours = (new Date(tdate.getFullYear(), tdate.getMonth() + 1, 0, 23, 59, 59)).toISOString().substr(0, 4);
document.getElementById('intervaldebut').value = debutmois;
document.getElementById('intervalfin').value = finmois;
document.getElementById('topposter').value = anneeencours;

//Function pour mettre en timestamp
function getTimestamp(newDate) {
	return (new Date(newDate).getTime());
}

//Function pour mettre en format date correct depuis un timestamp
function format_date(value) {
	date = new Date(value * 1000);
	month = date.getMonth();
	month = month + 1;
	if (month < 10) month = "0" + month;
	year = date.getFullYear();
	day = date.getDate();
	return day + "/" + month + "/" + year;
}

// recharger l'affichage de l'actualité qui vient d'être modifiée
function updateActualiteDOM(actufolowme, resp) {

	//Trunck des liens
	var reslien;
	if (resp.lien.length > 29) {
		reslien = resp.lien.substring(0, 30) + '...';
	} else {
		reslien = resp.lien;
	}

	var actualite = document.createElement("div");
	actualite.setAttribute('data-actualite-id', resp.id);
	actualite.setAttribute('data-actualite-date', resp.date);
	actualite.setAttribute('class', 'actualite');

	var actualiteContent = document.createElement("div");
	actualiteContent.setAttribute('class', 'actualite-content');

	var actualiteSection = document.createElement("div");
	actualiteSection.setAttribute('class', 'actualite-section');

	var actualiteDate = document.createElement("p");
	actualiteDate.append(document.createTextNode(format_date(resp.date)));
	actualiteDate.setAttribute('class', 'date');

	var actualiteLien = document.createElement("a");
	actualiteLien.setAttribute('href', resp.lien);
	actualiteLien.setAttribute('class', 'lien');
	actualiteLien.append(document.createTextNode(reslien));
	var spanModif = document.createElement("span");
	spanModif.setAttribute('class', 'modiffollowme jam jam-pencil');
	var spanSupp = document.createElement("span");
	spanSupp.setAttribute('class', 'supprfollowme jam jam-trash');

	actualiteSection.append(actualiteDate);
	actualiteSection.append(actualiteLien);

	actualiteSection.append(spanModif);
	actualiteSection.append(spanSupp);

	var actualiteTitle = document.createElement("div");
	actualiteTitle.setAttribute('class', 'actualiteTitle');
	var pTitle = document.createElement("p");
	pTitle.append(document.createTextNode(resp.title ?? ''));

	var actualiteDescription = document.createElement("div");
	actualiteDescription.setAttribute('class', 'actualiteDescription');
	var pDescription = document.createElement("p");
	pDescription.append(document.createTextNode(resp.description));

	var actualiteAuteur = document.createElement("p");
	actualiteAuteur.append(document.createTextNode(resp.utilisateur));
	actualiteAuteur.setAttribute('class', 'auteur');

	actualiteDescription.append(pTitle);
	actualiteDescription.append(pDescription);
	actualiteDescription.append(actualiteAuteur);

	var actualiteCategorie = document.createElement("div");
	actualiteCategorie.setAttribute('class', 'actualiteCategorie');
	var pCategorie = document.createElement("span");
	pCategorie.setAttribute('class', 'tag');
	pCategorie.append(document.createTextNode(resp.categorie));

	actualiteCategorie.append(pCategorie)

	actualiteContent.append(actualiteSection);
	actualiteContent.append(actualiteDescription);
	actualiteContent.append(actualiteCategorie);

	actualite.append(actualiteContent);

	actufolowme.append(actualite);
}


//Rafraichissement de l'affichage
//TODO AJOUTER LES CATEGORIES EN BDD
var refresh = function (baseUrl, actuFollowme) {

	//Affichage mensuel des actus
	var interval = {
		intervaldebut: String(getTimestamp(document.getElementById('intervaldebut').value) / 1000),
		intervalfin: String(getTimestamp(document.getElementById('intervalfin').value) / 1000),
	};

	//Refresh des articles
	fetch(baseUrl + '/showActu', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'requesttoken': OC.requestToken,
		},
		body: JSON.stringify(interval)
	})
		.then(response => response.json())
		.then(response => {

			actuFollowme.innerHTML = "";

			response.forEach(myresp => {
				updateActualiteDOM(actuFollowme, myresp);
			});

		})
		.catch(console.error);

	recupererTopPoster();

	//Refresh des catégories
	//Ajout catégorie à modifier à l'avenir
	const categorie = document.getElementById("categoriefollowme");

	categorie.innerHTML = `
			<option value="News">📰 News</option>
			<option value="Vulnérabilité">🛑 Vulnérabilité</option>
			<option value="Menace">⚠️ Menace</option>
			<option value="Protection des données">🔒 Protection des données</option>
			<option value="Outil">🔨 Outil</option>
			<option value="Guide">📍 Guide</option>
			<option value="Vidéos">🎞 Vidéos</option>
			<option value="Numérique Responsable">⌨️ Numérique Responsable</option>
			<option value="IA">🧠 IA</option>
		`;

	//RefreshListener
	refreshListener();
}

async function recupererTopPoster() {
	const myTopPoster = {
		year: document.getElementById('topposter').value
	};

	try {
		const response = await fetch(baseUrl + '/getNbArticleByUser', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'requesttoken': OC.requestToken,
			},
			body: JSON.stringify(myTopPoster)
		});

		if (!response.ok) {
			throw new Error(`Erreur HTTP : ${response.status}`);
		}

		const data = await response.json();

		getNbArticleByUser.replaceChildren();

		data.forEach(({ utilisateur, annee, count }) => {
			const tr = document.createElement('tr');

			[utilisateur, annee, count].forEach(value => {
				const td = document.createElement('td');
				td.textContent = value;
				tr.appendChild(td);
			});

			getNbArticleByUser.appendChild(tr);
		});

	} catch (error) {
		console.error('Erreur lors de la récupération des top posters :', error);
	}
}


//Envoie du formulaire de la modal d'édition ou ajout de l'actualité
document.getElementById("edit_followme").addEventListener("submit", function (e) {
	e.preventDefault();

	const actualite = {
		date: String(getTimestamp(document.getElementById('datefollowme').value) / 1000),
		lien: document.getElementById('lienfollowme').value,
		description: document.getElementById('descriptionfollowme').value,
		categorie: document.getElementById('categoriefollowme').value,
		title: document.getElementById('titlefollowme').value,
		idArticle: this.dataset.idArticle
	};

	if (this.dataset.mode === "edition") {
		editerActu(actualite);
	} else {
		ajouterActu(actualite);
	}

});

//Function ajax permettant l'enregistrement dans la base de données de l'actualité
async function ajouterActu(actualite) {
	try {
		const response = await fetch(baseUrl + '/insertActu', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'requesttoken': OC.requestToken,
			},
			body: JSON.stringify(actualite)
		});

		if (!response.ok) {
			throw new Error(`Erreur HTTP : ${response.status}`);
		}

		const data = await response.json();

		console.log(data);

		refresh(baseUrl, actuFollowme);
		fermer_modal();

	} catch (err) {
		console.error("Erreur lors de l'ajout de l'actualité :", err);
	}
}

//Function ajax permettant la modification dans la base de données de l'actualité
async function editerActu(actualite) {
	try {
		const response = await fetch(baseUrl + '/updateActu', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'requesttoken': OC.requestToken,
			},
			body: JSON.stringify(actualite)
		});

		if (!response.ok) {
			throw new Error(`Erreur HTTP : ${response.status}`);
		}

		await response.json();

		refresh(baseUrl, actuFollowme);
		fermer_modal();

	} catch (err) {
		console.error("Erreur lors de la modification de l'actualité :", err);
	}
}


//Supprimer une news dans la base de données
var refreshListener = function () {
	createDeleteListener(document.getElementById("actufollowme"));
	createEditListener(document.getElementById("actufollowme"));
};

function createDeleteListener(element) {
	if (element.getAttribute('DeleteListener') !== "1") {
		element.setAttribute('DeleteListener', "1");
		element.addEventListener("click", function (e) {

			const btn = e.target.closest(".supprfollowme");

			if (!btn) return;

			const actualite = btn.closest(".actualite");

			const resp = confirm(
				`Etes-vous sûr de vouloir supprimer l'actualité du ${actualite.querySelector(".date").textContent
				}, ${actualite.querySelector(".lien").textContent
				} ?`
			);

			if (!resp) return;

			e.preventDefault();

			const news = {
				id: actualite.dataset.actualiteId
			};

			fetch(baseUrl + "/delActu", {
				method: "POST",
				headers: {
					"Content-Type": "application/json",
					'requesttoken': OC.requestToken,
				},
				body: JSON.stringify(news)
			})
				.then(() => refresh(baseUrl, actuFollowme))
				.catch(console.error);

		});
	}
}

function createEditListener(element) {
	if (element.dataset.editListener === "1") {
		return;
	}

	element.dataset.editListener = "1";

	element.addEventListener("click", async function (e) {
		const btn = e.target.closest(".modiffollowme");

		if (!btn) {
			return;
		}

		e.stopPropagation();

		const actualite = btn.closest(".actualite");

		const news = {
			id: actualite.dataset.actualiteId
		};

		try {
			const response = await fetch(
				`${baseUrl}/findActu?id=${encodeURIComponent(news.id)}`,
				{
					method: "GET",
					headers: {
						"Content-Type": "application/json",
						'requesttoken': OC.requestToken,
					}
				}
			);

			if (!response.ok) {
				throw new Error(`Erreur HTTP : ${response.status}`);
			}

			const actualite = await response.json();

			console.log(actualite);

			setModalMode("edition");
			setModalInputValues(actualite);
			updateCaracteresCompteur();
			afficher_modal();

		} catch (err) {
			console.error(err);
		}
	});
}

function clearAllActus() {
	document.getElementById('actufollowme').innerHTML = '';
}

//---------------------MODAL 

const modal = document.getElementById("edit_followme");

// Afficher la modal
function afficher_modal() {
	modal.style.display = "block"; // ou "flex" selon ton CSS
}

// Fermer la modal
function fermer_modal() {
	modal.style.display = "none";
	clearModal();
}

// Ouvrir la modal en mode ajout
document.getElementById("afficher_ajout_modal")
	.addEventListener("click", () => {
		setModalMode("ajout");
		afficher_modal();
	});

// Boutons de fermeture
document.querySelectorAll(".fermer_modal").forEach(button => {
	button.addEventListener("click", fermer_modal);
});

// Passer la modal en mode ajout / édition
function setModalMode(mode) {
	modal.dataset.mode = mode;
}

// Remplir les champs de la modal
function setModalInputValues(actualite) {

	modal.dataset.idArticle = actualite.actu.id;

	const time = moment(actualite.actu.date * 1000).format("YYYY-MM-DD");

	modal.querySelector("#titlefollowme").value = actualite.actu.title ?? "";
	modal.querySelector("#datefollowme").value = time;
	modal.querySelector("#lienfollowme").value = actualite.actu.lien ?? "";
	modal.querySelector("#descriptionfollowme").value = actualite.actu.description ?? "";
	modal.querySelector("#categoriefollowme").value = actualite.actu.categorie ?? "";
}

// Vider la modal
function clearModal() {

	modal.querySelectorAll("input, textarea").forEach(input => {
		input.value = "";
	});

	modal.querySelector("#categoriefollowme").selectedIndex = 0;
	modal.querySelector("#nbCaracteres").textContent = "0";
}

// Mise à jour du compteur
document
	.getElementById("descriptionfollowme")
	.addEventListener("input", updateCaracteresCompteur);

// Mettre à jour le compteur de caractères
function updateCaracteresCompteur() {

	const nombreCaractere =
		modal.querySelector("#descriptionfollowme").value.length;

	modal.querySelector("#nbCaracteres").textContent = nombreCaractere;
}

document.getElementById("topposter").addEventListener("change", recupererTopPoster);

// ---------------------------------------------

// Paramètres
document.querySelector('#app-settings-header button').addEventListener('click', () => {
	const settings = document.querySelector('#app-settings-content')

	settings.style.display = settings.style.display === 'none' ? 'block' : 'none'
})

// Masquer les éléments au chargement
document.getElementById("edit_followme").style.display = "none";

document.querySelectorAll(".modal-background").forEach(element => {
	element.style.display = "none";
});

// document.getElementById("insertfollowme").style.display = "none";

// Gestion du bouton de rafraîchissement
document.getElementById("GenerateNews").addEventListener("click", () => {

	if (myInterval) {
		stopRafraichissement();
	}

	goInterval();
	refresh(baseUrl, actuFollowme);
});

// Lancer le rafraîchissement automatique
function goInterval() {
	myInterval = setInterval(() => {
		refresh(baseUrl, actuFollowme);
	}, 60000);
}

// Arrêter le rafraîchissement automatique
function stopRafraichissement() {
	clearInterval(myInterval);
	myInterval = null;
}


refresh(baseUrl, actuFollowme);
goInterval();
