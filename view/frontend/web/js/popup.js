define([
    "jquery",
    "Magento_Ui/js/modal/modal",
    "mage/url"
],function ($, modal, url)
{
    var ajax = url.build('popup/clearcart/clear');
    var checkout = url.build('checkout');
    return function(config)
    {
        var ajaxUrl = config + ajax;
        var checkoutUrl = config + checkout;
        // console.log(ajaxUrl);
        // console.log(checkoutUrl);
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            buttons: [{
                text: $.mage.__('Clear Shopping Cart'),
                class: 'mymodal1',
                click: function () {
                    var modal1 = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        title: "Clear Cart Confirmation",
                        buttons: [{
                            text: $.mage.__('Clear Cart'),
                            class: 'clear_cart_button',
                            click: function() {
                                // console.log(ajaxUrl);
                                $.ajax({
                                    showLoader: true,
                                    type: 'post',
                                    url: ajaxUrl,
                                    success: function(response) {
                                        console.log(response);
                                    },
                                    complete: function() {
                                        location.reload();
                                    }
                                })
                            }
                        },
                            {
                                text: $.mage.__('Close'),
                                click: function() {
                                    this.closeModal();
                                }
                            }]
                    };
                    var modal1_popup = modal(modal1, $('#clear_cart_confirm'));
                    $('#clear_cart_confirm').modal("openModal");
                }
            },
                {
                    text: $.mage.__('Use the existing item(s) and continues'),
                    class: 'mymodal2',
                    click: function () {
                        window.location = checkoutUrl;
                    }
                }]
        };

        var popup = modal(options, $('#content'));
        $(".tocart").on('click',function(){
            $("#content").modal("openModal");
        });
    }
});