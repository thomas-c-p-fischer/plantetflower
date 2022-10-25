// Initialiser le widget après le chargement complet de la page
$(document).ready(function () {
    // Charge le widget dans la DIV d'id "Zone_Widget" avec les paramètres indiqués
    $("#Zone_Widget").MR_ParcelShopPicker({
        //
        // Paramétrage de la liaison avec la page.
        //
        // Selecteur de l'élément dans lequel est envoyé l'ID du Point Relais (ex: input hidden)
        Target: "#Target_Widget",
        //
        // Paramétrage du widget pour obtention des point relais.
        //
        // Le code client Mondial Relay, sur 8 caractères (ajouter des espaces à droite)
        // BDTEST est utilisé pour les tests => un message d'avertissement apparaît
        Brand: "BDTEST  ",
        // Pays utilisé pour la recherche: code ISO 2 lettres.
        Country: "FR",
        // Code postal pour lancer une recherche par défaut
        PostCode: "33000",
        // Mode de livraison (Standard [24R], XL [24L], XXL [24X], Drive [DRI])
        ColLivMod: "24R",
        // Nombre de Point Relais à afficher
        NbResults: "7",
        //
        // Paramétrage d'affichage du widget.
        //
        // Activer l'affichage Responsive.
        Responsive: true,
        // Afficher les résultats sur une carte?
        ShowResultsOnMap: true
    });

});