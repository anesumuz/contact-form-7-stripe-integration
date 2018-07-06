<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


// add paypal menu under contact form 7 menu
add_action('admin_menu', 'cf7si_admin_menu', 20);
function cf7si_admin_menu()
{
    add_submenu_page('wpcf7', __('Stripe Settings', 'contact-form-7'), __('Stripe Settings', 'contact-form-7'), 'wpcf7_edit_contact_forms', 'cf7si_admin_table', 'cf7si_admin_table');
}


// plugin page links
add_filter('plugin_action_links', 'cf7si_plugin_settings_link', 10, 2);
function cf7si_plugin_settings_link($links, $file)
{
    if ($file == 'contact-form-7-stripe-invoices/contact-form-7-stripe-invoices.php') {
        $settings_link = '<a href="admin.php?page=cf7si_admin_table">' . __('Settings', 'PTP_LOC') . '</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}