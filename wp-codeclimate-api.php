<?php
/**
 * WP CodeClimate API (https://codeclimate.com/docs/api)
 *
 * @package WP-CodeClimate-API
 */

/*
* Plugin Name: WP CodeClimate API
* Plugin URI: https://github.com/wp-api-libraries/wp-codeclimate-api
* Description: Perform API requests to CodeClimate in WordPress.
* Author: WP API Libraries
* Version: 1.0.0
* Author URI: https://wp-api-libraries.com
* GitHub Plugin URI: https://github.com/wp-api-libraries/wp-codeclimate-api
* GitHub Branch: master
*/

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Check if class exists. */
if ( ! class_exists( 'CodeClimateAPI' ) ) {

	/**
	 * CodeClimateAPI class.
	 */
	class CodeClimateAPI {

		/**
		 * API Token.
		 *
		 * @var string
		 */
		static private $api_token;

		/**
		 * URL to the API.
		 *
		 * @var string
		 */
		private $base_uri = 'https://codeclimate.com/api/';


		/**
		 * __construct function.
		 *
		 * @access public
		 * @param mixed $api_token API Token.
		 * @return void
		 */
		public function __construct( $api_token ) {

			static::$api_token = $api_token;

		}

		/**
		 * Fetch the request from the API.
		 *
		 * @access private
		 * @param mixed $request Request URL.
		 * @return $body Body.
		 */
		private function fetch( $request ) {

			$request .= '?api_token=' .static::$api_token;

			$response = wp_remote_get( $request );
			$code = wp_remote_retrieve_response_code( $response );

			if ( 200 !== $code ) {
				return new WP_Error( 'response-error', sprintf( __( 'Server response code: %d', 'text-domain' ), $code ) );
			}

			$body = wp_remote_retrieve_body( $response );

			return json_decode( $body );

		}

		/**
		 * Get list of all repos.
		 *
		 * @access protected
		 * @return $request Request.
		 */
		public function get_repos() {

			$request = $this->base_uri . '/repos';
			return $this->fetch( $request );

		}


		/**
		 * Get Repo Details.
		 *
		 * @access protected
		 * @param mixed $repo_id Repo ID.
		 * @return $request Request.
		 */
		public function get_repo( $repo_id ) {

			if ( empty( $repo_id ) ) {
				return new WP_Error( 'required-fields', __( 'Required fields are empty.', 'text-domain' ) );
			}

			$request = $this->base_uri . '/repos/' . $repo_id;

			return $this->fetch( $request );
		}


		/**
		 * Refresh Repo.
		 *
		 * @access protected
		 * @param mixed $repo_id Repo ID.
		 * @return $request Request.
		 */
		public function refresh_repo( $repo_id ) {

			if ( empty( $repo_id ) ) {
				return new WP_Error( 'required-fields', __( 'Required fields are empty.', 'text-domain' ) );
			}

			$request = $this->base_uri . '/repos/' . $repo_id . 'refresh';

			return $this->fetch( $request );

		}


		/**
		 * Get Repo Branches.
		 *
		 * @access protected
		 * @param mixed $repo_id Repo ID.
		 * @param mixed $branch_name Branch Name.
		 * @return $request Request.
		 */
		public function get_repo_branches( $repo_id, $branch_name ) {

			if ( empty( $repo_id ) || empty( $branch_name ) ) {
				return new WP_Error( 'required-fields', __( 'Required fields are empty.', 'text-domain' ) );
			}

			$request = $this->base_uri . '/repos/' . $repo_id . 'branches/' . $branch_name;

			return $this->fetch( $request );
		}


		/**
		 * Refresh Branch.
		 *
		 * @access protected
		 * @param mixed $repo_id Repo ID.
		 * @param mixed $branch_name Branch Name.
		 * @return $request Request.
		 */
		public function refresh_repo_branches( $repo_id, $branch_name ) {

			if ( empty( $repo_id ) || empty( $branch_name ) ) {
				return new WP_Error( 'required-fields', __( 'Required fields are empty.', 'text-domain' ) );
			}

			$request = $this->base_uri . '/repos/' . $repo_id . 'branches/' . $branch_name . '/refresh';

			return $this->fetch( $request );

		}


		/**
		 * Response Codes.
		 *
		 * @access public
		 * @param string $code (default: '') Response Code.
		 * @return $msg Response Code Message.
		 */
		public function response_code_msg( $code = '' ) {

			switch ( $code ) {
				case 200:
					$msg = __( 'Ok.', 'text-domain' );
				break;
				case 404:
					$msg = __( 'Incorrect Request, Not Found.', 'text-domain' );
				break;
				default:
					$msg = __( 'Response code unknown.', 'text-domain' );
				break;
			}

			return $msg;
		}
	}
}
