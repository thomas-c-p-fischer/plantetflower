// Script permettant de d'afficher la liste de pages dans un menu déroulant
// de la barre de navigation de base.html.twig.
// Recuperation des elements par Id qu'on initialise dans une variable.
let containerMenuBtn = document.getElementById("container-hamburger-menu");
let menuBtn = document.getElementById("hamburger-menu");
let pagesList = document.getElementById("list-mains_pages");
let secondaryUserPage = document.getElementById("container-dropdown-secondary_user_page");

// Event de type clic pour afficher et cacher la listes des pages.
menuBtn.addEventListener("click", () => {
    if (pagesList.style.display !== 'flex') {
        pagesList.style.display = 'flex';
        secondaryUserPage.style.display = 'flex';
    } else {
        pagesList.style.display = 'none';
        secondaryUserPage.style.display = 'none';
    }
})