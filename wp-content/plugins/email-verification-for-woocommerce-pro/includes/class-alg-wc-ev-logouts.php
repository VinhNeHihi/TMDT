<?php
/**
 * Email Verification for WooCommerce - Logouts Class
 *
 * @version 1.9.0
 * @since   1.6.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Email_Verification_Logouts' ) ) :

class Alg_WC_Email_Verification_Logouts {

	/**
	 * Constructor.
	 *
	 * @version 1.8.3
	 * @since   1.6.0
	 * @todo    (maybe) force "activate" notice for guest users also
	 * @todo    (maybe) `alg_wc_ev_prevent_login_after_register`: `woocommerce_account_navigation` (doesn't seem to work though...)
	 */
	function __construct() {
		// Block unverified user login
		add_filter( 'wp_authenticate_user', array( $this, 'block_unverified_user_login' ), PHP_INT_MAX );
		// Prevent login: After registration
		if ( 'yes' === get_option( 'alg_wc_ev_prevent_login_after_register', 'yes' ) ) {
			add_filter( 'woocommerce_registration_auth_new_customer', '__return_true', PHP_INT_MAX );
			add_filter( 'woocommerce_registration_redirect', array( $this, 'logout_and_redirect_user_on_registration' ), PHP_INT_MAX );
		}
		// Prevent login: After checkout
		if ( 'yes' === get_option( 'alg_wc_ev_prevent_login_after_checkout', 'yes' ) ) {
			if ( 'woocommerce_get_return_url' === ( $action = get_option( 'alg_wc_ev_prevent_login_after_checkout_action', 'woocommerce_get_return_url' ) ) ) {
				add_filter( 'woocommerce_get_return_url', array( $this, 'logout_and_redirect_user_after_checkout' ), PHP_INT_MAX );
				if ( 'yes' === get_option( 'alg_wc_ev_prevent_login_after_checkout_notice', 'yes' ) ) {
					add_action( 'woocommerce_before_thankyou', array( $this, 'print_wc_notices' ) );
				}
			} else { // 'woocommerce_before_thankyou', 'woocommerce_thankyou'
				add_action( $action, array( $this, 'logout_and_redirect_user_after_checkout_thankyou' ) );
			}
		}
		// Prevent login: My account
		if ( 'yes' === get_option( 'alg_wc_ev_prevent_login_myaccount', 'no' ) ) {
			add_action( 'template_redirect', array( $this, 'logout_and_redirect_user_myaccount' ), PHP_INT_MAX );
		}
		// Prevent login: Always
		if ( 'yes' === get_option( 'alg_wc_ev_prevent_login_always', 'no' ) ) {
			add_action( 'wp_footer', array( $this, 'logout_and_redirect_user_always' ), PHP_INT_MAX );
		}
	}

	/**
	 * logout_and_redirect_user_myaccount.
	 *
	 * @version 1.8.3
	 * @since   1.8.3
	 */
	function logout_and_redirect_user_myaccount() {
		if ( is_account_page() && ( $user_id = get_current_user_id() ) && ! alg_wc_ev()->core->is_user_verified_by_user_id( $user_id ) ) {
			$this->logout_user();
			wp_safe_redirect( add_query_arg( 'alg_wc_ev_activate_account_message', $user_id ) );
			exit;
		}
	}

	/**
	 * block_unverified_user_login.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 */
	function block_unverified_user_login( $user ) {
		if ( ! alg_wc_ev()->core->is_user_verified( $user ) ) {
			$user = new WP_Error( 'alg_wc_ev_email_verified_error', alg_wc_ev()->core->messages->get_error_message( $user->ID ) );
		}
		return $user;
	}

	/**
	 * get_redirect_on_registration.
	 *
	 * @version 1.9.0
	 * @since   1.9.0
	 */
	function get_redirect_on_registration( $redirect_to ) {
		switch ( get_option( 'alg_wc_ev_prevent_login_after_register_redirect', 'no' ) ) {
			case 'yes':
				return wc_get_page_permalink( 'myaccount' );
			case 'custom':
				return get_option( 'alg_wc_ev_prevent_login_after_register_redirect_url', '' );
			default: // 'no'
				return $redirect_to;
		}
	}

	/**
	 * logout_and_redirect.
	 *
	 * @version 1.9.0
	 * @since   1.9.0
	 */
	function logout_and_redirect( $redirect_to, $type ) {
		if ( ( $user_id = get_current_user_id() ) && ! alg_wc_ev()->core->is_user_verified_by_user_id( $user_id ) ) {
			$this->logout_user();
			switch ( $type ) {
				case 'on_registration':
					$redirect_to = apply_filters( 'alg_wc_ev_redirect_on_registration', $this->get_redirect_on_registration( $redirect_to ), $user_id );
					break;
				case 'after_checkout':
					$redirect_to = apply_filters( 'alg_wc_ev_redirect_after_checkout', $redirect_to, $user_id );
					break;
			}
			return add_query_arg( 'alg_wc_ev_activate_account_message', $user_id, $redirect_to );
		} else {
			return $redirect_to;
		}
	}

	/**
	 * logout_and_redirect_user_on_registration.
	 *
	 * @version 1.9.0
	 * @since   1.0.0
	 */
	function logout_and_redirect_user_on_registration( $redirect_to ) {
		return $this->logout_and_redirect( $redirect_to, 'on_registration' );
	}

	/**
	 * logout_and_redirect_user_after_checkout.
	 *
	 * @version 1.9.0
	 * @since   1.5.0
	 */
	function logout_and_redirect_user_after_checkout( $redirect_to ) {
		return $this->logout_and_redirect( $redirect_to, 'after_checkout' );
	}

	/**
	 * logout_and_redirect_user_after_checkout_thankyou.
	 *
	 * @version 1.6.0
	 * @since   1.5.0
	 */
	function logout_and_redirect_user_after_checkout_thankyou() {
		if ( ( $user_id = get_current_user_id() ) && ! alg_wc_ev()->core->is_user_verified_by_user_id( $user_id ) ) {
			$this->logout_user();
			do_action( 'alg_wc_ev_after_thankyou_logout', $user_id );
			if ( 'yes' === get_option( 'alg_wc_ev_prevent_login_after_checkout_notice', 'yes' ) ) {
				wc_print_notice( alg_wc_ev()->core->messages->get_activation_message( $user_id ) );
			}
		}
	}

	/**
	 * logout_and_redirect_user_always.
	 *
	 * @version 1.8.1
	 * @since   1.8.0
	 * @todo    (maybe) `wc_add_notice( alg_wc_ev()->core->messages->get_activation_message( $user_id ) );` (i.e. instead of redirect)
	 */
	function logout_and_redirect_user_always() {
		if ( ( $user_id = get_current_user_id() ) && ! alg_wc_ev()->core->is_user_verified_by_user_id( $user_id ) ) {
			$this->logout_user();
			if ( 'yes' === get_option( 'alg_wc_ev_prevent_login_always_redirect', 'yes' ) ) {
				wp_safe_redirect( add_query_arg( 'alg_wc_ev_activate_account_message', $user_id ) );
				exit;
			}
		}
	}

	/**
	 * print_wc_notices.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function print_wc_notices( $order_id ) {
		wc_print_notices();
	}

	/**
	 * logout_user.
	 *
	 * @version 1.4.1
	 * @since   1.4.1
	 */
	function logout_user() {
		if ( 'yes' === get_option( 'alg_wc_ev_custom_logout_function', 'no' ) ) {
			// same as standard WP `wp_logout()` function (in `pluggable.php`) except `do_action( 'wp_logout' )`
			wp_destroy_current_session();
			wp_clear_auth_cookie();
			wp_set_current_user( 0 );
		} else {
			wp_logout();
		}
	}

}

endif;

return new Alg_WC_Email_Verification_Logouts();
