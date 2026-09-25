document.addEventListener("DOMContentLoaded", function () {

    // Открытие попапов по data-popup
    document.querySelectorAll("[data-popup]").forEach(button => {

        button.addEventListener("click", function (e) {

            e.preventDefault();

            const popupId = this.dataset.popup;

            if (!popupId) {
                return;
            }

            const targetSelector = `#${popupId}`;

            if (typeof Fancybox === "undefined") {
                console.error("Fancybox не загружен");
                return;
            }

            const target = document.querySelector(targetSelector);

            if (!target) {
                console.error("Попап не найден:", targetSelector);
                return;
            }


            // Данные товара для попапа "О товаре"
            if (popupId === "popup-about-product") {

                const productName = this.dataset.productName || "";
                const productSku = this.dataset.productSku || "—";

                const title = target.querySelector(".product-question-title");
                const nameField = target.querySelector('[name="product-name"]');
                const skuField = target.querySelector('[name="product-sku"]');

                if (title) {
                    title.textContent =
                        `Задайте свой вопрос о товаре  ${productName} (${productSku})`;
                }

                if (nameField) {
                    nameField.value = productName;
                }

                if (skuField) {
                    skuField.value = productSku;
                }
            }


            Fancybox.show([
                {
                    src: targetSelector,
                    type: "inline"
                }
            ]);
        });
    });


    // CF7 после успешной отправки
    document.addEventListener("wpcf7mailsent", function (event) {

        const mainForm = document.querySelector("#main-form");

        if (mainForm && mainForm.contains(event.target)) {

            event.target.style.display = "none";

            if (mainForm.querySelector(".main-form-success")) {
                return;
            }

            const successMessage = document.createElement("div");

            successMessage.className = "main-form-success";

            successMessage.innerHTML = `
                <h3>Спасибо, форма отправлена.</h3>
                <p>Мы свяжемся с вами в ближайшее время.</p>
            `;

            mainForm.appendChild(successMessage);
        }

    }, false);

});