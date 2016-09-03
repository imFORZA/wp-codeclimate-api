<?php
/**
 * WP CodeClimate API (https://codeclimate.com/docs/api)
 *
 * @package WP-CodeClimate-API
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }


/**
 * CodeClimateAPI class.
 */
class CodeClimateAPI {

	/**
	 * [__construct description]
	 *
	 * @param [type] $api_key [description]
	 */
	public function __construct( $api_key = null ) {
	}

	/**
	 * get_repos function.
	 *
	 * @access protected
	 * @return void
	 */
	protected function get_repos() {
	}

	/**
	 * get_repo function.
	 *
	 * @access protected
	 * @return void
	 */
	protected function get_repo() {
	}

	/**
	 * get_repo_branches function.
	 *
	 * @access protected
	 * @return void
	 */
	protected function get_repo_branches() {
	}

	/**
	 * refresh_repo function.
	 *
	 * @access protected
	 * @return void
	 */
	protected function refresh_repo() {
	}

	/**
	 * refresh_repo_branches function.
	 *
	 * @access protected
	 * @return void
	 */
	protected function refresh_repo_branches() {
	}

	/**
	 * response_code_msg function.
	 *
	 * @access public
	 * @param string $code (default: '')
	 * @return void
	 */
	public function response_code_msg( $code = '' ) {

		switch ( $code ) {
			case 200:
				$msg = __( 'Ok.','text-domain' );
				break;
			default:
				$msg = __( 'Response code unknown.', 'text-domain' );
				break;
		}

		return $msg;
	}
}
