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
* Text Domain: wp-codeclimate-api
* Author URI: https://wp-api-libraries.com
* GitHub Plugin URI: https://github.com/wp-api-libraries/wp-codeclimate-api
* GitHub Branch: master
*/

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'WpCodeClimateBase' ) ) {
	include_once( 'wp-api-libraries-base.php' );
}

/* Check if class exists. */
if ( ! class_exists( 'CodeClimateAPI' ) ) {

	/**
	 * CodeClimateAPI class.
	 */
	class CodeClimateAPI extends WpCodeClimateBase {

		protected $base_uri = 'https://api.codeclimate.com/v1/';

		/**
		 * API Token.
		 *
		 * @var string
		 */
		private $api_token;

		/**
		 * Arguments to populate.
		 *
		 * @var array
		 */
		protected $args;

		/**
		* Constructiroy.
		*
		* @access public
		* @param string $subdomain The organization's subdomain.
		* @param string $api_key   The auth API key.
		* @return void
		*/
		public function __construct( string $api_key ) {
		 $this->set_api_key( $api_key );
		}

		/**
		* Set the API key.
		*
		* @access public
		* @param string $api_key The API key.
		* @return void
		*/
		public function set_api_key( string $api_key ) {
		 $this->api_key = $api_key;
		}

		/**
		* Set headers.
		*
		* @access protected
		* @return void
		*/
		protected function set_headers() {
			$this->args['headers'] = array(
				'Authorization' => 'Token token='.$this->api_key,
				'Content-Type'  => 'application/vnd.api+json',
				'Accept'        => 'application/vnd.api+json',
			);
		}

		/**
		* Clear arguments.
		*
		* @access protected
		* @return void
		*/
		protected function clear() {
			$this->args = array();
		}

		/**
		* Execute call. Is a wrapper for $this->build_request( $route, $body, $method )->fetch
		*
		* @access protected
		* @param  string $route  The URI extension to send the request to.
		* @param  array  $body   Additional arguments to pass.
		* @param  string $method The method to call (get, delete, post, etc).
		* @return mixed          The response.
		*/
		protected function run( string $route, array $body = array(), string $method = 'GET', $type = '' ) {
			if( $method !== 'GET' ){
				$body = array(
					'data' => array(
						'attributes' => $body
					)
				);

				if( $type ){
					$body['data']['type'] = $type;
				}
			}

			return $this->build_request( $route, $body, $method )->fetch();
		}

		/**
		 * Pagination function.
		 *
		 * Example usage:
		 *
		 * $api = new CodeClimateAPI( /.../ );
		 * return $api->set_pagination( 2, 50 )->get_orgs();
		 *
		 * @param  int $page      The page to offset results by.
		 * @param  int $size      The number of results to display per page.
		 * @return CodeClimateAPI $this.
		 */
		public function set_pagination( $page = 1, $size = 30 ){
			if( ! isset( $this->args['body'] ) ){
				$this->args['body'] = array();
			}

			if( ! isset( $this->args['body']['page'] ) ){
				$this->args['body']['page'] = array();
			}

			$this->args['body']['page']['number'] = intval( $page );
			$this->args['body']['page']['number'] = intval( $size );

			return $this;
		}

		/**
		 * Wrapper for $this->set_pagination.
		 *
		 * Example usage:
		 *
		 * $api = new CodeClimateAPI( /.../ );
		 * return $api->p( 2, 50 )->get_orgs();
		 *
		 * @param  int $page      The page to offset results by.
		 * @param  int $size      The number of results to display per page.
		 * @return CodeCliamteAPI $this.
		 */
		public function p( $page = 1, $size = 30 ){
			return $this->set_pagination( $page, $size );
		}

		/**
		 * Get user.
		 *
		 * @access public
		 * @return object The user object.
		 */
		public function get_user() {
			return $this->run( 'user' );
		}

		/**
		 * Get organizations.
		 *
		 * Returns collection of organizations for the current user.
		 *
		 * Supports pagination.
		 *
		 * @access public
		 * @return object A list of organizations, along with some meta.
		 */
		public function get_orgs() {
			return $this->run( 'orgs' );
		}

		/**
		 * Get permissions
		 *
		 * Retrieves permissions such as which members can manage issues and/or
		 * approve pull requests.
		 *
		 * @access public
		 * @param  string $org_id The ID of the organization.
		 * @return object         The permissions of the organization.
		 */
		public function get_org_permissions( string $org_id ){
			return $this->run( 'orgs/'.$org_id.'/permissions' );
		}

		/**
		 * Create organization
		 *
		 * Creates a new single-person organization with the specified attributes.
		 * If the organization was created successfully, this endpoint responds with
		 * the created organization and status 201.
		 *
		 * @access public
		 * @param  string $name The name of the new organization.
		 * @return object       The newly created organization.
		 */
		public function create_org( string $name ) {
			return $this->run( 'orgs', array( 'name' => $name ), 'POST', 'orgs' );
		}

		/**
		 * Add private repository
		 *
		 * Adds the repository to the specified organization.
		 * If the repository was added successfully, this endpoint responds with
		 * the added repository and status 201.
		 *
		 * @access public
		 * @param  string $org_id The org to attach the repository to.
		 * @param  string $url    Code Climate uses the url parameter to determine where
		 *                        your repository is hosted and how to clone it. Currently,
		 *                        only repositories hosted on GitHub are supported, so we
		 *                        only accept https://github.com URLs. Once created, users
		 *                        will still find a Deploy Key added on GitHub and an
		 *                        SSH-based clone URL in their repo settings.
		 * @return object         The created repoitory
		 */
		public function add_private_repo( string $org_id, string $url ) {
			return $this->run( 'orgs/'.$org_id.'/repos', array( 'url' => $url ), 'POST', 'repos' );
		}

		/**
		 * Get repositories by organization
		 *
		 * Returns listing of repositories for the specified organization that the
		 * authenticated user (user associated with the passed token) has access to.
		 *
		 * ...Might support pagination?
		 *
		 * @access protected
		 * @param  string $org_id The ID of the organization.
		 * @return object         A list of repos associated with the organization.
		 */
		public function get_repos( string $org_id ) {
			return $this->run( 'orgs/'.$org_id.'/repos' );
		}

		/**
		 * Get repository (by ID)
		 *
		 * Retrieves information about the specified repository.
		 *
		 * https://api.codeclimate.com/v1/repos/:repo_id
		 *
		 * @access protected
		 * @param  string $repo_id The ID of the repository.
		 * @return object          The repository.
		 */
		public function get_repo_by_id( $repo_id ) {
			return $this->run( 'repos/'.$repo_id );
		}

		/**
		 * Get repository (by GitHub slug)
		 *
		 * Retrieves information about the specified repository.
		 *
		 * https://api.codeclimate.com/v1/repos?github_slug={github_slug}
		 *
		 * @param  string $github_slug The github slug.
		 * @return object              The repository.
		 */
		public function get_repo_by_git( $github_slug ) {
			return $this->run( 'repos', array( 'github_slug' => $github_slug ) );
		}

		/**
		 * Get ref points
		 *
		 * Returns collection of ref points for the repository.
		 *
		 * A ref point is an observation of a commit on a branch at a moment in time.
		 * Whenever Code Climate is notified about a new commit (e.g. through webhook
		 * events), Code Climate create a ref point for it. It's sort of like our own
		 * git log of the actions that occurred on the repository and won't change if
		 * you force-push and change history.
		 *
		 * Ref points are sorted by creation date in reverse chronological order.
		 *
		 * $filter supports:
		 *   analyzed:
		 *     Only ref points which have been analyzed (or not analyzed) by Code Climate
		 *   branch:
		 *     Only ref points associated with the specified branch
		 *   commit_sha:
		 *     Only ref points associated with the given commit_sha
		 *   local_ref:
		 *     Only ref points associated with the given local_ref. Examples include:
		 *       refs/pulls/553/head and ref/heads/master
		 *
		 * Might support pagination?
		 *
		 * @param  string $repo_id The ID of the repository.
		 * @param  array  $filter  (Default: array()) Additional optional arguments to filter by.
		 * @return object          List of referral points.
		 */
		public function get_ref_points( $repo_id, $filter = array() ) {
			return $this->run( 'repos/'.$repo_id.'/ref_points', array( 'filter' => $filter ) );
		}

		/**
		 * Get repository services
		 *
		 * Returns a collection of (external) service integrations for a particular
		 * repository. There are few types of integrations:
		 * - Issue tracker
		 * - Chat service
		 * - Pull requests
		 *
		 * Might support pagination?
		 *
		 * @param  string $repo_id The ID of the repository
		 * @param  mixed  $type    (Default: null) Only integrations of this type. Only
		 *                         supports issue_tracker value at the moment.
		 * @return object          A list of repository services.
		 */
		public function get_repo_services( $repo_id, $type = null ){
			$args = array();

			if( null !== $type ){
				$args['filter'] = array(
					'type' => $type
				);
			}

			return $this->run( 'repos/'.$repo_id.'/services', $args );
		}

		/**
		 * Trigger new service event
		 *
		 * Trigger an event to be consumed by one of the repository's service integrations.
		 *
		 * Event format:
		 *   event: {
		 *     name: 'issue',
		 *     issue: {
		 *       check_name : 'Name',
		 *       description: 'Description',
		 *       details_url: 'https://example.com/repos/1/issue@issue_12345',
		 *       id         : 12345,
		 *       locations  : {
		 *         path: 'foo.rb'
		 *       }
		 *     }
		 *   }
		 *
		 * Example:
		 *   "name" : "issue",
	 	 *   "issue": {
	 	 *     "check_name" : "Metrics/CyclomaticComplexity",
	 	 *     "description": "Cyclomatic complexity for method is too high.",
	 	 *     "details_url": "http://example.com/repos/1/issues#issue_12345",
	 	 *     "id"         : "12345",
	 	 *     "location"   : {
	 	 *       "path": "foo.rb"
 		 *     }
		 *   }
		 *
		 * @access public
		 * @param  string $repo_id    The repository ID.
		 * @param  string $service_id The service ID.
		 * @param  object $event      The event.
		 * @return object             Data about the service event.
		 */
		public function trigger_repo_service_event( string $repo_id, string $service_id, array $event ){
			return $this->run( 'repos/'.$repo_id.'/services/'.$service_id.'/events', $event, 'POST' );
		}

		/**
		 * Add public (OSS) repository
		 *
		 * Add a GitHub open source repository to Code Climate.
		 *
		 * @access public
		 * @param  string $url Code Climate uses the url parameter to determine where your
		 *                     repository is hosted and how to clone it. Currently, only
		 *                     repositories hosted on GitHub are supported, so we only accept
		 *                     https://github.com URLs. Once created, users will still find a
		 *                     Deploy Key added on GitHub and an SSH-based clone URL in their
		 *                     repo settings.
		 * @return object      The repository (if successful).
		 */
		public function add_repository( string $url ){
			return $this->run( 'github/repos', array( 'url' => $url ), 'POST', 'repos' );
		}

		/**
		 * Get issues
		 *
		 * Returns a paginated collection of analysis issues found by the snapshot.
		 * Each issue found includes its status ("invalid", "won't fix" etc), location,
		 * fingerprint, severity and other details.
		 *
		 * $filter also supports:
		 *   severity:
		 *     	Single severity or $in clause containing list of severities.
		 *   status:
		 *     Single status or $in clause containing list of statuses.
		 *   location.path:
		 *     Single path or $in clause containing list of statuses.
		 *
		 * Can be paginated.
		 *
		 * @param  string $repo_id     The repository ID.
		 * @param  string $snapshot_id The snapshot ID.
		 * @param  array  $filter      Additional arguments to filter by.
		 * @return object              A list of issues.
		 */
		public function get_issues( string $repo_id, string $snapshot_id, $filter = array() ){
			return $this->run( 'repos/'.$repo_id.'/snapshots/'.$snapshot_id.'/issues', array( 'filter' => $filter ) );
		}

		/**
		 * Get files
		 *
		 * Retrieve analysis of files associated with a given snapshot.
		 *
		 * Sorted by path in ascending order.
		 *
		 * Can be paginated.
		 *
		 * @param  string $repo_id     The repository ID.
		 * @param  mixed  $snapshot_id The snapshot ID.
		 * @return object              A list of files associated with the snapshot.
		 */
		public function get_files( string $repo_id, $snapshot_id ){
			return $this->run( 'repos/'.$repo_id.'/snapshots/'.$snapshot_id.'/files' );
		}

		/**
		 * Get builds
		 *
		 * Returns collection of builds for the repository, sorted in descending order
		 * by build number.
		 *
		 * Builds represent an attempt to run analysis on a particular commit of a repository.
		 * Builds may not have started or finished, or finished successfully.
		 *
		 * $filter also supports:
		 *   state:
		 *     Only builds in a particular state. Supports the states 'new' or 'running'
		 *   local_ref:
		 *     Only builds associated with the given local_ref. Examples include
		 *     refs/pulls/553/head and ref/heads/master
		 *
		 * Can be paginated.
		 *
		 * @param  string $repo_id     The repository ID.
		 * @param  array  $filter      (Default: array()) Additional optional arguments to filter by.
		 * @return object              A list of builds.
		 */
		public function get_builds( string $repo_id, $filter = array() ){
			return $this->run( 'repos/'.$repo_id.'/builds', array( 'filter' => $filter ) );
		}

		/**
		 * Get build
		 *
		 * A build represent an attempt to run analysis on a particular commit of a
		 * repository. Builds may not have started or finished, or finished successfully.
		 *
		 * @param  string $repo_id The repository ID.
		 * @param  int    $build   The build number.
		 * @return object          A list of builds.
		 */
		public function get_build( string $repo_id, int $build ) {
			return $this->run( 'repos/'.$repo_id.'/builds/'.$build );
		}

		/**
		 * Get snapshot
		 *
		 * Retrieves information associated with a given snapshot.
		 *
		 * A snapshot represents a successful completed analysis of a specific commit.
		 *
		 * @param  string $repo_id     The repository ID.
		 * @param  mixed  $snapshot_id The snapshot ID.
		 * @return object              The snapshot.
		 */
		public function get_snapshot( string $repo_id, $snapshot_id ){
			return $this->run( 'repos/'.$repo_id.'/snapshots/'.$snapshot_id );
		}

		/**
		 * Get time series
		 *
		 * Returns information about a particular repository metric as a time series.
		 * The time series returned is an array of data points, each containing a
		 * 'timestamp' and a 'value' for the metric at that 'timestamp'. A range of time is
		 * required, provided by passing query parameters to and from.
		 *
		 * Currently, data points are captured and returned in weekly increments only.
		 * The timestamp for a particular data point represents the start of that time period.
		 *
		 * Can be paginated.
		 *
		 * @param  string $repo_id The repository ID.
		 * @param  string $metric  The selected metric. Supports:
		 *                           gpa       - Grade point average of repository.
		 *                           ratings.A - Number of files with an A rating
		 *                           ratings.B - Number of files with an B rating..
		 *                           ratings.C - Number of files with an C rating.
		 *                           ratings.D - Number of files with an D rating.
		 *                           ratings.F - Number of files with an F rating.
		 * @param  string $from    Start date of time series range, in YYYY-MM-DD format.
		 * @param  string $to      End date of time series range, in YYYY-MM-DD format.
		 * @return object          A list of time series.
		 */
		public function get_time_series( string $repo_id, string $metric, string $from, string $to ){
			if( ! preg_match( '/^ratings\.[ABCDF]$/', $metric ) && 'gpa' !== $metric ){
				return new WP_Error( 'invalid-data', __( 'Not accepted metric.', 'wp-codeclimate-api' ), array( 'status' => 400 ) );
			}

			$args = array(
				'filter' => array(
					'to'   => $to,
					'from' => $from
				)
			);

			return $this->run( 'repos/'.$repo_id.'/metrics/'.$metric, $args );
		}

		/**
		 * Get test coverage reports
		 *
		 * Gets collection of test coverage reports, sorted by committed at descending.
		 *
		 * Each test coverage report contains the overall coverage percentage and
		 * test coverage rating for the repository at a specific commit. Does not
		 * contain line by line coverage: line by line coverage is available via
		 * the test file reports endpoint.
		 *
		 * Can be paginated.
		 *
		 * @param  string $repo_id The repository ID.
		 * @return object          A collection of test coverage reports.
		 */
		public function get_test_reports( string $repo_id ){
			return $this->run( 'repos/'.$repo_id.'/test_reports' );
		}

		/**
		 * Get test coverage file reports
		 *
		 * Gets collection of test coverage file reports, containing line by line
		 * coverage information.
		 *
		 * The coverage attribute contains an array of coverage information. null in
		 * the array means that line is "uncoverable" (e.g. blank line or comment), a
		 * number in the array represents how many times that line was covered by tests.
		 *
		 * Sorted by file paths in ascending order.
		 *
		 * Can be paginated.
		 *
		 * @param  string $repo_id        The repository ID.
		 * @param  string $test_report_id The test report ID.
		 * @return object                 Test file reports for the test report.
		 */
		public function get_test_file_reports( string $repo_id, string $test_report_id ){
			return $this->run( 'repos/'.$repo_id.'/test_reports/'.$test_report_id.'/test_file_reports' );
		}

		/**
		 * TODO: at some point, we could try reverse engineering this...
		 *
		 * Sending test coverage data
		 *
		 * While the API also supports receiving test coverage data from your build,
		 *  we generally do not recommend issuing calls against these endpoints yourself
		 *  and as such have not documented them.
		 *
		 * Instead, we recommend that you use our test reporter client which takes
		 * care of this plumbing for you.
		 *
		 * If the test reporter doesn't suit your specific needs, open an issue or
		 * pull request on that repository.
		 */

		/**
		 * Get rating changes
		 *
		 * Returns rating changes for files in a pull request.
		 *
		 * @param  string $repo_id The repository ID.
		 * @param  int    $pull    The pull number.
		 * @param  string $path    Complete file path for file to filter by.
		 * @return object          The rating changes.
		 */
		public function get_rating_changes( string $repo_id, int $pull, string $path ){
			return $this->run( 'repos/'.$repo_id.'/pulls/'.$pull.'/files', array( 'filter' => array( 'path' => $path ) ) );
		}

		/**
		 * Approve PRs
		 *
		 * Approves a given pull request.
		 *
		 * @param  string $repo_id The repository ID.
		 * @param  int    $pull    The number of the pull request.
		 * @param  string $reason  (Default: 'merge') The reason. Not sure, it's not very well documented...
		 * @return object          The response.
		 */
		public function approve_pull_request( string $repo_id, int $pull, string $reason = 'merge' ){
			return $this->run( 'repos/'.$repo_id.'/pulls/'.$pull.'/approvals', array( 'reason' => $reason ), 'POST' );
		}
	}
}
