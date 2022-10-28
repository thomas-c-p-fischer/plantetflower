// Script permettant de d'afficher la liste de pages dans un menu déroulant
// de la barre de navigation de base.html.twig.
// Recuperation des elements par Id qu'on initialise dans une variable.
let menuBtn = document.getElementById("hamburger-menu");
let pagesList = document.getElementById("list-mains_pages");
let secondaryUserPage = document.getElementById("container-dropdown-secondary_user_page");
let responsive;

// Event de type clic pour afficher et cacher la listes des pages.
menuBtn.addEventListener("click", () => {
    responsive = true;
    if (pagesList.style.display !== 'flex') {
        pagesList.style.display = 'flex';
        secondaryUserPage.style.display = 'flex';
    } else {
        pagesList.style.display = 'none';
        secondaryUserPage.style.display = 'none';
    }
})

// Event de type clic pour cacher la listes des pages si l'utilisateur clique ailleurs.
$(document.body).click(function(e) {
    if( !$.contains(menuBtn,e.target) && responsive === true ) {
        pagesList.style.display = 'none';
        secondaryUserPage.style.display = 'none';
    }
});