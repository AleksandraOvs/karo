(function ($) {

    'use strict';


    /* =====================================================
     * STATE
     * ===================================================== */

    let cwcIsUpdating = false;
    let cwcTimer = null;

    window.cwcCurrentPage = 1;


    /* =====================================================
     * MOBILE
     * ===================================================== */

    function isMobile() {
        return window.innerWidth < 768;
    }


    /* =====================================================
     * FILTER ACCORDION
     * ===================================================== */

    // function initFilterAccordions(context) {

    //     const root = context || document;

    //     $(root)
    //         .find('.filter-item__title')
    //         .off('click.cwcFilter')
    //         .on('click.cwcFilter', function () {

    //             const $title = $(this);
    //             const $filter = $title.closest('.filter');
    //             const $content = $filter.find(
    //                 '.filter-item__content'
    //             );

    //             $title.toggleClass('active');
    //             $content.toggleClass('opened');
    //         });
    // }


    /* =====================================================
     * GET WRAPPER
     * ===================================================== */

    function getWrapper() {
        return $('.sidebar-area-wrapper._filters').first();
    }


    /* =====================================================
     * COLLECT FILTERS
     * ===================================================== */

    function collectFilters(wrapper) {

        const filters = {
            action: 'cwc_filter_products',
            nonce: cwc_ajax_object.nonce,
            page: window.cwcCurrentPage || 1
        };


        /* -------------------------------------------------
         * ATTRIBUTES
         * ------------------------------------------------- */

        wrapper
            .find('.sidebar-list[data-taxonomy]')
            .each(function () {

                const $list = $(this);

                const taxonomy = $list.data('taxonomy');

                if (!taxonomy) {
                    return;
                }


                /*
                 * NUMERIC RANGE
                 */
                if (
                    $list.hasClass(
                        'numeric-filter-list'
                    )
                ) {

                    const ranges = [];


                    $list
                        .find('a.filter-item.active')
                        .each(function () {

                            const $item = $(this);

                            const min = parseFloat(
                                String(
                                    $item.data('min')
                                ).replace(',', '.')
                            );

                            const max = parseFloat(
                                String(
                                    $item.data('max')
                                ).replace(',', '.')
                            );


                            if (
                                Number.isNaN(min) ||
                                Number.isNaN(max)
                            ) {
                                return;
                            }

                            ranges.push({
                                min: min,
                                max: max
                            });
                        });


                    if (ranges.length) {

                        filters[
                            'numeric_' + taxonomy
                        ] = ranges;
                    }


                    return;
                }


                /*
                 * Обычный атрибут.
                 */

                const values = [];


                $list
                    .find('a.filter-item.active')
                    .each(function () {

                        const slug = $(this)
                            .data('slug');

                        if (slug) {
                            values.push(slug);
                        }
                    });


                if (values.length) {

                    filters[
                        'filter_' + taxonomy
                    ] = values;
                }
            });


        /* -------------------------------------------------
         * PRICE
         * ------------------------------------------------- */

        const $minPrice = wrapper.find(
            '#min_price'
        );

        const $maxPrice = wrapper.find(
            '#max_price'
        );

        if (
            $minPrice.length &&
            $maxPrice.length
        ) {

            filters.min_price = parseFloat(
                String(
                    $minPrice.val()
                ).replace(',', '.')
            );

            filters.max_price = parseFloat(
                String(
                    $maxPrice.val()
                ).replace(',', '.')
            );
        }


        /* -------------------------------------------------
         * SORT
         * ------------------------------------------------- */

        const orderby = $('select.orderby').val();

        if (orderby) {
            filters.orderby = orderby;
        }


        /* -------------------------------------------------
         * CATEGORY
         * ------------------------------------------------- */

        const currentCat = wrapper.data(
            'current-cat'
        );

        if (currentCat) {

            filters.current_cat_id =
                parseInt(currentCat, 10);
        }


        return filters;
    }


    /* =====================================================
     * AJAX UPDATE
     * ===================================================== */

    function updateProducts(wrapper) {

        if (
            cwcIsUpdating ||
            !wrapper ||
            !wrapper.length
        ) {
            return;
        }


        cwcIsUpdating = true;


        const filters = collectFilters(
            wrapper
        );


        const $products = $('.products').first();


        if (!$products.length) {

            cwcIsUpdating = false;

            return;
        }


        console.log(
            'CWC FILTERS →',
            filters
        );


        $.ajax({

            url: cwc_ajax_object.ajax_url,

            type: 'POST',

            data: filters,


            beforeSend: function () {

                $products.fadeTo(
                    200,
                    0.5
                );
            },


            success: function (response) {

                if (
                    !response ||
                    !response.success
                ) {

                    console.error(
                        'CWC AJAX ERROR',
                        response
                    );

                    return;
                }


                $products
                    .html(
                        response.data.html
                    )
                    .fadeTo(
                        200,
                        1
                    );


                /*
                 * Пагинация.
                 */

                const $pagination =
                    $('#pagination');


                if ($pagination.length) {

                    $pagination.html(
                        response.data.pagination || ''
                    );

                } else if (
                    response.data.pagination
                ) {

                    $products.after(
                        '<div id="pagination">' +
                        response.data.pagination +
                        '</div>'
                    );
                }


            },


            error: function (
                xhr,
                status,
                error
            ) {

                console.error(
                    'CWC AJAX ERROR:',
                    status,
                    error,
                    xhr.responseText
                );
            },


            complete: function () {

                $products.fadeTo(
                    200,
                    1
                );


                cwcIsUpdating = false;


                if (isMobile()) {

                    $('.sidebar-area-wrapper._filters')
                        .removeClass('show');

                    $('.toggle-filter')
                        .removeClass('active');
                }
            }

        });
    }


    /* =====================================================
     * DEBOUNCE
     * ===================================================== */

    function debounceUpdate(wrapper) {

        window.cwcCurrentPage = 1;

        clearTimeout(cwcTimer);


        cwcTimer = setTimeout(
            function () {

                updateProducts(
                    wrapper
                );

            },
            300
        );
    }


    /* =====================================================
     * ATTRIBUTE CLICK
     * ===================================================== */

    $(document).on(
        'click',
        '.sidebar-list a.filter-item',
        function (e) {

            e.preventDefault();


            const $item = $(this);

            $item.toggleClass('active');


            const wrapper =
                $item.closest(
                    '.sidebar-area-wrapper'
                );


            debounceUpdate(wrapper);
        }
    );


    /* =====================================================
     * PRICE INPUT
     * ===================================================== */

    $(document).on(
        'change',
        '#min_price, #max_price',
        function () {

            const wrapper =
                $(this).closest(
                    '.sidebar-area-wrapper'
                );


            debounceUpdate(wrapper);
        }
    );


    /* =====================================================
     * PRICE SLIDER
     * ===================================================== */

    function initPriceSliders(
        context
    ) {

        const root =
            context || document;


        $(root)
            .find('#price-slider')
            .each(function () {

                const $slider = $(this);


                /*
                 * Не инициализируем второй раз.
                 */

                if (
                    $slider.hasClass(
                        'ui-slider'
                    )
                ) {
                    return;
                }


                const wrapper =
                    $slider.closest(
                        '.sidebar-area-wrapper'
                    );


                const $minInput =
                    wrapper.find(
                        '#min_price'
                    );

                const $maxInput =
                    wrapper.find(
                        '#max_price'
                    );


                let min = parseFloat(
                    String(
                        $slider.data('min')
                    ).replace(',', '.')
                );

                let max = parseFloat(
                    String(
                        $slider.data('max')
                    ).replace(',', '.')
                );


                let currentMin =
                    parseFloat(
                        String(
                            $minInput.val()
                        ).replace(',', '.')
                    );


                let currentMax =
                    parseFloat(
                        String(
                            $maxInput.val()
                        ).replace(',', '.')
                    );


                if (Number.isNaN(currentMin)) {
                    currentMin = min;
                }


                if (Number.isNaN(currentMax)) {
                    currentMax = max;
                }


                $slider.slider({

                    range: true,

                    min: min,

                    max: max,

                    values: [
                        currentMin,
                        currentMax
                    ],


                    slide: function (
                        event,
                        ui
                    ) {

                        $minInput.val(
                            ui.values[0]
                        );

                        $maxInput.val(
                            ui.values[1]
                        );
                    },


                    change: function () {

                        /*
                         * При RESET не запускаем
                         * дополнительный AJAX.
                         */
                        if (
                            $slider.data('cwc-resetting')
                        ) {
                            return;
                        }


                        if (!cwcIsUpdating) {

                            window.cwcCurrentPage = 1;

                            updateProducts(wrapper);
                        }
                    }
                });
            });
    }


    /* =====================================================
     * RESET
     * ===================================================== */

    $(document).on(
        'click',
        '#cwc-reset-filters',
        function (e) {

            e.preventDefault();

            const $button = $(this);

            const $wrapper = $button.closest(
                '.sidebar-area-wrapper._filters'
            );

            if (!$wrapper.length) {
                console.warn(
                    'CWC: wrapper фильтра не найден'
                );

                return;
            }


            /*
             * 1. Сбрасываем все атрибуты
             *
             * Обычные значения + числовые диапазоны.
             */
            $wrapper
                .find('.sidebar-list a.filter-item.active')
                .removeClass('active');


            /*
             * 2. Сбрасываем цену
             */

            const $slider = $wrapper.find(
                '#price-slider'
            );

            const $minPrice = $wrapper.find(
                '#min_price'
            );

            const $maxPrice = $wrapper.find(
                '#max_price'
            );


            if ($slider.length) {

                const min = parseFloat(
                    String(
                        $slider.data('min')
                    ).replace(',', '.')
                );

                const max = parseFloat(
                    String(
                        $slider.data('max')
                    ).replace(',', '.')
                );


                if (
                    !Number.isNaN(min) &&
                    !Number.isNaN(max)
                ) {

                    /*
                     * Сбрасываем inputs.
                     */
                    $minPrice.val(min);
                    $maxPrice.val(max);


                    /*
                     * Меняем slider.
                     *
                     * Перед изменением ставим флаг,
                     * чтобы change slider не отправил
                     * дополнительный AJAX.
                     */
                    $slider.data(
                        'cwc-resetting',
                        true
                    );


                    if (
                        $slider.hasClass('ui-slider')
                    ) {

                        $slider.slider(
                            'values',
                            [min, max]
                        );
                    }


                    $slider.data(
                        'cwc-resetting',
                        false
                    );
                }
            }


            /*
             * 3. Первая страница.
             */
            window.cwcCurrentPage = 1;


            /*
             * 4. Один AJAX после полного сброса.
             */
            updateProducts($wrapper);


            /*
             * 5. Закрываем мобильный фильтр.
             */
            if (isMobile()) {

                $wrapper.removeClass('show');

                $('.toggle-filter')
                    .removeClass('active');
            }
        }
    );

    /* =====================================================
     * APPLY
     * ===================================================== */

    $(document).on(
        'click',
        '#cwc-apply-filters',
        function (e) {

            e.preventDefault();


            const wrapper =
                $(this).closest(
                    '.sidebar-area-wrapper'
                );


            window.cwcCurrentPage = 1;


            updateProducts(
                wrapper
            );


            $('.sidebar-area-wrapper._filters')
                .removeClass('show');

            $('.toggle-filter')
                .removeClass('active');
        }
    );


    /* =====================================================
     * SORT
     * ===================================================== */

    $(document).on(
        'change',
        'select.orderby',
        function () {

            window.cwcCurrentPage = 1;


            updateProducts(
                getWrapper()
            );
        }
    );


    /* =====================================================
     * PAGINATION
     * ===================================================== */

    $(document).on(
        'click',
        '#pagination .page-numbers',
        function (e) {

            e.preventDefault();


            const $link = $(this);


            let page =
                parseInt(
                    $link.text(),
                    10
                );


            if ($link.hasClass('next')) {

                page =
                    (window.cwcCurrentPage || 1) +
                    1;
            }


            if ($link.hasClass('prev')) {

                page =
                    (window.cwcCurrentPage || 1) -
                    1;
            }


            if (
                Number.isNaN(page) ||
                page < 1
            ) {
                page = 1;
            }


            window.cwcCurrentPage =
                page;


            updateProducts(
                getWrapper()
            );
        }
    );


    /* =====================================================
     * INIT
     * ===================================================== */

    $(function () {



        initPriceSliders(
            document
        );
    });

})(jQuery);