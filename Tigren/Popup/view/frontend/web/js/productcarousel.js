define(
    ['jquery', 'owlcarousel'],
    function($) {
        'use strict';

        $.widget('tigren.productCarousel', {
            _create: function() {
                this._carousel();
            },

            _carousel: function() {
                this.element.owlCarousel({
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
            }
        });
        return $.tigren.productCarousel;
    }
);