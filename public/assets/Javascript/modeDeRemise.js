// Script permettant de d'afficher le prix en fonction du mode de remise.
// Recuperation des elements par Id qu'on initialise dans une variable.
let checkboxHandDelivery = document.getElementById('checkboxHandDelivery');
let checkboxShipment = document.getElementById('checkboxShipment');
let priceHandDelivery = document.getElementById('priceHandDelivery');
let priceShipment = document.getElementById('priceShipment');
checkboxHandDelivery.addEventListener('click', handDeliveryCheck);
checkboxShipment.addEventListener('click', shipementCheck);

// Par défaut, le prix de la livraison est caché.
priceShipment.style.display = 'none';

// Fonction permettant de faire apparaitre ou disparaitre
// le prix et de "checker" ou "dé-checker" l'autre checkbox.
function handDeliveryCheck() {
    // Si la remise en main propre est "dé-checker".
    if (checkboxHandDelivery.checked === false) {
        // La livraison est "checker".
        checkboxShipment.checked = true;
        // Le prix de la remise en main propre est caché.
        priceHandDelivery.style.display = 'none';
        // Le prix de la livraison est affiché.
        priceShipment.style.display = 'block';
    } else {
        // Si la remise en main propre est "checker".
        // La livraison est "dé-checker".
        checkboxShipment.checked = false;
        // Le prix de la remise en main propre est affiché.
        priceHandDelivery.style.display = 'block';
        // Le prix de la livraison est caché.
        priceShipment.style.display = 'none';
    }
}

// Fonction permettant de faire apparaitre ou disparaitre
// le prix et de "checker" ou "dé-checker" l'autre checkbox.
function shipementCheck() {
    // Si la livraison est "dé-checker".
    if (checkboxShipment.checked === false) {
        // La remise en main propre est "checker".
        checkboxHandDelivery.checked = true;
        // Le prix de la remise en main propre est affiché.
        priceHandDelivery.style.display = 'block';
        // Le prix de la livraison est caché.
        priceShipment.style.display = 'none';
    } else {
        // Si la livraison est "checker".
        // La remise en main propre est "dé-checker".
        checkboxHandDelivery.checked = false;
        // Le prix de la remise en main propre est caché.
        priceHandDelivery.style.display = 'none';
        // Le prix de la livraison est affiché.
        priceShipment.style.display = 'block';
    }
}