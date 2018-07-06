<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


// returns the form id of the forms that have paypal enabled - used for redirect method 1 and method 2
function cf7si_forms_enabled()
{

    // array that will contain which forms paypal is enabled on
    $enabled = array();

    $args = array(
        'posts_per_page' => 999,
        'post_type' => 'wpcf7_contact_form',
        'post_status' => 'publish',
    );
    $posts_array = get_posts($args);


    // loop through them and find out which ones have paypal enabled
    foreach ($posts_array as $post) {

        $post_id = $post->ID;

        // paypal
        $enable = get_post_meta($post_id, "_cf7si_enable", true);

        if ($enable == "1") {
            $enabled[] = $post_id . '|paypal';
        }

        // stripe
        $enable_stripe = get_post_meta($post_id, "_cf7si_enable_stripe", true);

        if ($enable_stripe == "1") {
            $enabled[] = $post_id . '|stripe';
        }

    }

    return json_encode($enabled);

}


// hook into contact form 7 - after send - for redirect method 1 and 2
add_action('template_redirect', 'cf7si_redirect_method');
function cf7si_redirect_method()
{

    if (isset($_GET['cf7si_redirect'])) {

        // get the id from the cf7si_before_send_mail function theme redirect
        $post_id = $_GET['cf7si_redirect'];

        cf7si_redirect($post_id);
        exit;

    }
}


// for redirect method 2 - this must be loaded for redirect method 2 regardless of if the form has paypal enabled
$options = get_option('cf7si_options');

if (isset($options['redirect'])) {
    if ($options['redirect'] == "2" || $options['redirect'] == '') {

        if (!defined('WPCF7_LOAD_JS')) {
            define('WPCF7_LOAD_JS', false);
        }

    }
}


// hook into contact form 7 - before send for redirect method 1 and method 2
add_action('wpcf7_before_send_mail', 'cf7si_before_send_mail');
function cf7si_before_send_mail()
{

    $wpcf7 = WPCF7_ContactForm::get_current();

    // need to save submission for later and the variables get lost in the cf7 javascript redirect
    $submission_orig = WPCF7_Submission::get_instance();

    if ($submission_orig) {
        // get form post id
        $posted_data = $submission_orig->get_posted_data();

        $options = get_option('cf7si_options');

        $post_id = $posted_data['_wpcf7'];
        $gateway = strtolower(get_post_meta($post_id, "_cf7si_gateway", true));
        $amount_total = $_POST['price']; //get_post_meta($post_id, "_cf7si_price", true);
        $convenience_fee = $options['conveniencefee']; // Percentage of convenience fee
        $convenience_fee = number_format(($amount_total * ($convenience_fee/100)),2);

        $enable = get_post_meta($post_id, "_cf7si_enable", true);
        $enable_stripe = get_post_meta($post_id, "_cf7si_enable_stripe", true);

        $stripe_email = strtolower(get_post_meta($post_id, "_cf7si_stripe_email", true));

        if (!empty($stripe_email)) {
            $stripe_email = $posted_data[$stripe_email];
        } else {
            $stripe_email = '';
        }


        $gateway_orig = $gateway;

        if ($enable == '1') {
            $gateway = 'paypal';
        }

        if ($enable_stripe == '1') {
            $gateway = 'stripe';
        }

        if ($enable == '1' && $enable_stripe == '1') {
            $gateway = $posted_data[$gateway_orig];
        }


        if (!isset($options['default_symbol'])) {
            $options['default_symbol'] = '$';
        }


        if (isset($options['mode_stripe'])) {
            if ($options['mode_stripe'] == "1") {
                $tags['stripe_state'] = "test";
            } else {
                $tags['stripe_state'] = "live";
            }
        } else {
            $tags['stripe_state'] = "live";
        }


        $_SESSION['gateway'] = $gateway;
        $_SESSION['amount_total'] = $amount_total;
        $_SESSION['convenience_fee'] = $convenience_fee;
        $_SESSION['default_symbol'] = $options['default_symbol'];
        $_SESSION['stripe_state'] = $tags['stripe_state'];
        $_SESSION['stripe_email'] = $stripe_email;
        $_SESSION['stripe_return'] = $options['stripe_return'];
    }
}


// after submit post for js - used for method 1 and 2 for paypal and stripe
add_action('wp_ajax_cf7si_get_form_post', 'cf7si_get_form_post_callback');
add_action('wp_ajax_nopriv_cf7si_get_form_post', 'cf7si_get_form_post_callback');
function cf7si_get_form_post_callback()
{

    $response = array(
        'gateway' => $_SESSION['gateway'],
        'amount_total' => $_SESSION['amount_total'],
        'convenience_fee' => $_SESSION['convenience_fee'],
        'default_symbol' => $_SESSION['default_symbol'],
        'email' => $_SESSION['stripe_email'],
        'stripe_return' => $_SESSION['stripe_return'],
    );

    echo json_encode($response);

    wp_die();
}


// return stripe payment form
add_action('wp_ajax_cf7si_get_form_stripe', 'cf7si_get_form_stripe_callback');
add_action('wp_ajax_nopriv_cf7si_get_form_stripe', 'cf7si_get_form_stripe_callback');
function cf7si_get_form_stripe_callback()
{

    // generate nonce
    $salt = wp_salt();
    $nonce = wp_create_nonce('cf7si_' . $salt);

    $options = get_option('cf7si_options');


    $result = '';

    if ((empty($options['pub_key_test']) && $options['mode_stripe'] == '1') || (empty($options['pub_key_live']) && $options['mode_stripe'] == '2')) {
        $result .= 'Stripe Error. Admin: Please enter your Stripe Keys on the settings page.';

        $response = array(
            'html' => $result,
        );

        echo json_encode($response);
        wp_die();
    }

    // show stripe test mode div
    if ($_SESSION['stripe_state'] == 'test') {
        $result .= "<a href='https://stripe.com/docs/testing#cards' target='_blank' class='cf7si-test-mode'>test mode</a>";
    }

    $result .= "<div class='cf7si_stripe'>";
    $result .= "<form method='post' id='cf7si-payment-form'>";
    $result .= "<div class='cf7si_body'>";
    $result .= "<div class='cf7si_row'>";
    $result .= "<div class='cf7si_details_input'>";
    $result .= "<label for='cf7si_stripe_credit_card_number'>";
    $result .= __($options['card_number'], 'cf7si_stripe');
    $result .= "</label>";
    $result .= "<div id='cf7si_stripe_credit_card_number'></div>";
    $result .= "</div>";

    $result .= "<div class='cf7si_details_input'>";
    $result .= "<label for='cf7si_stripe_credit_card_csv'>";
    $result .= __($options['card_code'], 'cf7si_stripe');
    $result .= "</label>";
    $result .= "<div id='cf7si_stripe_credit_card_csv'></div>";
    $result .= "</div>";
    $result .= "</div>";
    $result .= "<div class='cf7si_row'>";
    $result .= "<div class='cf7si_details_input'>";
    $result .= "<label for='cf7si_stripe_credit_card_expiration'>";
    $result .= __($options['expiration'], 'cf7si_stripe');
    $result .= "</label>";
    $result .= "<div id='cf7si_stripe_credit_card_expiration''></div>";
    $result .= "</div>";

    $result .= "<div class='cf7si_details_input'>";
    $result .= "<label for='cf7si_stripe_credit_card_zip'>";
    $result .= __($options['zip'], 'cf7si_stripe');
    $result .= "</label>";
    $result .= "<div id='cf7si_stripe_credit_card_zip'></div>";
    $result .= "</div>";
    $result .= "</div>";
    $result .= "<div id='card-errors' role='alert'></div>";
    $buttonvalue = $options['pay'] . " " . $options['default_symbol'] . $_SESSION['amount_total'];
    if ($options['conveniencefee'] > 0) {
        $buttonvalue .= ' (' . $options['default_symbol'] . $_SESSION['convenience_fee'] . ' convenience fee)';
    }
    $result .= "<br />&nbsp;<input id='stripe-submit' value='".$buttonvalue."' type='submit'>";
    $result .= "<div>";
    $result .= "<input type='hidden' id='cf7si_stripe_nonce' value='$nonce'>";
    $result .= "</form>";
    $result .= "<div>";


    $response = array(
        'html' => $result,
    );

    echo json_encode($response);
    wp_die();
}
