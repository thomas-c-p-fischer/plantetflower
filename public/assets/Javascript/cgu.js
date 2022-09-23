//Utilisation du Js pour faire un menu deroulant.

//declaration d'un boolean a false.
let bool = false;

//Recuperation des elements par Id qu'on initialise dans une variable.
let title_1 = document.getElementById('title_1');
let text_1 = document.getElementById('text_1');
let chevron_1 = document.getElementById('cgu_chevron_1');
let title_2 = document.getElementById('title_2');
let text_2 = document.getElementById('text_2');
let chevron_2 = document.getElementById('cgu_chevron_2');
let title_3 = document.getElementById('title_3');
let text_3 = document.getElementById('text_3');
let chevron_3 = document.getElementById('cgu_chevron_3');
let title_4 = document.getElementById('title_4');
let text_4 = document.getElementById('text_4');
let chevron_4 = document.getElementById('cgu_chevron_4');
let title_5 = document.getElementById('title_5');
let text_5 = document.getElementById('text_5');
let chevron_5 = document.getElementById('cgu_chevron_5');
let title_6 = document.getElementById('title_6');
let text_6 = document.getElementById('text_6');
let chevron_6 = document.getElementById('cgu_chevron_6');
let title_7 = document.getElementById('title_7');
let text_7 = document.getElementById('text_7');
let chevron_7 = document.getElementById('cgu_chevron_7');

//Etant donné que le script ce lis ligne par ligne, on initialise le boolean à false sur l'élement cliquable afin que le menu soit fermé au chargement de la page
text_1.style.display = 'none';
text_2.style.display = 'none';
text_3.style.display = 'none';
text_4.style.display = 'none';
text_5.style.display = 'none';
text_6.style.display = 'none';
text_7.style.display = 'none';

//Ajout un event de type clic avec une condition
title_1.addEventListener('click', () => {
    if (bool) {
        text_1.style.display = 'none';
        chevron_1.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        text_1.style.display = 'block';
        chevron_1.style.transform = 'rotate(180deg)'
        bool = true
    }
})
title_2.addEventListener('click', () => {
    if (bool) {
        text_2.style.display = 'none';

        chevron_2.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        text_2.style.display = 'block';
        chevron_2.style.transform = 'rotate(180deg)'
        bool = true
    }
})

title_3.addEventListener('click', () => {
    if (bool) {
        text_3.style.display = 'none';

        chevron_3.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        text_3.style.display = 'block';
        chevron_3.style.transform = 'rotate(180deg)'
        bool = true
    }
})
title_4.addEventListener('click', () => {
    if (bool) {
        text_4.style.display = 'none';

        chevron_4.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        text_4.style.display = 'block';
        chevron_4.style.transform = 'rotate(180deg)'
        bool = true
    }
})
title_5.addEventListener('click', () => {
    if (bool) {
        text_5.style.display = 'none';

        chevron_5.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        text_5.style.display = 'block';
        chevron_5.style.transform = 'rotate(180deg)'
        bool = true
    }
})
title_6.addEventListener('click', () => {
    if (bool) {
        text_6.style.display = 'none';

        chevron_6.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        text_6.style.display = 'block';
        chevron_6.style.transform = 'rotate(180deg)'
        bool = true
    }
})
title_7.addEventListener('click', () => {
    if (bool) {
        text_7.style.display = 'none';

        chevron_7.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        text_7.style.display = 'block';
        chevron_7.style.transform = 'rotate(180deg)'
        bool = true
    }
})


