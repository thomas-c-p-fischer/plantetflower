// Script permettant d'afficher la demande de confirmation de suppression d'annonce
// dans les pages annonce.html.twig et editAnnonce.html.twig.
// Recuperation des elements par Id qu'on initialise dans une variable.
let aDeleteBtn = document.getElementById('annonce-delete_btn');
let eADeleteBtn = document.getElementById('editAnnonce-delete_btn');
let deleteBtn;
let confirmDelete;
let cancelBtn;

// Si aDeleteBtn est vrai autrement-dit si nous sommes dans la page annonce.html.twig.
if (aDeleteBtn) {
    deleteBtn = document.getElementById('annonce-delete_btn');
    confirmDelete = document.getElementById('annonce-confirm_delete');
    cancelBtn = document.getElementById('annonce-answer-delete_no');
}

// Si eADeleteBtn est vrai autrement-dit si nous sommes dans la page editAnnonce.html.twig.
if (eADeleteBtn) {
    containerReturnDeleteBtn = document.getElementById('editAnnonce-container-return_delete_btn');
    deleteBtn = document.getElementById('editAnnonce-delete_btn');
    confirmDelete = document.getElementById('editAnnonce-confirm_delete');
    cancelBtn = document.getElementById('editAnnonce-answer-delete_no');
}
if (aDeleteBtn || eADeleteBtn) {
    // Event de type clic pour afficher et cacher la confirmation.
    deleteBtn.addEventListener("click", () => {
        if (confirmDelete.style.display !== 'block') {
            confirmDelete.style.display = 'block';
            if (eADeleteBtn) {
                containerReturnDeleteBtn.style.display = 'none';
            }
        } else {
            confirmDelete.style.display = 'none';
        }
    });

// Event de type clic pour disparaitre la confirmation.
    cancelBtn.addEventListener("click", () => {
        confirmDelete.style.display = 'none';
        if (eADeleteBtn) {
            containerReturnDeleteBtn.style.display = 'flex';
        }
    });
}

// Script permettant d'effectuer un carrousel sur les différentes annonces.
// Déclaration puis initialisation de l'index à 0 (1ère annonce) et appel à la fonction de visibilité du slide.
// let slideIndex = 0;
// showSlides();
//
// // Fonction gérant la visibilité de toutes les annonces.
// function showSlides() {
//     let i;
//     let slides = document.getElementsByClassName("mySlides");
//     // Toutes les annonces ne sont pas visibles.
//     for (i = 0; i < slides.length; i++) {
//         slides[i].style.display = "none";
//     }
//     slideIndex++;
//     // Si l'annonce actuelle est la seule annonce alors taille de l'index est de 1.
//     if (slideIndex > slides.length) {
//         slideIndex = 1
//     }
//     // Si les deux annonces suivantes celle actuel alors l'annonce actuelle + les deux suivantes sont visibles.
//     slides[slideIndex - 1].style.display = "block";
//     // Change d'annonce toutes les 2 secondes.
//     setTimeout(showSlides, 2000);
// }

// Si aDeleteBtn est vrai autrement-dit si nous sommes dans la page annonce.html.twig.
if (aDeleteBtn) {
    let slideIndex = 1;
    showDivs(slideIndex);

    function plusDivs(n) {
        showDivs(slideIndex += n);
    }

    function showDivs(n) {
        let i;
        let x = document.getElementsByClassName("image-slide");
        if (n > x.length) {slideIndex = 1}
        if (n < 1) {slideIndex = x.length}
        for (i = 0; i < x.length; i++) {
            x[i].style.display = "none";
        }
        x[slideIndex-1].style.display = "block";
    }
}


// Scripts destiner à la page createAnnonce.html.twig.
// Script permettant de faire disparaitre et apparaitre les images de l'annonce en BDD
// laissant la place à la possibilité d'ajout de nouvelles images
// Recuperation des elements par Id qu'on initialise dans des variables.
let eA_actualImage_0 = document.getElementById("editAnnonce-actualImage_0");
let eA_actualImage_1 = document.getElementById("editAnnonce-actualImage_1");
let eA_actualImage_2 = document.getElementById("editAnnonce-actualImage_2");

// Mise en tableau des variables des images de l'annonce en BDD présentes dans le twig.
let nbImages = 0;
let arrayImages = [eA_actualImage_0, eA_actualImage_1, eA_actualImage_2];

// Calcul du nombre réel d'images de l'annonce en BDD présentes dans le twig.
for (let i = 0; i < arrayImages.length; i++) {
    if (arrayImages[i] != null)
        nbImages++;
}

// Si eA_actualImage_0 est vrai autrement-dit si nous sommes dans la page editAnnonce.html.twig et que l'image 0 de l'annonce en BDD existe.
if (eA_actualImage_0) {

    // Recuperation du message de confirmation de suppression de l'image que l'on ne fait pas afficher.
    eA_deleteImage_0 = document.getElementById("editAnnonce-conf_del_img_0");
    eA_deleteImage_0.style.display = 'none';

    // Event de type clic pour disparaitre l'image et apparaitre le message de confirmation de suppression de cette image.
    eA_actualImage_0.addEventListener("click", () => {
        eA_actualImage_0.style.display = 'none';
        eA_deleteImage_0.style.display = 'block';
    });

    // Si-il-y-à plusieurs images de l'annonce de la BDD.
    if (nbImages > 1) {

        // Recuperation de la réponse "non" du message de confirmation de suppression de l'image.
        eA_noDelImage_0 = document.getElementById("editAnnonce-ans-del_no_img_0");

        // Event de type clic pour disparaitre le message de confirmation de suppression et refaire apparaitre l'image.
        eA_noDelImage_0.addEventListener("click", () => {
            eA_actualImage_0.style.display = 'block';
            eA_deleteImage_0.style.display = 'none';
        });

        // Si-il n'-y-à qu'une seul image de l'annonce de la BDD.
    } else {

        // Recuperation de la réponse "rétablir" du message obligeant à conserver l'image.
        eA_restoreImage_0 = document.getElementById("editAnnonce-restore_img_0");

        // Event de type clic pour disparaitre le message obligeant à conserver et refaire apparaitre l'image.
        eA_restoreImage_0.addEventListener("click", () => {
            eA_actualImage_0.style.display = 'block';
            eA_deleteImage_0.style.display = 'none';
        });
    }
}

// Si eA_actualImage_1 est vrai autrement-dit si nous sommes dans la page editAnnonce.html.twig et que l'image 1 de l'annonce en BDD existe.
if (eA_actualImage_1) {

    // Recuperation du message de confirmation de suppression de l'image que l'on ne fait pas afficher.
    eA_deleteImage_1 = document.getElementById("editAnnonce-conf_del_img_1");
    eA_noDelImage_1 = document.getElementById("editAnnonce-ans-del_no_img_1");
    eA_deleteImage_1.style.display = 'none';

    // Event de type clic pour disparaitre l'image et apparaitre le message de confirmation de suppression de cette image.
    eA_actualImage_1.addEventListener("click", () => {
        eA_actualImage_1.style.display = 'none';
        eA_deleteImage_1.style.display = 'block';
    });

    // Event de type clic pour disparaitre le message de confirmation de suppression et refaire apparaitre l'image.
    eA_noDelImage_1.addEventListener("click", () => {
        eA_actualImage_1.style.display = 'block';
        eA_deleteImage_1.style.display = 'none';
    });
}

// Si eA_actualImage_2 est vrai autrement-dit si nous sommes dans la page editAnnonce.html.twig et que l'image 2 de l'annonce en BDD existe.
if (eA_actualImage_2) {

    // Recuperation du message de confirmation de suppression de l'image que l'on ne fait pas afficher.
    eA_deleteImage_2 = document.getElementById("editAnnonce-conf_del_img_2");
    eA_noDelImage_2 = document.getElementById("editAnnonce-ans-del_no_img_2");
    eA_deleteImage_2.style.display = 'none';

    // Event de type clic pour disparaitre l'image et apparaitre le message de confirmation de suppression de cette image.
    eA_actualImage_2.addEventListener("click", () => {
        eA_actualImage_2.style.display = 'none';
        eA_deleteImage_2.style.display = 'block';
    });

    // Event de type clic pour disparaitre le message de confirmation de suppression et refaire apparaitre l'image.
    eA_noDelImage_2.addEventListener("click", () => {
        eA_actualImage_2.style.display = 'block';
        eA_deleteImage_2.style.display = 'none';
    });
}

// Script permettant de revenir sur la page précédente de createAnnonce.html.twig.
// Recuperation de l'element par son Id où l'on change son lien hypertexte.
if (document.getElementById("cea-return_back_page")) {
    document.getElementById("cea-return_back_page").href ="javascript:history.back()";
}

// Scripts destiner aux pages createAnnonce.html.twig et editAnnonce.html.twig.
// Script permettant de faire apparaitre et disparaitre les images upload et
// les images importer avant qu'elles ne soient envoyées en BDD.
// Recuperation des elements par Id qu'on initialise dans des variables.
let cANewImg0 = document.getElementById("createAnnonce-new_img_0");
let cANewImg1 = document.getElementById("createAnnonce-new_img_1");
let cANewImg2 = document.getElementById("createAnnonce-new_img_2");
let eANewImg1 = document.getElementById("editAnnonce-new_img_1");
let eANewImg2 = document.getElementById("editAnnonce-new_img_2");

// Déclarations de variables qui peuvent éventuellement servir sous certaines conditions.
let uploader;
let uploader0;
let uploader1;
let uploader2;
let containerNewImg0;
let containerNewImg1;
let containerNewImg2;
let containerNewImg;
let newImg;

// On fait disparaitre les emplacements des images non encore upload et on met les Id des uploader dans les variables.
// Si eA_actualImage_0 est faux autrement-dit si nous ne sommes pas dans la page editAnnonce.html.twig.
if (!eA_actualImage_0) {
    containerNewImg0 = document.getElementById("createAnnonce-container-new_img_0")
    containerNewImg1 = document.getElementById("createAnnonce-container-new_img_1")
    containerNewImg2 = document.getElementById("createAnnonce-container-new_img_2")
    containerNewImg0.style.display = 'none';
    containerNewImg1.style.display = 'none';
    containerNewImg2.style.display = 'none';
    uploader0 = document.getElementById("createAnnonce-uploader_0")
    uploader1 = document.getElementById("createAnnonce-uploader_1")
    uploader2 = document.getElementById("createAnnonce-uploader_2")
} else {
    containerNewImg1 = document.getElementById("editAnnonce-container-new_img_1")
    containerNewImg2 = document.getElementById("editAnnonce-container-new_img_2")
    containerNewImg1.style.display = 'none';
    containerNewImg2.style.display = 'none';
    uploader1 = document.getElementById("editAnnonce-uploader_1")
    uploader2 = document.getElementById("editAnnonce-uploader_2")
}

// Suite de fonctions appelant une seule et unique fonction permettant pour
// chaque image upload d'apparaitre et de faire disparaitre son uploader.
let createAnnoncePreviewNewImg0 = function (e) {
    containerNewImg = containerNewImg0;
    newImg = cANewImg0;
    uploader = uploader0;
    previewPicture(e);
}

let createAnnoncePreviewNewImg1 = function (e) {
    containerNewImg = containerNewImg1;
    newImg = cANewImg1;
    uploader = uploader1;
    previewPicture(e);
}

let createAnnoncePreviewNewImg2 = function (e) {
    containerNewImg = containerNewImg2;
    newImg = cANewImg2;
    uploader = uploader2;
    previewPicture(e);
}

let editAnnoncePreviewNewImg1 = function (e) {
    containerNewImg = containerNewImg1;
    newImg = eANewImg1;
    uploader = uploader1;
    previewPicture(e);
}

let editAnnoncePreviewNewImg2 = function (e) {
    containerNewImg = containerNewImg2;
    newImg = eANewImg2;
    uploader = uploader2;
    previewPicture(e);
}

// Fonction permettant à une image upload d'apparaitre et de faire disparaitre son uploader.
function previewPicture(e) {
    // e.files contient un objet FileList.
    const [picture] = e.files
    // "picture" est un objet File.
    if (picture) {
        // On change l'URL de l'image.
        newImg.src = URL.createObjectURL(picture)
        // On fait apparaitre l'image upload et disparaitre son uploader.
        containerNewImg.style.display = 'block';
        uploader.style.display = 'none';
    }
}

// On fait disparaitre l'image upload lorsque l'on "click" dessus et on fait réapparaitre son uploader
// en appelant une fonction unique.
// Si eA_actualImage_0 est faux autrement-dit si nous ne sommes pas dans la page editAnnonce.html.twig.
if (!eA_actualImage_0) {
    cANewImg0.addEventListener("click", () => {
        a(containerNewImg0, uploader0)
    });

    cANewImg1.addEventListener("click", () => {
        a(containerNewImg1, uploader1)
    });

    cANewImg2.addEventListener("click", () => {
        a(containerNewImg2, uploader2)
    });
} else {
    eANewImg1.addEventListener("click", () => {
        a(containerNewImg1, uploader1)
    });

    eANewImg2.addEventListener("click", () => {
        a(containerNewImg2, uploader2)
    });
}

// Fonction qui fait disparaitre l'image upload lorsque l'on "click" dessus et fait réapparaitre son uploader.
function a(containerNewImg, uploader) {
    containerNewImg.style.display = 'none';
    uploader.style.display = 'block';
}

// Script permettant de calculer le prix définitif de façon dynamique.
// Recuperation des elements par Id (création ou édition de l'annonce) qu'on initialise dans une variable.
let cA_origin_price = document.getElementById("createAnnonce_origin_price");
let eA_origin_price = document.getElementById("editAnnonce_origin_price");

let origin_price;
let total_price;
let price_message;
let submit;

// Si création d'une annonce.
if (cA_origin_price) {

    origin_price = document.getElementById("createAnnonce_origin_price");
    total_price = document.getElementById("createAnnonce_total_price");
    price_message = document.getElementById("createAnnonce_price_message");
    submit = document.getElementById("createAnnonce_submit");
    origin_price.addEventListener("input", sum);

// Si édition d'une annonce.
} else if (eA_origin_price) {

    origin_price = document.getElementById("editAnnonce_origin_price");
    total_price = document.getElementById("editAnnonce_total_price");
    price_message = document.getElementById("editAnnonce_price_message");
    submit = document.getElementById("editAnnonce_submit");
    origin_price.addEventListener("input", sum);
}

// Calcul du prix total.
function sum() {

    // Modification des éventuels caractères saisie par l'utilisateur.
    ChangeCharacters(origin_price)

    // Déclaration des valeurs ajoutées.
    // Frais fixes.
    let fixedFee = 0.7;

    // Pourcentage de marge.
    let marginPercentage = 0.12;

    // Calcul de la marge.
    let margin = parseFloat(origin_price.value) * marginPercentage;

    // Calcul du prix taxé arrondi au 3e chiffre après la virgule.
    let taxedPrice = parseFloat(origin_price.value) + fixedFee + margin;

    // Arrondissement à la demi-unité supérieure.
    // Récupération du modulo du prix taxé.
    let moduloTaxedPrice = taxedPrice % 1;

    // Initialisation de la variable du prix total.
    let totalPrice;

    // Si le modulo du prix taxé est entre 0 et 0,5.
    if (moduloTaxedPrice > 0 && moduloTaxedPrice < 0.5) {

        // Arrondissement du prix total à l'entier supérieur ajouté à 0,5.
        totalPrice = Math.round(taxedPrice) + 0.5;

        // Si le modulo du prix taxé est supérieur ou égal à 0,5 et inférieur à 1.
    } else if (moduloTaxedPrice >= 0.5 && moduloTaxedPrice < 1) {

        // Arrondissement du prix total à l'entier supérieur.
        totalPrice = Math.round(taxedPrice);
    }

    // Si le prix initial est supérieur à 0 et inférieur à 100.
    if (origin_price.value > 0 && origin_price.value < 100) {
        total_price.innerHTML = totalPrice + " €";
        price_message.innerHTML = "";

        // Activation du bouton submit du formulaire.
        submit.disabled = false;
        submit.style.backgroundColor = '';
        submit.style.color = '';
        submit.style.border = '';

    } else {
        total_price.innerHTML = "0 €";
    }
    // Si le prix initial est supérieur à 100.
    if (origin_price.value >= 100) {
        total_price.innerHTML = "0 €";
        price_message.innerHTML = "Nous ne prenons pas en compte votre annonce au-delà de 100€, veuillez nous excuser";

        // Désactivation du bouton submit du formulaire.
        submit.disabled = true;
        submit.style.backgroundColor = 'grey';
        submit.style.color = 'white';
        submit.style.border = '2px solid grey';
    }

    // Fonction permettant de modifier d'éventuels caractères saisie par l'utilisateur.
    function ChangeCharacters(origin_price) {

        // Modification de la virgule en point.
        if (origin_price.value.indexOf(",") !== -1) {
            origin_price.value = (origin_price.value.split(",")[0] + "." + origin_price.value.split(",")[1]);
        }

        // Suppression d'espace.
        if (origin_price.value.indexOf(" ") !== -1) {
            origin_price.value = (origin_price.value.split(" ")[0] + "" + origin_price.value.split(" ")[1]);
        }

        // Suppression de Euro.
        if (origin_price.value.indexOf("€") !== -1) {
            origin_price.value = (origin_price.value.split("€")[0] + "" + origin_price.value.split("€")[1]);
        }
    }
}

// Script permettant d'obliger l'utilisateur à checker au minimum un mode de remise du produit.
// Recuperation des elements par Id (création ou édition de l'annonce) qu'on initialise dans une variable.
let cA_hand_delivery = document.getElementById("createAnnonce-hand_delivery");
let eA_hand_delivery = document.getElementById("editAnnonce-hand_delivery");

let hand_delivery;
let shipement;

// Si création d'une annonce.
if (cA_hand_delivery) {

    hand_delivery = document.getElementById("createAnnonce-hand_delivery");
    shipement = document.getElementById("createAnnonce_shipement");
    hand_delivery.checked = true;
    shipement.checked = true;
    hand_delivery.addEventListener('click', hand_delivery_check);
    shipement.addEventListener('click', shipement_check);

// Si édition d'une annonce.
} else if (eA_hand_delivery) {

    hand_delivery = document.getElementById("editAnnonce-hand_delivery");
    shipement = document.getElementById("editAnnonce_shipement");
    hand_delivery.addEventListener('click', hand_delivery_check);
    shipement.addEventListener('click', shipement_check);

}

// Fonction permettant de checker la livraison si la remise en main propre est "dé-checker".
function hand_delivery_check() {
    if (hand_delivery.checked === false) {
        shipement.checked = true;
    }
}

// Fonction permettant de checker la remise en main propre si la livraison est "dé-checker".
function shipement_check() {
    if (shipement.checked === false) {
        hand_delivery.checked = true;
    }
}