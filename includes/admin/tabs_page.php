<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


// hook into contact form 7 form
function cf7si_editor_panels ( $panels ) {

	$new_page = array(
		'PayPal' => array(
			'title' => __( 'Stripe', 'contact-form-7-paypal' ),
			'callback' => 'cf7si_admin_after_additional_settings'
		)
	);

	$panels = array_merge($panels, $new_page);

	return $panels;

}
add_filter( 'wpcf7_editor_panels', 'cf7si_editor_panels' );


function cf7si_admin_after_additional_settings( $cf7 ) {

	$post_id = sanitize_text_field($_GET['post']);

	$enable = 					get_post_meta($post_id, "_cf7si_enable", true);
	$enable_stripe = 			get_post_meta($post_id, "_cf7si_enable_stripe", true);
	$name = 					get_post_meta($post_id, "_cf7si_name", true);
	$price = 					get_post_meta($post_id, "_cf7si_price", true);
	$id = 						get_post_meta($post_id, "_cf7si_id", true);
	$gateway = 					get_post_meta($post_id, "_cf7si_gateway", true);
	$stripe_email = 			get_post_meta($post_id, "_cf7si_stripe_email", true);

	if ($enable == "1") { $checked = "CHECKED"; } else { $checked = ""; }
	if ($enable_stripe == "1") { $checked_stripe = "CHECKED"; } else { $checked_stripe = ""; }

	$admin_table_output = "";
	$admin_table_output .= "<h2>Stripe Settings</h2>";

	$admin_table_output .= "<div class='mail-field'></div>";
	
	$admin_table_output .= "<table><tr>";
	

	$admin_table_output .= "<td><label>Enable Stripe on this form</label></td>";
	$admin_table_output .= "<td><input name='enable_stripe' value='1' type='checkbox' $checked_stripe></td></tr>";

	$admin_table_output .= "<tr><td>Email Code: </td>";
	$admin_table_output .= "<td><input type='text' name='stripe_email' value='$stripe_email'> </td><td> (Optional. Pass email to Stripe. Example: text-105)</td></tr><tr><td colspan='3'><br />";


	$admin_table_output .= "<hr></td></tr>";

	$admin_table_output .= "<tr><td>Item Description: </td>";
	$admin_table_output .= "<td><input type='text' name='name' value='$name'> </td><td> (Optional)</td></tr>";

	$admin_table_output .= "<tr><td>Item ID / SKU: </td>";
	$admin_table_output .= "<td><input type='text' name='id' value='$id'> </td><td> (Optional)</td></tr>";
	
	$admin_table_output .= "<input type='hidden' name='post' value='$post_id'>";

	$admin_table_output .= "</td></tr></table>";

	echo $admin_table_output;

}






// hook into contact form 7 admin form save
add_action('wpcf7_after_save', 'cf7si_save_contact_form');

function cf7si_save_contact_form( $cf7 ) {
		
		$post_id = sanitize_text_field($_POST['post']);
		
		if (!empty($_POST['enable'])) {
			$enable = sanitize_text_field($_POST['enable']);
			update_post_meta($post_id, "_cf7si_enable", $enable);
		} else {
			update_post_meta($post_id, "_cf7si_enable", 0);
		}
		
		if (!empty($_POST['enable_stripe'])) {
			$enable_stripe = sanitize_text_field($_POST['enable_stripe']);
			update_post_meta($post_id, "_cf7si_enable_stripe", $enable_stripe);
		} else {
			update_post_meta($post_id, "_cf7si_enable_stripe", 0);
		}
		
		$name = sanitize_text_field($_POST['name']);
		update_post_meta($post_id, "_cf7si_name", $name);
		
		$price = sanitize_text_field($_POST['price']);
		$price = cf7si_format_currency($price);
		update_post_meta($post_id, "_cf7si_price", $price);
		
		$id = sanitize_text_field($_POST['id']);
		update_post_meta($post_id, "_cf7si_id", $id);
		
		$gateway = sanitize_text_field($_POST['gateway']);
		update_post_meta($post_id, "_cf7si_gateway", $gateway);
		
		$stripe_email = sanitize_text_field($_POST['stripe_email']);
		update_post_meta($post_id, "_cf7si_stripe_email", $stripe_email);

}
