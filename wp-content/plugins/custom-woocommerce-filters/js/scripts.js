document.addEventListener('DOMContentLoaded', () => {

    const filtersHead = document.querySelector('.filter-toggle');
    const filtersWrapper = document.querySelector('.filters-wrapper');
    const closeBtn = document.querySelector('.toggle-close');

    const button = document.querySelector('button.toggle-filter');

    const applyBtnSelector = '#cwc-apply-filters';


    // ==================================================
    // ПОЛУЧЕНИЕ SIDEBAR
    // ==================================================

    const getSidebar = () => {
        return document.querySelector('.sidebar-area-wrapper._filters');
    };


    // ==================================================
    // FIXED ДЛЯ BODY ТОЛЬКО НА <576PX
    // ==================================================

    const updateBodyFixed = () => {

        const sidebar = getSidebar();

        const filterIsOpen =
            (filtersWrapper && filtersWrapper.classList.contains('opened')) ||
            (sidebar && sidebar.classList.contains('show'));

        if (window.innerWidth < 576 && filterIsOpen) {
            document.body.classList.add('fixed');
        } else {
            document.body.classList.remove('fixed');
        }

    };


    // ==================================================
    // ОТКРЫТИЕ ФИЛЬТРА
    // ==================================================

    const openFilter = () => {

        if (filtersWrapper) {
            filtersWrapper.classList.add('opened');
        }

        updateBodyFixed();

    };


    // ==================================================
    // ПОЛНОЕ ЗАКРЫТИЕ ФИЛЬТРА
    // ==================================================

    const closeFilter = () => {

        const sidebar = getSidebar();

        if (filtersWrapper) {
            filtersWrapper.classList.remove('opened');
        }

        if (sidebar) {
            sidebar.classList.remove('show');
        }

        document.body.classList.remove('fixed');

    };


    // ==================================================
    // ОСНОВНОЙ ФИЛЬТР
    // ==================================================

    if (filtersHead && filtersWrapper) {

        filtersHead.addEventListener('click', () => {

            if (filtersWrapper.classList.contains('opened')) {
                closeFilter();
            } else {
                openFilter();
            }

        });

    }


    // ==================================================
    // КНОПКА .toggle-close
    // ==================================================

    if (closeBtn) {

        closeBtn.addEventListener('click', () => {
            closeFilter();
        });

    }


    /* ===============================
       КНОПКА ОТКРЫТИЯ ФИЛЬТРА <=992PX
    =============================== */

    if (button) {

        button.addEventListener('click', () => {

            if (window.innerWidth > 992) {
                return;
            }

            const sidebar = getSidebar();

            if (!sidebar) {
                return;
            }

            const isOpened = sidebar.classList.contains('show');

            if (isOpened) {
                closeFilter();
            } else {
                sidebar.classList.add('show');
                updateBodyFixed();
            }

        });

    }


    // ==================================================
    // ЗАКРЫТИЕ ПО КЛИКУ ВНЕ SIDEBAR
    // ==================================================

    document.addEventListener('click', (e) => {

        const sidebar = getSidebar();

        if (!sidebar || !button) {
            return;
        }

        if (
            window.innerWidth <= 992 &&
            sidebar.classList.contains('show') &&
            !sidebar.contains(e.target) &&
            !button.contains(e.target)
        ) {
            closeFilter();
        }

    });


    // ==================================================
    // APPLY
    // ==================================================
    // Делегирование нужно потому, что кнопка может
    // пересоздаваться после AJAX-фильтрации.
    //
    // Используем capture=true, чтобы обработчик сработал
    // даже если другой скрипт вызывает stopPropagation().
    // ==================================================

    document.addEventListener('click', (e) => {

        const applyBtn = e.target.closest(applyBtnSelector);

        if (!applyBtn) {
            return;
        }

        if (window.innerWidth < 576) {
            closeFilter();
        }

    }, true);


    // ==================================================
    // ESC
    // ==================================================

    document.addEventListener('keydown', (e) => {

        if (e.key === 'Escape') {
            closeFilter();
        }

    });


    /* ===============================
       СВАЙП ВНИЗ
    =============================== */

    let touchStartX = 0;
    let touchStartY = 0;
    let touchStartElement = null;


    document.addEventListener('pointerdown', (e) => {

        if (e.pointerType !== 'touch') {
            return;
        }

        const sidebar = getSidebar();

        if (
            window.innerWidth > 992 ||
            !sidebar ||
            !sidebar.classList.contains('show')
        ) {
            return;
        }

        // Проверяем, что жест начался внутри sidebar
        if (!sidebar.contains(e.target)) {
            return;
        }

        touchStartX = e.clientX;
        touchStartY = e.clientY;
        touchStartElement = e.target;

    }, { passive: true });


    document.addEventListener('pointerup', (e) => {

        if (e.pointerType !== 'touch') {
            return;
        }

        const sidebar = getSidebar();

        if (
            window.innerWidth > 992 ||
            !sidebar ||
            !sidebar.classList.contains('show') ||
            !touchStartElement
        ) {
            touchStartElement = null;
            return;
        }

        const deltaX = e.clientX - touchStartX;
        const deltaY = e.clientY - touchStartY;

        // Сбрасываем стартовую точку
        touchStartElement = null;

        // Жест должен быть преимущественно вертикальным
        if (Math.abs(deltaY) <= Math.abs(deltaX)) {
            return;
        }

        // Минимальная длина свайпа
        if (Math.abs(deltaY) < 60) {
            return;
        }

        // Свайп вниз
        if (deltaY > 0) {
            closeFilter();
        }

    }, { passive: true });


    // ==================================================
    // RESIZE
    // ==================================================

    window.addEventListener('resize', () => {

        updateBodyFixed();

    });

});
