//Utilisation du Js pour déroulé la demande de confirmation de suppression d'annonce.

//Recuperation des elements par Id qu'on initialise dans une variable.
let delete_btn = document.getElementById('delete_btn');
let confirm_delete = document.getElementById('confirm_delete');
let cancel_btn = document.getElementById('cancel_btn');

// Event de type clic pour afficher la confirmation
delete_btn.addEventListener("click", () => {
    confirm_delete.style.display = 'block';
});

// Event de type clic pour disparaitre la confirmation
cancel_btn.addEventListener("click", () => {
    confirm_delete.style.display = 'none';
});
