// Script permettant d'afficher les informations, achats et annonces
// selon le rôle et la demande de l'utilisateur.
// Recuperation des elements par Id qu'on initialise dans une variable.
let btnInformations = document.getElementById('btn-informations');
let btnPurchases = document.getElementById('btn-purchases');
let btnAnnonces = document.getElementById('btn-annonces');
let informationsDisplay = document.getElementById('informations_display');
let purchasesDisplay = document.getElementById('purchases_display');
let annoncesDisplay = document.getElementById('annonces_display');

informationsDisplay.style.display = 'block';
purchasesDisplay.style.display = 'none';
if (btnAnnonces !== null ) {
    annoncesDisplay.style.display = 'none';
}

// Permet de faire apparaitre les informations et de
// disparaitre les autres éléments au click.
btnInformations.addEventListener("click", () => {
    informationsDisplay.style.display = 'block';
    purchasesDisplay.style.display = 'none';
    if (btnAnnonces !== null ) {
        annoncesDisplay.style.display = 'none';
    }
});

// Permet de faire apparaitre les informations et de
// disparaitre les autres éléments au click.
btnPurchases.addEventListener("click", () => {
    informationsDisplay.style.display = 'none';
    purchasesDisplay.style.display = 'block';
    if (btnAnnonces !== null ) {
        annoncesDisplay.style.display = 'none';
    }
});

// Permet de faire apparaitre les informations et de
// disparaitre les autres éléments au click.
if (btnAnnonces !== null ) {
    btnAnnonces.addEventListener("click", () => {
        informationsDisplay.style.display = 'none';
        purchasesDisplay.style.display = 'none';
        annoncesDisplay.style.display = 'block';
    });
}