document.querySelectorAll('.links-slider').forEach((slider) => {

    const isMobile = window.matchMedia('(max-width: 767px)').matches;

    new Swiper(slider, {

        slidesPerView: 3,
        spaceBetween: 24,

        loop: true,

        speed: 30000,

        autoplay: isMobile
            ? false
            : {
                delay: 0,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },

        freeMode: {
            enabled: true,
            momentum: false,
        },

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