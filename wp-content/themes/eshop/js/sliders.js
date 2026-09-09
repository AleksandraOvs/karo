document.querySelectorAll('.links-slider').forEach((slider) => {

    new Swiper(slider, {

        slidesPerView: 3,
        spaceBetween: 24,

        pagination: {
            el: slider.querySelector('.slider-pagination'),
            clickable: true,
        },

        breakpoints: {
            0: {
                slidesPerView: 1,
            },

            768: {
                slidesPerView: 2,
            },

            1024: {
                slidesPerView: 3,
            },
        },

    });

});