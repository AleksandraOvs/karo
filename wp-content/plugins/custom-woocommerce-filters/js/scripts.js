document.addEventListener('DOMContentLoaded', () => {

    const filtersHead = document.querySelector('.filter-toggle');
    const filtersWrapper = document.querySelector('.filters-wrapper');
    const closeBtn = document.querySelector('.toggle-close');
    if (!filtersHead || !filtersWrapper) return;

    filtersHead.addEventListener('click', function () {
        filtersWrapper.classList.toggle('opened');
    });

    closeBtn.addEventListener('click', function () {
        filtersWrapper.classList.toggle('opened');
    });


    /* ===============================
       КНОПКА ОТКРЫТИЯ ФИЛЬТРА НА <992PX
    =============================== */

    const button = document.querySelector('button.toggle-filter');
    const sidebar = document.querySelector('.sidebar-area-wrapper._filters');

    const applyBtn = document.querySelector('#cwc-apply-filters');

    if (!button || !sidebar) return;

    // Открытие / переключение
    button.addEventListener('click', () => {
        if (window.innerWidth <= 992) {
            sidebar.classList.toggle('show');
        }
    });

    // Закрытие по кнопке
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            sidebar.classList.remove('show');
        });
    }

    // Закрытие по клику вне сайдбара
    document.addEventListener('click', (e) => {
        if (
            window.innerWidth <= 992 &&
            sidebar.classList.contains('show') &&
            !sidebar.contains(e.target) &&
            !button.contains(e.target)
        ) {
            sidebar.classList.remove('show');
        }
    });

    // Закрытие по Esc
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            sidebar.classList.remove('show');
        }
    });

    // Закрытие после применения фильтров на <576px
    if (applyBtn) {
        applyBtn.addEventListener('click', () => {
            if (window.innerWidth < 576) {
                sidebar.classList.remove('show');
            }
        });
    }

    /* ===============================
   ЗАКРЫТИЕ ФИЛЬТРА СВАЙПОМ ВНИЗ
=============================== */

    let touchStartX = 0;
    let touchStartY = 0;

    sidebar.addEventListener('touchstart', (e) => {

        if (window.innerWidth > 992) return;

        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;

    }, { passive: true });


    sidebar.addEventListener('touchend', (e) => {

        if (window.innerWidth > 992) return;

        const touchEndX = e.changedTouches[0].clientX;
        const touchEndY = e.changedTouches[0].clientY;

        const deltaX = touchEndX - touchStartX;
        const deltaY = touchEndY - touchStartY;

        // Свайп должен быть преимущественно вертикальным
        if (Math.abs(deltaY) <= Math.abs(deltaX)) {
            return;
        }

        // Минимальная длина свайпа — 60px
        if (Math.abs(deltaY) < 60) {
            return;
        }

        // Свайп ВНИЗ закрывает фильтр
        if (deltaY > 0) {
            sidebar.classList.remove('show');
            filtersWrapper.classList.remove('opened');
        }

    }, { passive: true });
});