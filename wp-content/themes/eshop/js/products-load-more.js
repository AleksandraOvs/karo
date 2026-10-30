document.addEventListener('DOMContentLoaded', () => {

    const productsList = document.querySelector('.js-products-list');
    const loader = document.querySelector('.products-loader');
    const observerTarget = document.querySelector('.products-load-more-trigger');

    if (!productsList || !observerTarget) {
        return;
    }

    let loading = false;

    const loadMoreProducts = async () => {

        if (
            loading ||
            productsList.dataset.hasMore !== '1'
        ) {
            return;
        }

        const wrapper = document.querySelector(
            '.sidebar-area-wrapper._filters'
        );

        if (
            !wrapper ||
            typeof window.cwcGetCurrentFilters !== 'function'
        ) {
            console.error('CWC: сборщик фильтров не найден');
            return;
        }

        loading = true;

        if (loader) {
            loader.hidden = false;
        }

        const currentPage = parseInt(
            productsList.dataset.page,
            10
        ) || 1;

        const nextPage = currentPage + 1;

        /*
         * Получаем актуальные фильтры.
         */
        const filters = window.cwcGetCurrentFilters(wrapper);

        /*
         * Передаём следующую страницу.
         */
        filters.page = nextPage;

        console.log('LOAD MORE →', filters);

        try {

            const response = await fetch(
                cwc_ajax_object.ajax_url,
                {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },

                    body: new URLSearchParams(
                        serializeFilters(filters)
                    )
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const result = await response.json();

            if (!result.success) {
                console.error('LOAD MORE ERROR:', result.data);
                return;
            }

            /*
             * Добавляем товары.
             */
            if (result.data.html) {
                productsList.insertAdjacentHTML(
                    'beforeend',
                    result.data.html
                );
            }

            /*
             * Обновляем состояние.
             */
            productsList.dataset.page = String(nextPage);

            productsList.dataset.hasMore =
                result.data.has_more ? '1' : '0';

            window.cwcCurrentPage = nextPage;

            console.log('LOAD MORE RESULT:', result.data);

        } catch (error) {

            console.error('Ошибка загрузки товаров:', error);

        } finally {

            loading = false;

            if (loader) {
                loader.hidden = true;
            }
        }
    };


    /*
     * Сериализация вложенных массивов.
     *
     * Нужна для numeric_pa_weight-gr и подобных
     * параметров с диапазонами.
     */
    function serializeFilters(data, prefix = '') {

        const result = [];

        Object.entries(data).forEach(([key, value]) => {

            const name = prefix
                ? `${prefix}[${key}]`
                : key;

            if (
                value !== null &&
                typeof value === 'object'
            ) {

                if (Array.isArray(value)) {

                    value.forEach((item, index) => {

                        if (
                            item !== null &&
                            typeof item === 'object'
                        ) {

                            result.push(
                                ...serializeFilters(
                                    item,
                                    `${name}[${index}]`
                                )
                            );

                        } else {

                            result.push([
                                `${name}[]`,
                                item
                            ]);
                        }
                    });

                } else {

                    result.push(
                        ...serializeFilters(value, name)
                    );
                }

            } else {

                result.push([name, value]);
            }
        });

        return result;
    }


    /*
     * Intersection Observer.
     */
    const observer = new IntersectionObserver(

        (entries) => {

            if (entries[0].isIntersecting) {
                loadMoreProducts();
            }

        },

        {
            rootMargin: '500px 0px',
            threshold: 0,
        }

    );

    observer.observe(observerTarget);

});