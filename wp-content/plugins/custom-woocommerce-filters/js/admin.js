jQuery(function ($) {

    /*
     * Drag & Drop
     */
    $('.cwc-filter-list').sortable({
        handle: '.cwc-drag-handle',
        placeholder: 'cwc-filter-placeholder',
        tolerance: 'pointer'
    });


    /*
     * Открытие / закрытие настроек
     */
    $(document).on('click', '.cwc-expand', function () {

        const item = $(this).closest('.cwc-filter-item');

        item.toggleClass('is-open');

        $(this).text(
            item.hasClass('is-open')
                ? 'Свернуть'
                : 'Настроить'
        );

    });


    /*
     * Переключение типа фильтра
     */
    $(document).on('change', '.cwc-filter-type', function () {

        const select = $(this);
        const item = select.closest('.cwc-filter-item');

        item.attr(
            'data-type',
            select.val()
        );

    });


    /*
     * Добавление диапазона
     */
    $(document).on('click', '.cwc-add-range', function () {

        const button = $(this);
        const item = button.closest('.cwc-filter-item');

        const taxonomy = item.data('taxonomy');

        const index = item.find('.cwc-range-row').length;

        const row = `
            <div class="cwc-range-row">

                <input
                    type="number"
                    step="any"
                    name="filters[${taxonomy}][ranges][${index}][min]"
                    placeholder="От"
                >

                <span>—</span>

                <input
                    type="number"
                    step="any"
                    name="filters[${taxonomy}][ranges][${index}][max]"
                    placeholder="До"
                >

                <button
                    type="button"
                    class="button cwc-remove-range"
                >
                    Удалить
                </button>

            </div>
        `;

        item.find('.cwc-ranges').append(row);

    });


    /*
     * Удаление диапазона
     */
    $(document).on('click', '.cwc-remove-range', function () {

        $(this)
            .closest('.cwc-range-row')
            .remove();

    });


    /*
     * Перед отправкой формы
     *
     * Сохраняем текущий порядок атрибутов
     */
    $('form').on('submit', function () {

        const form = $(this);

        form.find('input[name="filter_order[]"]').remove();

        $('.cwc-filter-item').each(function () {

            const taxonomy = $(this).data('taxonomy');

            $('<input>')
                .attr({
                    type: 'hidden',
                    name: 'filter_order[]',
                    value: taxonomy
                })
                .appendTo(form);

        });

    });


    /*
     * Инициализация типа после загрузки
     */
    $('.cwc-filter-item').each(function () {

        const item = $(this);

        const type = item.find('.cwc-filter-type').val();

        item.attr('data-type', type);

    });

});