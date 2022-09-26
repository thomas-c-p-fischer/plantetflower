//Utilisation du Js pour faire un menu deroulant.

//declaration d'un boolean a false.
let bool = false;

//Recuperation des elements par Id qu'on initialise dans une variable.
let question_1 = document.getElementById('question_1');
let answer_1 = document.getElementById('answer_1');
let chevron_1 = document.getElementById('faq_chevron_1');
let question_2 = document.getElementById('question_2');
let answer_2 = document.getElementById('answer_2');
let chevron_2 = document.getElementById('faq_chevron_2');
let question_3 = document.getElementById('question_3');
let answer_3 = document.getElementById('answer_3');
let chevron_3 = document.getElementById('faq_chevron_3');
let question_4 = document.getElementById('question_4');
let answer_4 = document.getElementById('answer_4');
let chevron_4 = document.getElementById('faq_chevron_4');

//Etant donné que le script ce lis ligne par ligne, on initialise le boolean à false sur l'élement cliquable afin que le menu soit fermé au chargement de la page
answer_1.style.display = 'none';
answer_2.style.display = 'none';
answer_3.style.display = 'none';
answer_4.style.display = 'none';

//Ajout un event de type clic avec une condition
question_1.addEventListener('click', () => {
    if (bool) {
        answer_1.style.display = 'none';
        //la ligne 26 fait en sorte que le chevron fait une rotation de 180° lorsque que le boolean est true
        chevron_1.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        answer_1.style.display = 'block';
        chevron_1.style.transform = 'rotate(180deg)'
        bool = true
    }
})
question_2.addEventListener('click', () => {
    if (bool) {
        answer_2.style.display = 'none';

        chevron_2.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        answer_2.style.display = 'block';
        chevron_2.style.transform = 'rotate(180deg)'
        bool = true
    }
})

question_3.addEventListener('click', () => {
    if (bool) {
        answer_3.style.display = 'none';

        chevron_3.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        answer_3.style.display = 'block';
        chevron_3.style.transform = 'rotate(180deg)'
        bool = true
    }
})
question_4.addEventListener('click', () => {
    if (bool) {
        answer_4.style.display = 'none';

        chevron_4.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        answer_4.style.display = 'block';
        chevron_4.style.transform = 'rotate(180deg)'
        bool = true
    }
})


