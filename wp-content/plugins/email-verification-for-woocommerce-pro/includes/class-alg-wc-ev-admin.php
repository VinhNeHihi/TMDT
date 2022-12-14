<?php
/**
 * Email Verification for WooCommerce - Admin Class
 *
 * @version 1.8.0
 * @since   1.5.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Email_Verification_Admin' ) ) :

class Alg_WC_Email_Verification_Admin {

	/**
	 * Constructor.
	 *
	 * @version 1.8.0
	 * @since   1.5.0
	 * @todo    (maybe) move more stuff here, e.g. settings, action links etc.
	 */
	function __construct() {
		// Admin column
		if ( 'yes' === get_option( 'alg_wc_ev_admin_column', 'yes' ) ) {
			add_filter( 'manage_users_columns',       array( $this, 'add_verified_email_column' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'render_verified_email_column' ), PHP_INT_MAX, 3 );
			if ( $this->is_admin_manual_actions = ( 'yes' === get_option( 'alg_wc_ev_admin_manual', 'no' ) ) ) {
				$this->actions = array(
					'alg_wc_ev_admin_verify',
					'alg_wc_ev_admin_unverify',
					'alg_wc_ev_admin_resend',
					'alg_wc_ev_admin_verify_done',
					'alg_wc_ev_admin_unverify_done',
					'alg_wc_ev_admin_resend_done',
					'_alg_wc_ev_wpnonce',
				);
				add_action( 'admin_init', array( $this, 'admin_manual_actions' ) );
			}
		}
		// Admin delete unverified users
		add_action( 'alg_wc_email_verification_after_save_settings', array( $this, 'maybe_delete_unverified_users' ) );
		// Admin delete unverified users: Cron
		if ( 'yes' === get_option( 'alg_wc_ev_delete_users_cron', 'no' ) ) {
			add_action( 'init',                              array( $this, 'schedule_delete_unverified_users_cron' ) );
			add_action( 'alg_wc_ev_delete_unverified_users', array( $this, 'delete_unverified_users_cron' ) );
		} else {
			add_action( 'init',                              array( $this, 'unschedule_delete_unverified_users_cron' ) );
		}
		add_action( 'init',                                  array( $this, 'unschedule_delete_unverified_users_cron_on_deactivation' ) );
	}

	/**
	 * delete_unverified_users_cron.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function delete_unverified_users_cron() {
		$this->delete_unverified_users( true );
	}

	/**
	 * schedule_delete_unverified_users_cron.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [next] customizable recurrence (e.g. `daily`)
	 */
	function schedule_delete_unverified_users_cron() {
		if ( ! wp_next_scheduled( 'alg_wc_ev_delete_unverified_users' ) ) {
			wp_schedule_event( time(), 'weekly', 'alg_wc_ev_delete_unverified_users' );
		}
	}

	/**
	 * unschedule_delete_unverified_users_cron_on_deactivation.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function unschedule_delete_unverified_users_cron_on_deactivation() {
		register_deactivation_hook( alg_wc_ev()->plugin_file(), array( $this, 'unschedule_delete_unverified_users_cron' ) );
	}

	/**
	 * unschedule_delete_unverified_users_cron.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function unschedule_delete_unverified_users_cron() {
		if ( $time = wp_next_scheduled( 'alg_wc_ev_delete_unverified_users' ) ) {
			wp_unschedule_event( $time, 'alg_wc_ev_delete_unverified_users' );
		}
	}

	/**
	 * maybe_delete_unverified_users.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function maybe_delete_unverified_users() {
		if ( 'yes' === get_option( 'alg_wc_ev_delete_users', 'no' ) ) {
			update_option( 'alg_wc_ev_delete_users', 'no' );
			add_action( 'admin_init', array( $this, 'delete_unverified_users' ) );
		}
	}

	/**
	 * delete_unverified_users.
	 *
	 * @version 1.7.0
	 * @since   1.3.0
	 * @todo    add "preview"
	 */
	function delete_unverified_users( $is_cron = false ) {
		$current_user_id = ( function_exists( 'get_current_user_id' ) && 0 != get_current_user_id() ? get_current_user_id() : null );
		// Args
		$args = array(
			'role__not_in' => get_option( 'alg_wc_ev_skip_user_roles', array( 'administrator' ) ),
			'exclude'      => ( $current_user_id ? array( $current_user_id ) : array() ),
			'meta_query'   => array(
				array(
					'key'     => 'alg_wc_ev_is_activated',
					'value'   => '1',
					'compare' => '!=',
				),
			),
		);
		if ( 'yes' === get_option( 'alg_wc_ev_verify_already_registered', 'no' ) ) {
			$args['meta_query']['relation'] = 'OR';
			$args['meta_query'][] = array(
				'key'     => 'alg_wc_ev_is_activated',
				'value'   => 'alg_wc_ev_is_activated', // this is ignored; needed for WP prior to v3.9, see https://core.trac.wordpress.org/ticket/23268
				'compare' => 'NOT EXISTS',
			);
		}
		$args = apply_filters( 'alg_wc_ev_delete_unverified_users_loop_args', $args, $current_user_id, $is_cron );
		// Loop
		$do_log = ( $is_cron && defined( 'WP_DEBUG' ) && true === WP_DEBUG );
		$total  = 0;
		if ( $is_cron && ! function_exists( 'wp_delete_user' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/user.php' );
		}
		foreach ( get_users( $args ) as $user ) {
			if ( wp_delete_user( $user->ID, $current_user_id ) ) {
				$total++;
				if ( $do_log ) {
					alg_wc_ev()->core->add_to_log( __( 'Cron', 'emails-verification-for-woocommerce' ) . ': ' .
						sprintf( __( 'User deleted: %d.', 'emails-verification-for-woocommerce' ), $user->ID ) );
				}
			}
		}
		// Output
		if ( ! $is_cron && method_exists( 'WC_Admin_Settings', 'add_message' ) ) {
			WC_Admin_Settings::add_message( sprintf( __( 'Total users deleted: %d.', 'emails-verification-for-woocommerce' ), $total ) );
		} elseif ( $do_log ) {
			alg_wc_ev()->core->add_to_log( __( 'Cron', 'emails-verification-for-woocommerce' ) . ': ' .
				sprintf( __( 'Total users deleted: %d.', 'emails-verification-for-woocommerce' ), $total ) );
		}
	}

	/**
	 * get_user_id_from_action.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function get_user_id_from_action( $action, $do_check_nonce = true ) {
		if ( ! empty( $_GET[ $action ] ) && ( $user_id = intval( $_GET[ $action ] ) ) > 0 && current_user_can( 'manage_woocommerce' ) ) {
			if ( ! $do_check_nonce || ( isset( $_GET['_alg_wc_ev_wpnonce'] ) && wp_verify_nonce( $_GET['_alg_wc_ev_wpnonce'], 'alg_wc_ev_action' ) ) ) {
				return $user_id;
			} else {
				wp_die( __( 'Nonce not found or not verified.', 'emails-verification-for-woocommerce' ) );
			}
		}
		return false;
	}

	/**
	 * admin_manual_actions.
	 *
	 * @version 1.8.0
	 * @since   1.1.0
	 * @todo    [next] (maybe) new action: "expire" (i.e. make link expired) (i.e. remove `alg_wc_ev_activation_code_time` meta)
	 */
	function admin_manual_actions() {
		// Actions
		if ( $user_id = $this->get_user_id_from_action( 'alg_wc_ev_admin_verify' ) ) {
			update_user_meta( $user_id, 'alg_wc_ev_is_activated', '1' );
			wp_safe_redirect( add_query_arg( 'alg_wc_ev_admin_verify_done', $user_id, remove_query_arg( $this->actions ) ) );
			exit;
		}
		if ( $user_id = $this->get_user_id_from_action( 'alg_wc_ev_admin_unverify' ) ) {
			update_user_meta( $user_id, 'alg_wc_ev_is_activated', '0' );
			delete_user_meta( $user_id, 'alg_wc_ev_customer_new_account_email_sent' );
			delete_user_meta( $user_id, 'alg_wc_ev_admin_email_sent' );
			wp_safe_redirect( add_query_arg( 'alg_wc_ev_admin_unverify_done', $user_id, remove_query_arg( $this->actions ) ) );
			exit;
		}
		if ( $user_id = $this->get_user_id_from_action( 'alg_wc_ev_admin_resend' ) ) {
			alg_wc_ev()->core->emails->reset_and_mail_activation_link( $user_id );
			wp_safe_redirect( add_query_arg( 'alg_wc_ev_admin_resend_done', $user_id, remove_query_arg( $this->actions ) ) );
			exit;
		}
		// Notices
		if ( $user_id = $this->get_user_id_from_action( 'alg_wc_ev_admin_verify_done', false ) ) {
			$this->admin_notice = sprintf( __( 'User %s: verified.', 'emails-verification-for-woocommerce' ), '<code>ID: ' . $user_id . '</code>' );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
		if ( $user_id = $this->get_user_id_from_action( 'alg_wc_ev_admin_unverify_done', false ) ) {
			$this->admin_notice = sprintf( __( 'User %s: unverified.', 'emails-verification-for-woocommerce' ), '<code>ID: ' . $user_id . '</code>' );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
		if ( $user_id = $this->get_user_id_from_action( 'alg_wc_ev_admin_resend_done', false ) ) {
			$this->admin_notice = sprintf( __( 'User %s: activation link resent.', 'emails-verification-for-woocommerce' ), '<code>ID: ' . $user_id . '</code>' );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
	}

	/**
	 * admin_notices.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function admin_notices() {
		if ( isset( $this->admin_notice ) ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . $this->admin_notice . '</p></div>';
		}
	}

	/**
	 * add_verified_email_column.
	 *
	 * @version 1.0.1
	 * @since   1.0.0
	 * @todo    new column: "expire"
	 * @todo    (maybe) add option to rename the column(s)
	 */
	function add_verified_email_column( $columns ) {
		$columns['alg_wc_ev'] = __( 'Verified', 'emails-verification-for-woocommerce' );
		return $columns;
	}

	/**
	 * get_admin_action_html.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 */
	function get_admin_action_html( $action, $title, $user_id ) {
		$link = wp_nonce_url( add_query_arg( 'alg_wc_ev_admin_' . $action, $user_id, remove_query_arg( $this->actions ) ), 'alg_wc_ev_action', '_alg_wc_ev_wpnonce' );
		return '[<a href="' . $link . '"' . ' onclick="return confirm(\'' . __( 'Are you sure?', 'emails-verification-for-woocommerce' ) . '\')">' . $title . '</a>]';
	}

	/**
	 * render_verified_email_column.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 */
	function render_verified_email_column( $output, $column_name, $user_id ) {
		if ( 'alg_wc_ev' === $column_name && $user_id ) {
			$user = new WP_User( $user_id );
			if ( $user && ! is_wp_error( $user ) ) {
				$output = '';
				if ( ! alg_wc_ev()->core->is_user_verified( $user ) ) {
					$activation_code_time = get_user_meta( $user_id, 'alg_wc_ev_activation_code_time', true );
					if ( $activation_code_time ) {
						$activation_code_time = ' (' . date( 'Y-m-d H:i:s', $activation_code_time ) . ')';
					}
					$output .= '<span title="' . __( 'Email not verified', 'emails-verification-for-woocommerce' ) . $activation_code_time . '">&#10006;</span>';
					if ( $this->is_admin_manual_actions ) {
						$output .= ' ' . $this->get_admin_action_html( 'verify', __( 'verify', 'emails-verification-for-woocommerce' ), $user_id );
						$output .= ' ' . $this->get_admin_action_html( 'resend', __( 'resend', 'emails-verification-for-woocommerce' ), $user_id );
					}
				} else {
					$output .= '<span title="' . __( 'Email verified', 'emails-verification-for-woocommerce' ) . '">&#9745;</span>';
					if ( $this->is_admin_manual_actions && ! alg_wc_ev()->core->is_user_role_skipped( $user ) && ! apply_filters( 'alg_wc_ev_is_user_verified', false, $user_id ) ) {
						$output .= ' ' . $this->get_admin_action_html( 'unverify', __( 'unverify', 'emails-verification-for-woocommerce' ), $user_id );
					}
				}
			}
		}
		return $output;
	}

}

endif;

return new Alg_WC_Email_Verification_Admin();
