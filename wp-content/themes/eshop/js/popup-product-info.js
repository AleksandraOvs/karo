document.addEventListener('click', function (e) {

    const infoButton = e.target.closest('.product-card__info.--info');

    if (!infoButton) {
        return;
    }

    const productId = infoButton.dataset.product_id;

    if (!productId) {
        return;
    }

    const popup = document.querySelector('#product-info-popup');
    const popupBody = document.querySelector('#product-info-popup-body');

    if (!popup || !popupBody) {
        return;
    }

    // Показываем loader
    popupBody.innerHTML = `
       <div class="product-info-popup__loader" aria-label="Загрузка">

    <svg
        class="product-info-popup__diamond"
        width="27"
        height="24"
        viewBox="0 0 27 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
    >
        <path
            d="M0.338867 6.87074L13.4024 23.2001L26.4659 6.87074L21.567 0.338989H5.25237L0.338867 6.87074ZM26.4659 6.87074H0.338867M13.4024 23.2001L8.50355 6.87074M13.4024 23.2001L18.3012 6.87074"
            stroke="currentColor"
            stroke-width="0.677778"
            stroke-linecap="round"
            stroke-linejoin="round"
        />

        <path
            d="M5.25195 0.338989L8.50313 6.87074L13.4019 0.338989L18.3007 6.87074L21.5666 0.338989"
            stroke="currentColor"
            stroke-width="0.677778"
            stroke-linecap="round"
            stroke-linejoin="round"
        />

        <!-- Блик -->
        <path
            class="product-info-popup__diamond-shine"
            d="M5.25 0.34L8.5 6.87L13.4 23.2L18.3 6.87L21.57 0.34"
            stroke="white"
            stroke-width="1"
            stroke-linecap="round"
            stroke-linejoin="round"
        />

    </svg>

</div>
    `;

    // Сразу открываем Fancybox
    Fancybox.show([
        {
            src: '#product-info-popup',
            type: 'inline'
        }
    ]);

    const formData = new FormData();

    formData.append('action', 'get_product_info_popup');
    formData.append('product_id', productId);

    fetch(productInfoData.ajax_url, {
        method: 'POST',
        body: formData
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {

            if (!data.success) {

                popupBody.innerHTML = `
                    <div class="product-info-popup__error">
                        Не удалось загрузить информацию о товаре.
                    </div>
                `;

                return;
            }

            popupBody.innerHTML = data.data.html;

        })
        .catch(function (error) {

            console.error(error);

            popupBody.innerHTML = `
                <div class="product-info-popup__error">
                    Произошла ошибка. Попробуйте ещё раз.
                </div>
            `;

        });

});