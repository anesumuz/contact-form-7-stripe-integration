jQuery(document).ready(function ($) {

    var formid;
    var formid_long;
    var amount_total;
    var convenience_fee;
    var default_symbol;
    var email;
    var stripe_return;

    // for redirect method 1
    document.addEventListener('wpcf7mailsent', function (event) {
        var id_long = event.detail.id;
        var id = event.detail.contactFormId;

        var formid = id_long;
        var formid = id;

        var forms = ajax_object_cf7si.forms;
        var result_paypal = forms.indexOf(id + '|paypal');
        var result_stripe = forms.indexOf(id + '|stripe');
        var path = ajax_object_cf7si.path + id;


        var gateway;

        var data = {
            'action': 'cf7si_get_form_post',
        };

        jQuery.ajax({
            type: "GET",
            data: data,
            dataType: "json",
            async: false,
            url: ajax_object_cf7si.ajax_url,
            xhrFields: {
                withCredentials: true
            },
            success: function (response) {
                gateway = response.gateway;
                amount_total = response.amount_total;
                convenience_fee = response.convenience_fee;
                default_symbol = response.default_symbol;
                email = response.email;
                stripe_return = response.stripe_return;
            }
        });


        // gateway chooser
        if (gateway != null) {
            // paypal
            if (result_paypal > -1 && gateway == 'paypal') {
                window.location.href = path;
            }

            // stripe
            if (result_stripe > -1 && gateway == 'stripe') {

                var data = {
                    'action': 'cf7si_get_form_stripe',
                };

                jQuery.ajax({
                    type: "GET",
                    data: data,
                    dataType: "json",
                    async: false,
                    url: ajax_object_cf7si.ajax_url,
                    xhrFields: {
                        withCredentials: true
                    },
                    success: function (response) {

                        jQuery('#' + id_long).html(response.html);

                    }
                });

            }
        } else {
            // no gateway chooser
            if (result_paypal > -1) {
                window.location.href = path;
            }

            // stripe
            if (result_stripe > -1) {

                var data = {
                    'action': 'cf7si_get_form_stripe',
                };

                jQuery.ajax({
                    type: "GET",
                    data: data,
                    dataType: "json",
                    async: false,
                    url: ajax_object_cf7si.ajax_url,
                    xhrFields: {
                        withCredentials: true
                    },
                    success: function (response) {

                        jQuery('#' + id_long).html(response.html);

                    }
                });

            }
        }

    }, false);


    // for redirect method 2 - with WPCF7_LOAD_JS off
    if (jQuery('.wpcf7-mail-sent-ok')[0]) {

        var id_long = jQuery('.wpcf7-mail-sent-ok').closest('.wpcf7').attr("id");
        var id = id_long.split('f').pop().split('-').shift();

        var formid = id_long;
        var formid = id;

        var forms = ajax_object_cf7si.forms;
        var result_paypal = forms.indexOf(id + '|paypal');
        var result_stripe = forms.indexOf(id + '|stripe');
        var path = ajax_object_cf7si.path + id;

        var gateway;

        var data = {
            'action': 'cf7si_get_form_post',
        };

        jQuery.ajax({
            type: "GET",
            data: data,
            dataType: "json",
            async: false,
            url: ajax_object_cf7si.ajax_url,
            xhrFields: {
                withCredentials: true
            },
            success: function (response) {
                gateway = response.gateway;
                amount_total = response.amount_total;
                convenience_fee = response.convenience_fee;
                default_symbol = response.default_symbol;
                email = response.email;
                stripe_return = response.stripe_return;
            }
        });


        // gateway chooser
        if (gateway != null) {
            // paypal
            if (result_paypal > -1 && gateway == 'paypal') {
                window.location.href = path;
            }

            // stripe
            if (result_stripe > -1 && gateway == 'stripe') {

                var data = {
                    'action': 'cf7si_get_form_stripe',
                };

                jQuery.ajax({
                    type: "GET",
                    data: data,
                    dataType: "json",
                    async: false,
                    url: ajax_object_cf7si.ajax_url,
                    xhrFields: {
                        withCredentials: true
                    },
                    success: function (response) {

                        jQuery('#' + id_long).html(response.html);

                    }
                });

            }
        } else {
            // no gateway chooser
            if (result_paypal > -1) {
                window.location.href = path;
            }

            // stripe
            if (result_stripe > -1) {

                var data = {
                    'action': 'cf7si_get_form_stripe',
                };

                jQuery.ajax({
                    type: "GET",
                    data: data,
                    dataType: "json",
                    async: false,
                    url: ajax_object_cf7si.ajax_url,
                    xhrFields: {
                        withCredentials: true
                    },
                    success: function (response) {

                        jQuery('#' + id_long).html(response.html);

                    }
                });

            }
        }


    }
    ;


    // stripe payment form

    // method 1
    jQuery(document).ajaxComplete(function () {
        cf7si_load_stripe();
    });

    // method 2
    if (jQuery('.cf7si_stripe').length) {
        cf7si_load_stripe();
    }
    ;


    function cf7si_load_stripe() {
        if (jQuery('.cf7si_stripe').length) {
            var stripe = Stripe(ajax_object_cf7si.stripe_key);

            var elements = stripe.elements();

            var elementClasses = {
                base: 'cf7si_details_input',
                focus: 'focus',
                empty: 'empty',
                invalid: 'invalid',
            };

            var cardNumber = elements.create('cardNumber', {
                classes: elementClasses,
                placeholder: "\u2022\u2022\u2022\u2022 \u2022\u2022\u2022\u2022 \u2022\u2022\u2022\u2022 \u2022\u2022\u2022\u2022",
            });
            cardNumber.mount('#cf7si_stripe_credit_card_number');

            var cardExpiry = elements.create('cardExpiry', {
                classes: elementClasses,
                placeholder: "\u2022\u2022 / \u2022\u2022",
            });
            cardExpiry.mount('#cf7si_stripe_credit_card_expiration');

            var cardCvc = elements.create('cardCvc', {
                classes: elementClasses,
                placeholder: "\u2022\u2022\u2022",
            });
            cardCvc.mount('#cf7si_stripe_credit_card_csv');

            var cardPostalCode = elements.create('postalCode', {
                classes: elementClasses,
                placeholder: "\u2022\u2022\u2022\u2022\u2022",
            });
            cardPostalCode.mount('#cf7si_stripe_credit_card_zip');


            // Handle real-time validation errors from the card Element.
            cardNumber.addEventListener('change', function (event) {

                var displayError = document.getElementById('card-errors');

                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }

            });

            cardExpiry.addEventListener('change', function (event) {

                var displayError = document.getElementById('card-errors');

                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }

            });

            cardCvc.addEventListener('change', function (event) {

                var displayError = document.getElementById('card-errors');

                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }

            });

            cardPostalCode.addEventListener('change', function (event) {

                var displayError = document.getElementById('card-errors');

                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }

            });


            // Handle form submission

            var form = document.getElementById('cf7si-payment-form');

            form.addEventListener('submit', function (event) {


                // id get lost using method 1, so we need to get it again
                var id_long = jQuery('.cf7si_stripe').closest('.wpcf7').attr("id");
                var formid = id_long.split('f').pop().split('-').shift();


                // disable submit button
                jQuery('#stripe-submit').attr("disabled", "disabled");
                jQuery('#stripe-submit').val(ajax_object_cf7si.processing);

                event.preventDefault();

                stripe.createToken(cardNumber).then(function (result) {
                    if (result.error) {


                        // Inform the user if there was an error
                        var errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;

                        // undisable submit button
                        jQuery('#stripe-submit').removeAttr("disabled");
                        var button_text = ajax_object_cf7si.pay + " " + default_symbol + amount_total;
                        if (convenience_fee > 0) {
                            button_text = button_text + " (" + default_symbol + convenience_fee + " convenience fee)";
                        }
                        jQuery('#stripe-submit').val(button_text);
                    } else {
                        // Send the token to your server

                        var data = {
                            'action': 'cf7si_stripe_charge',
                            'token': result.token,
                            'nonce': jQuery('#cf7si_stripe_nonce').val(),
                            'id': formid,
                            'email': email,
                            'amount_total': amount_total,
                            'convenience_fee': convenience_fee,
                        };

                        jQuery.ajax({
                            type: "POST",
                            data: data,
                            dataType: "json",
                            url: ajax_object_cf7si.ajax_url,
                            xhrFields: {
                                withCredentials: true
                            },
                            success: function (result) {

                                if (result.response == 'completed') {

                                    if (stripe_return) {
                                        window.location.href = stripe_return;
                                    } else {
                                        jQuery('#' + id_long).html(result.html_success);
                                    }

                                } else {
                                    // undisable submit button
                                    jQuery('#card-errors').html(ajax_object_cf7si.failed);
                                    jQuery('#stripe-submit').removeAttr("disabled");
                                    var button_text = ajax_object_cf7si.pay + " " + default_symbol + amount_total;
                                    if (convenience_fee > 0) {
                                        button_text = button_text + " (" + default_symbol + convenience_fee + " convenience fee)";
                                    }
                                    jQuery('#stripe-submit').val(button_text);
                                }
                             }
                        });
                    }
                });

            });

        }
        ;
    };


});
