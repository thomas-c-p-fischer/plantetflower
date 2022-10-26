// Script permettant d'effectuer un carrousel sur les différentes annonces.
// Déclaration puis initialisation de l'index à 0 (1ère annonce) et appel à la fonction de visibilité du slide.
let slideIndex = 0;
showSlides();
// Fonction gérant la visibilité de toutes les annonces.
function showSlides() {
    let i;
    let slides = document.getElementsByClassName("mySlides");
    // Toutes les annonces ne sont pas visibles.
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    slideIndex++;
    // Si l'annonce actuelle est la seule annonce alors taille de l'index est de 1.
    if (slideIndex > slides.length) {
        slideIndex = 1
    }
    // Si les deux annonces suivantes celle actuel alors l'annonce actuelle + les deux suivantes sont visibles.
    if (slides[slideIndex] && slides[slideIndex + 1]) {
        slides[slideIndex - 1].style.display = "block";
        slides[slideIndex].style.display = "block";
        slides[slideIndex + 1].style.display = "block";
        // Change d'annonce toutes les 3 secondes.
        setTimeout(showSlides, 3000);
    } else {
        // Refait appel à la fonction pour retour à la 1ère annonce
        showSlides();
    }
}