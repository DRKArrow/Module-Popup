define(
    ['jquery', 'owlcarousel'],
    function($) {
        $(function () {
            $('.owl-carousel').owlCarousel({
                items: 5,
                autoplay: true,
                loop: true,
                autoplayTimeout: 6000,
                autoplayHoverPause: true,
                responsive:{
                    0:{
                        items:1
                    },
                    600:{
                        items:3
                    },
                    1000:{
                        items:5
                    }
                }
            });
        });
    }
);