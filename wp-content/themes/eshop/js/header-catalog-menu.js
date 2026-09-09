document.addEventListener('DOMContentLoaded', function () {

    const catalogButton = document.querySelector('.menu-toggle');
    const catalogMenu = document.querySelector('.main-menu');
    const closeButton = document.querySelector('.collapse-menu-button');


    if (!catalogButton || !catalogMenu) {
        console.log('Каталог: элементы не найдены');
        return;
    }

    // Открытие / закрытие каталога
    catalogButton.addEventListener('click', function (event) {

        event.stopPropagation();

        catalogMenu.classList.toggle('show');

    });

    // Закрытие по крестику
    if (closeButton) {
        closeButton.addEventListener('click', function (event) {

            event.stopPropagation();

            catalogMenu.classList.remove('show');

        });
    }


    // Закрытие при клике вне меню
    document.addEventListener('click', function (event) {

        if (
            catalogMenu.classList.contains('show') &&
            !catalogMenu.contains(event.target) &&
            !catalogButton.contains(event.target)
        ) {
            catalogMenu.classList.remove('show');
        }

    });

});