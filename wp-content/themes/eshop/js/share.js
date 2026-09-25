document.addEventListener('click', function (e) {

    const shareButton = e.target.closest('.product-card__info.--share');

    if (!shareButton) {
        return;
    }

    const productUrl = shareButton.dataset.product_url;

    if (!productUrl) {
        return;
    }

    const popup = document.querySelector('#share-popup');

    if (!popup) {
        return;
    }

    const input = popup.querySelector('.share-popup__input');
    const copyButton = popup.querySelector('.share-popup__copy');
    const copyButtonText = copyButton.querySelector('span');

    const telegramLink = popup.querySelector('.--telegram');
    const emailLink = popup.querySelector('.--email');
    const maxLink = popup.querySelector('.--max');
    const whatsappLink = popup.querySelector('.--whatsapp');

    input.value = productUrl;

    /*
     * Telegram
     */
    if (telegramLink) {
        telegramLink.href =
            'https://t.me/share/url?url=' +
            encodeURIComponent(productUrl);
    }

    /*
     * E-mail
     */
    if (emailLink) {
        emailLink.href =
            'mailto:?subject=' +
            encodeURIComponent('Посмотрите этот товар') +
            '&body=' +
            encodeURIComponent(productUrl);
    }

    /*
     * MAX
     */
    if (maxLink) {
        maxLink.href =
            'https://max.ru/share?' +
            'text=' +
            encodeURIComponent(productUrl);
    }

    /*
     * WhatsApp
     */
    if (whatsappLink) {
        whatsappLink.href =
            'https://wa.me/?text=' +
            encodeURIComponent(productUrl);
    }

    // Сбрасываем состояние
    copyButton.classList.remove('is-copied');
    copyButtonText.textContent = 'Скопировать';

    Fancybox.show([
        {
            src: '#share-popup',
            type: 'inline'
        }
    ]);

});