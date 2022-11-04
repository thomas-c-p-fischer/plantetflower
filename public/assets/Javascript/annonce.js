// Script permettant d'afficher la demande de confirmation de suppression d'annonce
// dans la page annonce.html.twig.
// Recuperation des elements par Id qu'on initialise dans une variable.
let aDeleteBtn = document.getElementById('annonce-delete_btn');
let deleteBtn;
let confirmDelete;
let cancelBtn;

// Si aDeleteBtn est vrai autrement-dit si nous sommes dans la page annonce.html.twig.
if (aDeleteBtn) {
    deleteBtn = document.getElementById('annonce-delete_btn');
    confirmDelete = document.getElementById('annonce-confirm_delete');
    cancelBtn = document.getElementById('annonce-answer-delete_no');
}

if (aDeleteBtn) {
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
