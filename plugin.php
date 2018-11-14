<?php
/**
 * Plugin Name: WooCommerce Customer Order Average
 * Description: Add button to show user average order value in customer reports.
 * Version: 0.0.1
 * Author: Dawid Urbański (Webastik)
 * Author URI: https://dawidurbanski.com
 * Text Domain: wcoa
 */

/**
 * Include scripts and styles
 */
add_action('admin_enqueue_scripts', function () {
	wp_enqueue_style('wcoa-admin-styles', esc_url(plugin_dir_url(__FILE__) . 'assets/styles/admin.css'));
	wp_enqueue_script('wcoa-admin-scripts', esc_url(plugin_dir_url(__FILE__) . 'assets/scripts/admin.js'), ['jquery']);

	wp_localize_script('wcoa-admin-scripts', 'ajax', [
		'url' => admin_url('admin-ajax.php'),
	]);
});

/**
 * Add notice with update user meta link
 */
add_action('admin_notices', function () {
	if (! isset($_GET['page']) || 'wc-reports' != $_GET['page']) {
		return;
	}

	if (! isset($_GET['report']) || 'customer_list' != $_GET['report']) {
		return;
	}

	if (! empty(get_option('order_average_updated'))) {
		return;
	}

	$users = get_users();
	$users_ids = [];
	$users_count = count_users();

	foreach ($users as $user) {
		array_push($users_ids, $user->ID);
	}

	$class = 'notice notice-error';
	$message = __('Users has no order average information applied to them. Sorting per average order will not work until updated.', 'wcoa');
	$ajax_nonce = wp_create_nonce('user-order-average-nonce');
	$ajax_counter = '<a href="#" class="update-users-average-order-meta" data-users="' . json_encode($users_ids) . '" data-ajax-nonce="' . esc_attr($ajax_nonce) . '">Update users now</a> (click and wait a minute)';
	$ajax_counter .= '<span class="users-progress hidden">(<span class="current">0</span>/<span class="total">' . $users_count['total_users'] . '</span>)</span>';

	printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) . $ajax_counter);
});

/**
 * Replace customers report class path tith our custom one
 */
add_filter('wc_admin_reports_path', function ($path, $name, $class) {
	if ('customer-list' != $name) {
		return $path;
	}

	return plugin_dir_path(__FILE__) . 'includes/class-wc-report-customer-list.php';
}, 10, 3);

/**
 * Bulk update users meta with order average info using wp ajax
 */
add_action('wp_ajax_add-user-order-average', function () {
	check_ajax_referer('user-order-average-nonce');

	$user_id = $_REQUEST['user_id'];

	$updated = update_user_meta($user_id, 'order_average', (int) ceil(wc_get_customer_total_spent($user_id) / wc_get_customer_order_count($user_id)));

	wp_send_json_success([
		'id' => $user_id,
		'updated' => $updated,
	]);
});

/**
 * Update flag to check if admin notice should be displaed later
 */
add_action('wp_ajax_users-updated-flag', function () {
	check_ajax_referer('user-order-average-nonce');

	update_option('order_average_updated', true);
	wp_send_json_success();
});

/**
 * Update user average order info each time an order status is updated
 */
add_action('woocommerce_order_status_changed', function($order_id, $old_status, $new_status) {
	if ($new_status == $old_status) {
		return;
	}

	$order = new WC_Order($order_id);
	$user_id = $order->user_id;

	update_user_meta($user_id, 'order_average', (int) ceil(wc_get_customer_total_spent($user_id) / wc_get_customer_order_count($user_id)));
}, 10, 3);
