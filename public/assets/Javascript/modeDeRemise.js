// Script permettant de d'afficher le prix en fonction du mode de remise.
// Recuperation des elements par Id qu'on initialise dans une variable.
let checkboxHandDelivery = document.getElementById('checkboxHandDelivery');
let checkboxShipment = document.getElementById('checkboxShipment');
let priceHandDelivery = document.getElementById('priceHandDelivery');
let priceShipment = document.getElementById('priceShipment');

// Si le choix de la remise en main propre est sélectionné
// alors le prix de celui-ci est affiché et l'autre caché.
if (checkboxHandDelivery.checked === true) {
    checkboxShipment.checked = false;
    priceHandDelivery.style.display = 'block';
    priceShipment.style.display = 'none';
}else
// Si le choix de la livraison est sélectionné
// alors le prix de celui-ci est affiché et l'autre caché.
{
    checkboxHandDelivery.checked = false;
    priceHandDelivery.style.display = 'none';
    priceShipment.style.display = 'block';
}