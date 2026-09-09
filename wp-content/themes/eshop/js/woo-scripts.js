document.addEventListener('DOMContentLoaded', () => {

    /* ===============================
      WOOCOMMERCE MESSAGE AUTO-HIDE
   =============================== */

    document.addEventListener('click', e => {
        document.querySelectorAll('.woocommerce-message').forEach(msg => {
            if (!msg.contains(e.target)) {
                msg.classList.add('fade-out');
                setTimeout(() => msg.remove(), 700);
            }
        });
    });

    /* ===============================
      SINGLE PRODUCT TABS
   =============================== */

    const tabs = document.querySelectorAll('.product-tabs__btn');
    const panes = document.querySelectorAll('.product-tabs__pane');

    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            // активная кнопка
            tabs.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // активный контент
            panes.forEach(pane => {
                pane.classList.remove('active');
                if (pane.dataset.tab === tab) {
                    pane.classList.add('active');
                }
            });
        });
    });


});

document.body.addEventListener('added_to_cart', function (e) {
    const button = e.detail?.button;

    if (!button) return;

    button.textContent = 'В корзине';
    button.classList.add('in-cart');
    button.disabled = true;
});

document.addEventListener('DOMContentLoaded', function () {

    document
        .querySelectorAll('.shop-header__catalog-select, select.orderby')
        .forEach(function (select) {

            if (select.nextElementSibling?.classList.contains('nice-select')) {
                return;
            }

            NiceSelect.bind(select);

            if (
                select.classList.contains('shop-header__catalog-select') &&
                !select.value
            ) {
                const niceSelect = select.nextElementSibling;
                const current = niceSelect?.querySelector('.current');

                if (current) {
                    current.textContent = 'Выбрать каталог';
                }
            }

        });

});