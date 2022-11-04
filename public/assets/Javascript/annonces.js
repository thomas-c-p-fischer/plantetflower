// Script permettant d'effectuer une autocomplétion.
// La fonction d'autocomplétion prend deux arguments :
// L’élément de champ de texte (inp) et un tableau pouvant contenir les valeurs d'autocomplétion (arr).
function autocomplete(inp, arr) {
    let currentFocus;

    // Fonction s'exécutant lorsque l'utilisateur écrit dans le champ de texte.
    inp.addEventListener("input", function (e) {
        let a, b, i, val = this.value;

        // Ferme la liste des valeurs déjà ouverte.
        closeAllLists();
        if (!val) {
            return false;
        }
        currentFocus = -1;

        // Crée une DIV qui contiendra les valeurs.
        a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-list");
        a.setAttribute("class", "autocomplete-items");

        // Ajoute la DIV en tant que fils du conteneur d'autocomplétion.
        this.parentNode.appendChild(a);

        // Boucle sur les éléments du tableau.
        for (i = 0; i < arr.length; i++) {

            // Vérifie si l’élément commence par les mêmes lettres que la valeur du champ de texte.
            if (arr[i].substr(0, val.length).toUpperCase() === val.toUpperCase()) {

                // Créer une DIV pour chaque élément correspondant.
                b = document.createElement("DIV");

                // Met les lettres correspondantes en gras.
                b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                b.innerHTML += arr[i].substr(val.length);

                // Insère un champ de saisie qui contiendra la valeur de l’élément du tableau actuel.
                b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";

                // Exécute une fonction lorsque quelqu’un clique sur la valeur de la DIV.
                b.addEventListener("click", function (e) {

                    // Insère la valeur du champ de texte d'autocomplétion.
                    inp.value = this.getElementsByTagName("input")[0].value;

                    // Ferme la liste des valeurs d'autocomplétion
                    // (ou toute autre liste ouverte de valeurs d'autocomplétion).
                    closeAllLists();
                });
                a.appendChild(b);
            }
        }
    });

    // Fonction s'exécutant lorsque l'utilisateur appuie sur une touche du clavier.
    inp.addEventListener("keydown", function (e) {
        let x = document.getElementById(this.id + "autocomplete-list");
        if (x) x = x.getElementsByTagName("div");

        // Si l'utilisateur appuie sur la touche fléchée BAS, la variable currentFocus augmente.
        if (e.keyCode === 40) {
            currentFocus++;

            // Rend l’élément courant plus visible.
            addActive(x);

            // Si l'utilisateur appuie sur la touche fléchée HAUT, la variable currentFocus diminue.
        } else if (e.keyCode === 38) {
            currentFocus--;

            // Rend l’élément courant plus visible.
            addActive(x);
        } else if (e.keyCode === 13) {

            // Si l'utilisateur appuie sur la touche ENTRÉE, cela empêche la soumission du formulaire.
            e.preventDefault();
            if (currentFocus > -1) {

                // Simule un clic sur l’élément "actif".
                if (x) x[currentFocus].click();
            }
        }
    });

    // Fonction pour classer un élément comme "actif".
    function addActive(x) {
        if (!x) return false;

        // Supprime la classe "active" sur tous les éléments.
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (x.length - 1);

        // Ajoute la classe "autocomplete-active".
        x[currentFocus].classList.add("autocomplete-active");
    }

    // Fonction pour supprimer la classe "active" de tous les éléments d'autocomplétion.
    function removeActive(x) {
        for (let i = 0; i < x.length; i++) {
            x[i].classList.remove("autocomplete-active");
        }
    }

    function closeAllLists(elmnt) {

        // Ferme toutes les listes d'autocomplétion du document, sauf celle passée comme argument.
        let x = document.getElementsByClassName("autocomplete-items");
        for (let i = 0; i < x.length; i++) {
            if (elmnt !== x[i] && elmnt !== inp) {
                x[i].parentNode.removeChild(x[i]);
            }
        }
    }

    // s'exécutant lorsque l'utilisateur clique sur le document.
    document.addEventListener("click", function (e) {
        closeAllLists(e.target);
    });
}

// Récupération des noms de toutes les plantes non vendus se trouvant en BDD.
let annoncesTitles = document.getElementsByClassName("plantName");
let allPlantNames = [];
for (i = 0; i < annoncesTitles.length; i++) {
    allPlantNames[i] = annoncesTitles[i].id;
}

// Nouveau tableau ne contenant aucuns doublons des noms de plantes de la BDD.
let uniquePlantNames = allPlantNames.filter((x, i) => allPlantNames.indexOf(x) === i);

// Renvoie le nom des plantes de BDD sans doublons à l'input comprenant l'autocomplétion.
autocomplete(document.getElementById("inputSearch"), uniquePlantNames);