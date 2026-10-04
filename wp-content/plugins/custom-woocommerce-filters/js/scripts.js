document.addEventListener('DOMContentLoaded', () => {

    const filtersHead = document.querySelector('.filter-toggle');
    const filtersWrapper = document.querySelector('.filters-wrapper');
    const closeBtn = document.querySelector('.toggle-close');

    const button = document.querySelector('button.toggle-filter');
    const sidebar = document.querySelector('.sidebar-area-wrapper._filters');

    const applyBtn = document.querySelector('#cwc-apply-filters');


    // ==================================================
    // FIXED ДЛЯ BODY ТОЛЬКО НА <576PX
    // ==================================================

    const updateBodyFixed = (isOpen) => {

        if (window.innerWidth < 576 && isOpen) {
            document.body.classList.add('fixed');
        } else {
            document.body.classList.remove('fixed');
        }

    };


    // ==================================================
    // ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
    // ==================================================

    const openFilter = () => {

        if (filtersWrapper) {
            filtersWrapper.classList.add('opened');
        }

        updateBodyFixed(true);
    };


    const closeFilter = () => {

        if (filtersWrapper) {
            filtersWrapper.classList.remove('opened');
        }

        updateBodyFixed(false);
    };


    // ==================================================
    // ОСНОВНОЙ ФИЛЬТР
    // ==================================================

    if (filtersHead && filtersWrapper) {

        filtersHead.addEventListener('click', () => {

            const isOpened = filtersWrapper.classList.contains('opened');

            if (isOpened) {
                closeFilter();
            } else {
                openFilter();
            }

        });

    }


    // ==================================================
    // ЗАКРЫТИЕ ПО .toggle-close
    // ==================================================

    if (closeBtn) {

        closeBtn.addEventListener('click', () => {

            closeFilter();

            if (sidebar) {
                sidebar.classList.remove('show');
            }

        });

    }


    /* ===============================
       КНОПКА ОТКРЫТИЯ ФИЛЬТРА НА <=992PX
    =============================== */

    if (button && sidebar) {

        // Открытие / переключение
        button.addEventListener('click', () => {

            if (window.innerWidth <= 992) {

                const isOpened = sidebar.classList.contains('show');

                sidebar.classList.toggle('show');

                updateBodyFixed(!isOpened);

            }

        });


        // Закрытие по кнопке
        if (closeBtn) {

            closeBtn.addEventListener('click', () => {

                sidebar.classList.remove('show');
                updateBodyFixed(false);

            });

        }


        // ==================================================
        // ЗАКРЫТИЕ ПО КЛИКУ ВНЕ САЙДБАРА
        // ==================================================

        document.addEventListener('click', (e) => {

            if (
                window.innerWidth <= 992 &&
                sidebar.classList.contains('show') &&
                !sidebar.contains(e.target) &&
                !button.contains(e.target)
            ) {

                sidebar.classList.remove('show');
                updateBodyFixed(false);

            }

        });


        // ==================================================
        // ЗАКРЫТИЕ ПО ESC
        // ==================================================

        document.addEventListener('keydown', (e) => {

            if (e.key === 'Escape') {

                sidebar.classList.remove('show');
                closeFilter();

            }

        });


        // ==================================================
        // ЗАКРЫТИЕ ПОСЛЕ ПРИМЕНЕНИЯ ФИЛЬТРОВ <576PX
        // ==================================================

        if (applyBtn) {

            applyBtn.addEventListener('click', () => {

                if (window.innerWidth < 576) {

                    sidebar.classList.remove('show');
                    closeFilter();

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
                closeFilter();

            }

        }, { passive: true });

    }


    // ==================================================
    // КОНТРОЛЬ ПРИ ИЗМЕНЕНИИ ШИРИНЫ ЭКРАНА
    // ==================================================

    window.addEventListener('resize', () => {

        const filterIsOpen =
            (filtersWrapper && filtersWrapper.classList.contains('opened')) ||
            (sidebar && sidebar.classList.contains('show'));

        updateBodyFixed(filterIsOpen);

    });

});
