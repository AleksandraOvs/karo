document.addEventListener('DOMContentLoaded', () => {

    const filtersHead = document.querySelector('.filters-head');
    const filtersWrapper = document.querySelector('.filters-wrapper');

    if (!filtersHead || !filtersWrapper) return;

    const body = document.body;
    const filterActions = document.getElementById('cwc-filter-actions');

    // Открытие / закрытие фильтра
    filtersHead.addEventListener('click', function () {
        filtersWrapper.classList.toggle('opened');

        // fixed для body только когда фильтр открыт
        body.classList.toggle('fixed', filtersWrapper.classList.contains('opened'));
    });

    // На мобильных (<576px) кнопка "Применить" закрывает фильтр
    if (filterActions) {
        filterActions.addEventListener('click', function () {
            if (window.innerWidth < 576) {
                filtersWrapper.classList.remove('opened');
                body.classList.remove('fixed');
            }
        });
    }

    function initPriceSlider() {

        const slider = document.getElementById('price-slider');
        if (!slider) return;

        const min = parseInt(slider.dataset.min);
        const max = parseInt(slider.dataset.max);

        const minInput = document.getElementById('min_price');
        const maxInput = document.getElementById('max_price');

        if (!minInput || !maxInput) return;

        jQuery(slider).slider({
            range: true,
            min: min,
            max: max,
            values: [min, max],

            slide: function (event, ui) {
                minInput.value = ui.values[0];
                maxInput.value = ui.values[1];
            }
        });

        // Синхронизация input → slider
        minInput.addEventListener('change', function () {
            jQuery(slider).slider('values', 0, this.value);
        });

        maxInput.addEventListener('change', function () {
            jQuery(slider).slider('values', 1, this.value);
        });
    }

    // Запуск
    initPriceSlider();

});
