//Framework Nextcloud
var baseUrl = OC.generateUrl('/apps/followme');

//Génération de la newsletter
document.getElementById("Generate").addEventListener("click", async () => {

	stopRafraichissement();

	const interval = {
		intervaldebut: String(
			getTimestamp(document.getElementById("intervaldebut").value) / 1000
		),
		intervalfin: String(
			getTimestamp(document.getElementById("intervalfin").value) / 1000
		)
	};

	try {
		const response = await fetch(baseUrl + "/postActuInterval", {
			method: "POST",
			headers: {
				"Content-Type": "application/json"
			},
			body: JSON.stringify(interval)
		});

		if (!response.ok) {
			throw new Error(`Erreur HTTP : ${response.status}`);
		}

		const data = await response.json();

		// Réinitialiser le conteneur
		actuFollowme.innerHTML = "";

		const pre = document.createElement("pre");
		pre.id = "formatNews";
		actuFollowme.appendChild(pre);

		let mycategorie = "";
		let res = "";

		data.forEach(myresp => {

			// Changement de catégorie
			if (mycategorie !== myresp.categorie) {
				res += `<hr class="wp-block-separator is-style-wide"/>`;
				res += `<h2><strong>${myresp.categorie}</strong></h2><br>`;
				mycategorie = myresp.categorie;
			}

			// Tronquer le lien
			const reslien =
				myresp.lien.length > 29
					? myresp.lien.substring(0, 30) + "..."
					: myresp.lien;

			res += `
				<p>
				<strong>${format_date(myresp.date)}</strong><br>
				<strong><a title="${myresp.lien}" href="${myresp.lien}">
				<u>${reslien}</u>
				</a></strong><br>
				${myresp.description}<br><br>
			`;
		});

		pre.textContent = res;

		envoieWP(res);

	} catch (err) {
		console.error(err);
	}
});

async function envoieWP(contentData) {
    // En-tête
    const entete =
        "Adacis vous propose un condensé de l'actualité dans sa newsletter, dont nous avons un peu changé le format. " +
        "On espère que ça vous plaira ! Toute la Team Adacis vous souhaite une bonne semaine. <br/>\n" +
        "Au menu: <br/>\n [toc]";

    const payload = {
        titre: "NEWSLETTER CYBERSÉCURITÉ <MOIS> <ANNEE>",
        content: entete + contentData
    };

    try {
        const response = await fetch(baseUrl + "/envoieWP", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            throw new Error(`Erreur HTTP : ${response.status}`);
        }

        const data = await response.json();

        console.log(data);

    } catch (error) {
        console.error(error);
    }
}
