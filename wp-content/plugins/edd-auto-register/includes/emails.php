<?php
/**
 * EDD Auto Register Emails.
 *
 * @package     EDD Auto Register
 * @subpackage  Emails
 * @copyright   Copyright (c) 2022, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
*/

namespace EDD\Auto_Register;

class Emails {

	/**
	 * Notifications
	 * Sends the user an email with their logins details and also sends the site admin an email notifying them of a signup
	 *
	 * @since 1.4
	 * @param $user_id   int
	 * @param $user_data array
	 */
	public function email_notifications( $user_id = 0, $user_data = array() ) {
		$user = get_userdata( $user_id );

		$this->send_admin_email( $user );
		$this->send_user_email( $user, $user_data );
	}

	/**
	 * Sends the admin email if the setting is not disabled.
	 *
	 * @since 1.4
	 * @param WP_User $user
	 * @return void
	 */
	private function send_admin_email( $user ) {
		if ( edd_get_option( 'edd_auto_register_disable_admin_email', '' ) ) {
			return;
		}

		$blogname = $this->get_blogname();

		$message  = sprintf( __( 'New user registration on your site %s:', 'edd-auto-register' ), $blogname ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', 'edd-auto-register' ), $user->user_login ) . "\r\n\r\n";
		$message .= sprintf( __( 'E-mail: %s', 'edd-auto-register' ), $user->user_email ) . "\r\n";

		wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration', 'edd-auto-register' ), $blogname ), $message );
	}

	/**
	 * Sends the user email if the setting is not disabled.
	 *
	 * @since 1.4
	 * @param WP_User $user
	 * @param array   $user_data
	 * @return void
	 */
	private function send_user_email( $user, $user_data ) {
		if ( edd_get_option( 'edd_auto_register_disable_user_email', '' ) ) {
			return;
		}

		// message
		$message = $this->get_message( $user, $user_data );
		if ( ! $message ) {
			return;
		}

		// subject line
		$subject = apply_filters( 'edd_auto_register_email_subject', sprintf( __( '[%s] Login Details', 'edd-auto-register' ), $this->get_blogname() ) );

		// get from name and email from EDD options
		$from_name  = edd_get_option( 'from_name', get_bloginfo( 'name' ) );
		$from_email = edd_get_option( 'from_email', get_bloginfo( 'admin_email' ) );

		$headers  = 'From: ' . stripslashes_deep( html_entity_decode( $from_name, ENT_COMPAT, 'UTF-8' ) ) . " <$from_email>\r\n";
		$headers .= 'Reply-To: ' . $from_email . "\r\n";
		$headers  = apply_filters( 'edd_auto_register_headers', $headers );

		$emails = new \EDD_Emails();

		$emails->__set( 'from_name', $from_name );
		$emails->__set( 'from_email', $from_email );
		$emails->__set( 'headers', $headers );

		// Email the user
		$emails->send( $user->user_email, $subject, $message );
	}

	/**
	 * The blogname option is escaped with esc_html on the way into the database in sanitize_option
	 * we want to reverse this for the plain text arena of emails.
	 *
	 * @since 1.4
	 * @return string
	 */
	private function get_blogname() {
		return wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	}

	/**
	 * Email Template Body
	 *
	 * @since 1.4
	 * @param WP_User $user
	 * @param array   $user_data
	 * @return false|string $message Body of the email
	 */
	public function get_message( $user, $user_data ) {
		$key = get_password_reset_key( $user );
		if ( is_wp_error( $key ) ) {
			return false;
		}

		// Email body
		$message  = sprintf( __( 'Dear %s', 'edd-auto-register' ), $user->first_name ) . ",\n\n";
		$message .= __( 'Below are your login details:', 'edd-auto-register' ) . "\n\n";
		$message  = sprintf( __( 'Your Username: %s', 'edd-auto-register' ), sanitize_user( $user->user_login, true ) ) . "\r\n\r\n";
		$message .= __( 'To set your password, visit the following address:' ) . "\r\n\r\n";
		$message .= network_site_url( 'wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode( $user->user_login ), 'login' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Login: %s', 'edd-auto-register' ), wp_login_url() ) . "\r\n";

		/**
		 * Optionally filters the email message.
		 *
		 * @param string  $message
		 * @param string  $first_name
		 * @param WP_User $user
		 * @param string  $password
		 */
		return apply_filters( 'edd_auto_register_email_body', $message, $user->first_name, sanitize_user( $user->user_login, true ), $user_data['user_pass'] );
	}

	/**
	 * Define new tags {password_link} and {login_link}
	 *
	 * @since 1.4
	 */
	public function add_email_tag() {

		edd_add_email_tag(
			'password_link',
			__( 'The link to set the user\'s password. This will only be included for the user\'s first purchase.', 'edd-auto-register' ),
			array( $this, 'password_link_tag' ),
			__( 'Password Reset Link', 'edd-auto-register' )
		);

		edd_add_email_tag(
			'login_link',
			__( 'The link to log into the site.', 'edd-auto-register' ),
			array( $this, 'login_link_tag' ),
			__( 'Login Link', 'edd-auto-register' )
		);
	}

	/**
	 * Email tag callback for {password_link}.
	 * Returns the link for new users; otherwise returns an empty string.
	 *
	 * @since 1.4
	 * @return string
	 */
	public function password_link_tag( $payment_id ) {
		if ( ! $this->is_first_purchase( $payment_id ) ) {
			return '';
		}
		$user = $this->get_user_for_order( $payment_id );
		if ( ! $user ) {
			return '';
		}
		$password_reset_link = $this->get_password_reset_link( $user );
		if ( ! $password_reset_link ) {
			return '';
		}

		return sprintf(
			'<a href="%1$s">%2$s</a>',
			$password_reset_link,
			__( 'Set your password', 'edd-auto-register' )
		);
	}

	/**
	 * Gets the password reset link for the user.
	 *
	 * @since 1.4.4
	 * @param WP_User $user
	 * @return false|string
	 */
	private function get_password_reset_link( $user ) {
		if ( function_exists( 'edd_get_password_reset_link' ) ) {
			return edd_get_password_reset_link( $user );
		}
		$key = get_password_reset_key( $user );
		if ( is_wp_error( $key ) ) {
			return false;
		}

		return add_query_arg(
			array(
				'action' => 'rp',
				'key'    => $key,
				'login'  => rawurlencode( $user->user_login ),
			),
			network_site_url( 'wp-login.php', 'login' )
		);
	}

	/**
	 * Email tag callback for {login_link}
	 *
	 * @since 1.4
	 * @return string Return link to log into the site.
	 */
	public function login_link_tag( $payment_id ) {
		return sprintf(
			'<a href="%1$s">%2$s</a>',
			wp_login_url(),
			__( 'Login', 'edd-auto-register' )
		);
	}

	/**
	 * Check if it the first purchase for a given user.
	 *
	 * @since 1.4
	 * @return bool
	 */
	private function is_first_purchase( $payment_id ) {
		$user = $this->get_user_for_order( $payment_id );
		if ( ! $user ) {
			return false;
		}
		$customer = new \EDD_Customer( $user->ID, true );

		return $customer instanceof \EDD_Customer && $customer->purchase_count <= 1;
	}

	/**
	 * Fetch user Object
	 *
	 * @since 1.4
	 * @param int $payment_id Payment ID
	 * @return WP_User|false WP_User object on success, false on failure.
	 */
	private function get_user_for_order( $payment_id = 0 ) {
		$user_id = edd_get_payment_user_id( $payment_id );
		if ( $user_id ) {
			return get_userdata( $user_id );
		}

		return false;
	}
}
