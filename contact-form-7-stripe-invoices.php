<?php

/**
 * Plugin Name: Contact Form 7 Stripe Invoices
 * Plugin URI:  https://wordpress.org/plugins/
 * Author:      John James Jacoby
 * Author URI:  https://profiles.wordpress.org/cgsmith/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Description: A simple way to collect money on invoices in WordPress
 * Version:     1.0.0
 * Text Domain: contact-form-7-stripe-invoices
 */

// Exit if accessed directly
defined('ABSPATH') || exit;


// plugin variable: cf7si


//  plugin functions
register_activation_hook(__FILE__, "cf7si_activate");
register_deactivation_hook(__FILE__, "cf7si_deactivate");
register_uninstall_hook(__FILE__, "cf7si_uninstall");

function cf7si_activate()
{

    // default options
    $cf7si_options = array(
        'currency' => '25',
        'language' => '3',
        'liveaccount' => '',
        'conveniencefee' => '3.5',
        'sandboxaccount' => '',
        'mode' => '2',
        'cancel' => '',
        'return' => '',
        'redirect' => '2',
        'pub_key_live' => '',
        'sec_key_live' => '',
        'pub_key_test' => '',
        'sec_key_test' => '',
    );

    add_option("cf7si_options", $cf7si_options);

}

function cf7si_deactivate()
{

    delete_option("cf7si_my_plugin_notice_shown");

}

function cf7si_uninstall()
{
}

// check to make sure contact form 7 is installed and active
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if (is_plugin_active('contact-form-7/wp-contact-form-7.php')) {

    // public includes
    include_once('includes/functions.php');
    include_once('includes/redirect_methods.php');
    include_once('includes/redirect.php');
    include_once('includes/enqueue.php');

    if (!class_exists('Stripe\Stripe')) {
        include_once('includes/stripe_library/init.php');
    }
    include_once('includes/process_stripe_payment.php');

    // admin includes
    if (is_admin()) {
        include_once('includes/admin/tabs_page.php');
        include_once('includes/admin/settings_page.php');
        include_once('includes/admin/menu_links.php');
    }


    // start session if not already started
    function cf7si_session()
    {
        if (!session_id()) {
            session_start();
        }
    }

    add_action('init', 'cf7si_session', 1);


} else {

    // give warning if contact form 7 is not active
    function cf7si_my_admin_notice()
    {
        ?>
        <div class="error">
            <p><?php _e('<b>Contact Form 7 - Stripe Invoices:</b> Contact Form 7 is not installed and / or active! Please install <a target="_blank" href="https://wordpress.org/plugins/contact-form-7/">Contact Form 7</a>.', 'cf7si'); ?></p>
        </div>
        <?php
    }

    add_action('admin_notices', 'cf7si_my_admin_notice');

}
