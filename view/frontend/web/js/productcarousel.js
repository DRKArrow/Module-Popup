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
                    items: 4,
                    autoplay: true,
                    autoplayTimeout: 6000,
                    autoplayHoverPause: true,
                    responsive:{
                        0:{
                            items:1
                        },
                        500: {
                            items:2
                        },
                        1000:{
                            items:4
                        },
                    }
                });
            }
        });
        return $.tigren.productCarousel;
    }
);