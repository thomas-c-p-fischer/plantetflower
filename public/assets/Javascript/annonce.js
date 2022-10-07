// Script permettant de d'afficher la demande de confirmation de suppression d'annonce dans la page annonce.html.twig

// Recuperation des elements par Id qu'on initialise dans une variable.
let delete_btn = document.getElementById('delete_btn');


if (delete_btn) {

    confirm_delete = document.getElementById('confirm_delete');
    cancel_btn = document.getElementById('cancel_btn');

    // Event de type clic pour afficher la confirmation
    delete_btn.addEventListener("click", () => {
        confirm_delete.style.display = 'block';
    });

    // Event de type clic pour disparaitre la confirmation
    cancel_btn.addEventListener("click", () => {
        confirm_delete.style.display = 'none';
    });
}


// Script permettant de calculer le prix définitif de façon dynamique dans les pages createAnnonce.html.twig et editAnnonce.html.twig

// Recuperation des elements par Id (création ou édition de l'annonce) qu'on initialise dans une variable.
let cA_origin_price = document.getElementById("createAnnonce_origin_price");
let eA_origin_price = document.getElementById("editAnnonce_origin_price");

let origin_price;
let total_price;
let price_message;
let submit;

// Si création d'une annonce
if (cA_origin_price) {

    origin_price = document.getElementById("createAnnonce_origin_price");
    total_price = document.getElementById("createAnnonce_total_price");
    price_message = document.getElementById("createAnnonce_price_message");
    submit = document.getElementById("createAnnonce_submit");
    origin_price.addEventListener("input", sum);

// Si édition d'une annonce
} else if (eA_origin_price) {

    origin_price = document.getElementById("editAnnonce_origin_price");
    total_price = document.getElementById("editAnnonce_total_price");
    price_message = document.getElementById("editAnnonce_price_message");
    submit = document.getElementById("editAnnonce_submit");
    origin_price.addEventListener("input", sum);

}

// Calcul du prix total
function sum() {

    // Modification des éventuels caractères saisie par l'utilisateur
    ChangeCharacters(origin_price)

    // Déclaration des valeurs ajoutées

    // Frais fixes
    let fixedFee = 0.7;

    // Pourcentage de marge
    let marginPercentage = 0.12;

    // Calcul de la marge
    let margin = parseFloat(origin_price.value) * marginPercentage;

    // Calcul du prix taxé arrondi au 3e chiffre après la virgule
    let taxedPrice = parseFloat(origin_price.value) + fixedFee + margin;


    // Arrondissement à la demi-unité supérieure

    // Récupération du modulo du prix taxé
    let moduloTaxedPrice = taxedPrice % 1;

    // Initialisation de la variable du prix total
    let totalPrice;

    // Si le modulo du prix taxé est entre 0 et 0,5
    if (moduloTaxedPrice > 0 && moduloTaxedPrice < 0.5) {

        // Arrondissement du prix total à l'entier supérieur ajouté à 0,5
        totalPrice = Math.round(taxedPrice) + 0.5;

    // Si le modulo du prix taxé est supérieur ou égal à 0,5 et inférieur à 1
    } else if (moduloTaxedPrice >= 0.5 && moduloTaxedPrice < 1) {

        // Arrondissement du prix total à l'entier supérieur
        totalPrice = Math.round(taxedPrice);
    }

    // Si le prix initial est supérieur à 0 et inférieur à 100
    if (origin_price.value > 0 && origin_price.value < 100) {
        total_price.innerHTML = totalPrice + " €";
        price_message.innerHTML = "";

        // Activation du bouton submit du formulaire
        submit.disabled = false;
        submit.style.backgroundColor = '';
        submit.style.color = '';
        submit.style.border = '';

    } else {
        total_price.innerHTML = "0 €";
    }
    // Si le prix initial est supérieur à 100
    if (origin_price.value >= 100) {
        total_price.innerHTML = "0 €";
        price_message.innerHTML = "Nous ne prenons pas en compte votre annonce au-delà de 100€, veuillez nous excuser";

        // Désactivation du bouton submit du formulaire
        submit.disabled = true;
        submit.style.backgroundColor = 'grey';
        submit.style.color = 'white';
        submit.style.border = '2px solid grey';
    }

    // Fonction permettant de modifier d'éventuels caractères saisie par l'utilisateur
    function ChangeCharacters(origin_price) {

        // Modification de la virgule en point
        if (origin_price.value.indexOf(",") !== -1) {
            origin_price.value = (origin_price.value.split(",")[0] + "." + origin_price.value.split(",")[1]);
        }

        // Suppression d'espace
        if (origin_price.value.indexOf(" ") !== -1) {
            origin_price.value = (origin_price.value.split(" ")[0] + "" + origin_price.value.split(" ")[1]);
        }

        // Suppression de Euro
        if (origin_price.value.indexOf("€") !== -1) {
            origin_price.value = (origin_price.value.split("€")[0] + "" + origin_price.value.split("€")[1]);
        }
    }
}