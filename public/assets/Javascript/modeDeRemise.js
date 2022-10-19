// Script permettant de d'afficher le prix en fonction du mode de remise.
// Recuperation des elements par Id qu'on initialise dans une variable.
let checkboxHandDelivery = document.getElementById('checkboxHandDelivery');
let checkboxShipment = document.getElementById('checkboxShipment');
let priceHandDelivery;
let priceShipment;

// Si le vendeur propose la remise en main propre.
if (checkboxHandDelivery) {
    // Appel à la fonction correspondante pour afficher le prix de la remise en main propre.
    priceHandDelivery = document.getElementById('priceHandDelivery');
    // Si le vendeur propose également la livraison.
    if (checkboxShipment) {
        checkboxHandDelivery.checked = true;
        // Le prix de la livraison est caché.
        document.getElementById('priceShipment').style.display = 'none';
    }
    checkboxHandDelivery.addEventListener('click', handDeliveryCheck);
} else {
    // Si le vendeur ne propose pas la remise en main propre.
    // La checkbox de la livraison est "checker".
    checkboxShipment.checked = true;
}
// Si le vendeur propose la livraison.
if (checkboxShipment) {
    // Appel à la fonction correspondante pour afficher le prix de la livraison.
    priceShipment = document.getElementById('priceShipment');
    checkboxShipment.addEventListener('click', shipementCheck);
} else {
    // Si le vendeur ne propose pas la livraison.
    // La checkbox de la remise en main propre est "checker".
    checkboxHandDelivery.checked = true;
}

// Fonction permettant de faire apparaitre ou disparaitre.
// Le prix et de "checker" ou "dé-checker" l'autre checkbox.
function handDeliveryCheck() {
    // Si le vendeur ne propose pas la livraison.
    if (!checkboxShipment) {
        // La checkbox de la remise en main propre est "checker".
        checkboxHandDelivery.checked = true;
    } else {
        // Si le vendeur propose également la livraison.
        // Si la remise en main propre est "dé-checker".
        if (checkboxHandDelivery.checked === false) {
            // Le prix de la remise en main propre est caché.
            priceHandDelivery.style.display = 'none';
            // Si la livraison est possible.
            if (checkboxShipment) {
                // La livraison est "checker".
                checkboxShipment.checked = true;
                // Le prix de la livraison est affiché.
                priceShipment.style.display = 'block';
            }
        } else {
            // Si la remise en main propre est "checker".
            // Le prix de la remise en main propre est affiché.
            priceHandDelivery.style.display = 'block';
            // Si la livraison est possible.
            if (checkboxShipment) {
                // La livraison est "dé-checker".
                checkboxShipment.checked = false;
                // Le prix de la livraison est caché.
                priceShipment.style.display = 'none';
            }
        }
    }
}

// Fonction permettant de faire apparaitre ou disparaitre.
// Le prix et de "checker" ou "dé-checker" l'autre checkbox.
function shipementCheck() {
    // Si le vendeur ne propose pas la remise en main propre.
    if (!checkboxHandDelivery) {
        // La checkbox de la livraison est "checker".
        checkboxShipment.checked = true;
    } else {
        // Si le vendeur propose également la remise en main propre.
        // Si la livraison est "dé-checker".
        if (checkboxShipment.checked === false) {
            // Le prix de la livraison est caché.
            priceShipment.style.display = 'none';
            // Si la remise en main propre est possible.
            if (checkboxHandDelivery) {
                // La remise en main propre est "checker".
                checkboxHandDelivery.checked = true;
                // Le prix de la remise en main propre est affiché.
                priceHandDelivery.style.display = 'block';
            }
        } else {
            // Si la livraison est "checker".
            // Le prix de la livraison est affiché.
            priceShipment.style.display = 'block';
            // Si la remise en main propre est possible.
            if (checkboxHandDelivery) {
                // La remise en main propre est "dé-checker".
                checkboxHandDelivery.checked = false;
                // Le prix de la remise en main propre est caché.
                priceHandDelivery.style.display = 'none';
            }
        }
    }
}