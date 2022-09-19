//Utilisation du Js pour faire un menu deroulant.

//declaration d'un boolean a false.
let bool = false;

//Recuperation des elements par Id qu'on initialise dans une variable.
let part1 = document.getElementById('part1');
let reponse = document.getElementById('reponse');
let chevron = document.getElementById('chevron');
let part2 = document.getElementById('part2');
let reponse2 = document.getElementById('reponse2');
let chevron2 = document.getElementById('chevron2');
let part3 = document.getElementById('part3');
let reponse3 = document.getElementById('reponse3');
let chevron3 = document.getElementById('chevron3');
let part4 = document.getElementById('part4');
let reponse4 = document.getElementById('reponse4');
let chevron4 = document.getElementById('chevron4');
let part5 = document.getElementById('part5');
let reponse5 = document.getElementById('reponse5');
let chevron5 = document.getElementById('chevron5');
let part6 = document.getElementById('part6');
let reponse6 = document.getElementById('reponse6');
let chevron6 = document.getElementById('chevron6');
let part7 = document.getElementById('part7');
let reponse7 = document.getElementById('reponse7');
let chevron7 = document.getElementById('chevron7');

//Etant donné que le script ce lis ligne par ligne, on initialise le boolean à false sur l'élement cliquable afin que le menu soit fermé au chargement de la page
reponse.style.display = 'none';
reponse2.style.display = 'none';
reponse3.style.display = 'none';
reponse4.style.display = 'none';
reponse5.style.display = 'none';
reponse6.style.display = 'none';
reponse7.style.display = 'none';

//Ajout un event de type clic avec une condition
part1.addEventListener('click', () => {
    if (bool) {
        reponse.style.display = 'none';
        chevron.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        reponse.style.display = 'block';
        chevron.style.transform = 'rotate(180deg)'
        bool = true
    }
})
part2.addEventListener('click', () => {
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

part3.addEventListener('click', () => {
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
part4.addEventListener('click', () => {
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
part5.addEventListener('click', () => {
    if (bool) {
        reponse5.style.display = 'none';

        chevron5.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        reponse5.style.display = 'block';
        chevron5.style.transform = 'rotate(180deg)'
        bool = true
    }
})
part6.addEventListener('click', () => {
    if (bool) {
        reponse6.style.display = 'none';

        chevron6.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        reponse6.style.display = 'block';
        chevron6.style.transform = 'rotate(180deg)'
        bool = true
    }
})
part7.addEventListener('click', () => {
    if (bool) {
        reponse7.style.display = 'none';

        chevron7.style.transform = 'rotate(0deg)'
        bool = false

    } else {
        reponse7.style.display = 'block';
        chevron7.style.transform = 'rotate(180deg)'
        bool = true
    }
})


