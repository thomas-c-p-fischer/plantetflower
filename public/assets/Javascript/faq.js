//Utilisation du Js pour faire un menu deroulant.

//declaration d'un boolean a false.
let bool = false;

//Recuperation des elements par Id qu'on initialise dans une variable.
let question = document.getElementById('question');
let reponse = document.getElementById('reponse');
let chevron = document.getElementById('chevron');
let question2 = document.getElementById('question2');
let reponse2 = document.getElementById('reponse2');
let chevron2 = document.getElementById('chevron2');
let question3 = document.getElementById('question3');
let reponse3 = document.getElementById('reponse3');
let chevron3 = document.getElementById('chevron3');
let question4 = document.getElementById('question4');
let reponse4 = document.getElementById('reponse4');
let chevron4 = document.getElementById('chevron4');

//Etant donné que le script ce lis ligne par ligne, on initialise le boolean a false sur l'element cliquable afin que le menu soit fermé au chargement de la page
reponse.style.display = 'none';
reponse2.style.display = 'none';
reponse3.style.display = 'none';
reponse4.style.display = 'none';

//Ajout un event de type clic avec une condition
question.addEventListener('click', () => {
    if (bool) {
        reponse.style.display = 'none';
        //la ligne 26 fait en sorte que le chevron fait une rotation de 180° lorsque que le boolean est true
        chevron.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        reponse.style.display = 'block';
        chevron.style.transform = 'rotate(180deg)'
        bool = true
    }
})
question2.addEventListener('click', () => {
    if (bool) {
        reponse2.style.display = 'none';

        chevron2.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        reponse2.style.display = 'block';
        chevron2.style.transform = 'rotate(180deg)'
        bool = true
    }
})

question3.addEventListener('click', () => {
    if (bool) {
        reponse3.style.display = 'none';

        chevron3.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        reponse3.style.display = 'block';
        chevron3.style.transform = 'rotate(180deg)'
        bool = true
    }
})
question4.addEventListener('click', () => {
    if (bool) {
        reponse4.style.display = 'none';

        chevron4.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        reponse4.style.display = 'block';
        chevron4.style.transform = 'rotate(180deg)'
        bool = true
    }
})


