<?php 
/**
 * rv_booking_system plugin 
 *
 * @package rv_booking_system
 * 
 * @link 
 * @see
 */

 function ikrrv_booking_system_add_all_script (){

 };

 
function ikrrv_booking_system_add_all_style()
{
	// load primary css 
	wp_enqueue_style('rv_booking_system_bootstrap', plugin_dir_url(__FILE__) . '/public/css/bootstrap.min.css', array(), RVBS_PLUGIN_VER, 'all');
	wp_enqueue_style(
		'ecommarcetheam-icon',
		'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
		array(),
		RVBS_PLUGIN_VER,
		'all'
	);
	// enque bootstrap css 
}

add_action('wp_enqueue_scripts', 'ecommarcetheam_add_all_style');