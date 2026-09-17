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

    const input = popup.querySelector('.share-popup__input');
    const copyButton = popup.querySelector('.share-popup__copy');
    const copyButtonText = copyButton.querySelector('span');
    const telegramLink = popup.querySelector('.--telegram');
    const emailLink = popup.querySelector('.--email');

    input.value = productUrl;

    telegramLink.href =
        'https://t.me/share/url?url=' +
        encodeURIComponent(productUrl);

    emailLink.href =
        'mailto:?subject=' +
        encodeURIComponent('Посмотрите этот товар') +
        '&body=' +
        encodeURIComponent(productUrl);

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


document.addEventListener('click', function (e) {

    const copyButton = e.target.closest('.share-popup__copy');

    if (!copyButton) {
        return;
    }

    const popup = copyButton.closest('.share-popup');
    const input = popup.querySelector('.share-popup__input');
    const copyButtonText = copyButton.querySelector('span');

    function copied() {

        copyButton.classList.add('is-copied');
        copyButtonText.textContent = 'Скопировано';

    }

    function copyFallback() {

        input.focus();
        input.select();
        input.setSelectionRange(0, input.value.length);

        try {
            const success = document.execCommand('copy');

            if (success) {
                copied();
            }
        } catch (error) {
            console.error('Не удалось скопировать ссылку:', error);
        }

    }

    // Современный Clipboard API
    if (navigator.clipboard && window.isSecureContext) {

        navigator.clipboard.writeText(input.value)
            .then(copied)
            .catch(copyFallback);

    } else {

        // HTTP / старые браузеры
        copyFallback();

    }

});


document.addEventListener('click', function (e) {

    const button = e.target.closest('.share-popup__native');

    if (!button) {
        return;
    }

    const popup = button.closest('.share-popup');
    const input = popup.querySelector('.share-popup__input');

    if (!navigator.share) {
        return;
    }

    navigator.share({
        title: document.title,
        url: input.value
    });

});

