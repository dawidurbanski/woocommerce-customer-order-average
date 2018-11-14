<?php
/**
 * Plugin Name: WooCommerce Customer Order Average
 * Description: Add button to show user average order value in customer reports.
 * Version: 0.0.1
 * Author: Dawid Urbański (Webastik)
 * Author URI: https://dawidurbanski.com
 * Text Domain: wcoa
 */

add_action('admin_enqueue_scripts', function () {
	wp_enqueue_style('wcoa-admin-styles', esc_url(plugin_dir_url(__FILE__) . 'assets/scripts/admin.css'));
	wp_enqueue_script('wcoa-admin-scripts', esc_url(plugin_dir_url(__FILE__) . 'assets/scripts/admin.js'), ['jquery']);

	wp_localize_script('wcoa-admin-scripts', 'l10n', [
		'column_title' => esc_html__('Order average', 'wcoa'),
		'column_cell_title' => esc_html__('Average per order', 'wcoa'),
	]);
});

add_action('woocommerce_admin_user_actions_end', function ($user) {
	$average = 0;
	$orders_number = wc_get_customer_order_count($user->ID);

	if ($orders_number > 0) {
		$average = wc_get_customer_total_spent($user->ID) / $orders_number;
	}

	echo '<div class="hidden orders-average">';
	echo wc_price($average);
	echo '</div>';
});
