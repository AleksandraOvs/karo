/* ===============================
      SINGLE PRODUCT FAQ
   =============================== */

document.addEventListener('click', function (e) {

    const button = e.target.closest('.faq-items__question');

    if (!button) {
        return;
    }

    const item = button.closest('.faq-items__item');
    const answer = item.querySelector('.faq-items__answer');

    const isOpen = button.getAttribute('aria-expanded') === 'true';

    button.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    item.classList.toggle('active', !isOpen);

    if (isOpen) {
        answer.style.maxHeight = null;
    } else {
        answer.style.maxHeight = answer.scrollHeight + 'px';
    }
});