<?php

use \Firebase\JWT\JWT;

/**
 * Class Connecting Zoom APi V2
 *
 * @since   2.0
 * @author  Deepen
 * @modifiedn
 */
if ( ! class_exists( 'Zoom_Video_Conferencing_Api' ) ) {

	class Zoom_Video_Conferencing_Api {

		/**
		 * Zoom API KEY
		 *
		 * @var
		 */
		public $zoom_api_key;

		/**
		 * Zoom API Secret
		 *
		 * @var
		 */
		public $zoom_api_secret;

		/**
		 * Hold my instance
		 *
		 * @var
		 */
		protected static $_instance;

		/**
		 * API endpoint base
		 *
		 * @var string
		 */
		private $api_url = 'https://api.zoom.us/v2/';

		/**
		 * Create only one instance so that it may not Repeat
		 *
		 * @since 2.0.0
		 */
		public static function instance(): Zoom_Video_Conferencing_Api {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Zoom_Video_Conferencing_Api constructor.
		 *
		 * @param $zoom_api_key
		 * @param $zoom_api_secret
		 */
		public function __construct( $zoom_api_key = '', $zoom_api_secret = '' ) {
			$this->zoom_api_key    = get_option( 'zoom_api_key' );
			$this->zoom_api_secret = get_option( 'zoom_api_secret' );
		}

		/**
		 * Change the keys constructor.
		 *
		 * @param $zoom_api_key
		 * @param $zoom_api_secret
		 */
		public function set_new_keys( $zoom_api_key = '', $zoom_api_secret = '' ): void {
			$this->zoom_api_key    = $zoom_api_key;
			$this->zoom_api_secret = $zoom_api_secret;
		}

		/**
		 * Send request to API
		 *
		 * @param $calledFunction
		 * @param $data
		 * @param string $request
		 *
		 * @return array|bool|string|WP_Error
		 */
		public function     sendRequest( $calledFunction, $data, $request = "GET" ) {

			$request_url = $this->api_url . $calledFunction;
			$args        = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->generateJWTKey(),
					'Content-Type'  => 'application/json'
				)
			);

			if ( $request === "GET" ) {
				$args['body'] = ! empty( $data ) ? $data : array();
				$response     = wp_remote_get( $request_url, $args );
			} else if ( $request === "DELETE" ) {
				$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
				$args['method'] = "DELETE";
				$response       = wp_remote_request( $request_url, $args );
			} else if ( $request === "PATCH" ) {
				$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
				$args['method'] = "PATCH";
				$response       = wp_remote_request( $request_url, $args );
			} else if ( $request === "PUT" ) {
				$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
				$args['method'] = "PUT";
				$response       = wp_remote_request( $request_url, $args );
			} else {
				$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
				$args['method'] = "POST";
				$response       = wp_remote_post( $request_url, $args );
			}

			if ( is_wp_error( $response ) ) {
				return false;
			}

			if ( ! isset( $response['body'] ) ) {
				return false;
			}

			if ( ! empty( $response['response'] ) && ! empty( $response['response']['code'] ) && ( $response['response']['code'] === 203 || $response['response']['code'] === 204 ) ) {
				return true;
			}

			$response_body = $response['body'];
			if ( ! empty( $response_body['response'] ) && ! empty( $response_body['response']['code'] ) && ( $response_body['response']['code'] === 203 || $response_body['response']['code'] === 204 ) ) {
				return true;
			}

			return $response_body;
		}

		//function to generate JWT
		private function generateJWTKey(): string {
			$key    = $this->zoom_api_key;
			$secret = $this->zoom_api_secret;

			$token = array(
				"iss" => $key,
				"exp" => time() + 3600 //60 seconds as suggested
			);

			return JWT::encode( $token, $secret );
		}

		/**
		 * Creates a User
		 *
		 * @param $postedData
		 *
		 * @return array|bool|string
		 */
		public function createAUser( $postedData = array() ) {
			$createAUserArray              = array();
			$createAUserArray['action']    = $postedData['action'];
			$createAUserArray['user_info'] = array(
				'email'      => $postedData['email'],
				'type'       => $postedData['type'],
				'first_name' => $postedData['first_name'],
				'last_name'  => $postedData['last_name']
			);
			$createAUserArray              = apply_filters( 'vczapi_createAUser', $createAUserArray );

			return $this->sendRequest( 'users', $createAUserArray, "POST" );
		}

		/**
		 * User Function to List
		 *
		 * @param $page
		 * @param $params
		 *
		 * @return array
		 */
		public function listUsers( $page = 1, $params = array() ) {
			$listUsersArray                = array();
			$listUsersArray['page_size']   = 300;
			$listUsersArray['page_number'] = absint( $page );
			$listUsersArray                = apply_filters( 'vczapi_listUsers', $listUsersArray );

			if ( ! empty( $params ) ) {
				$listUsersArray = array_merge( $listUsersArray, $params );
			}

			return $this->sendRequest( 'users', $listUsersArray, "GET" );
		}

		/**
		 * Get A users info by user Id
		 *
		 * @param $user_id
		 *
		 * @return array|bool|string
		 */
		public function getUserInfo( $user_id ) {
			$getUserInfoArray = array();
			$getUserInfoArray = apply_filters( 'vczapi_getUserInfo', $getUserInfoArray );

			return $this->sendRequest( 'users/' . $user_id, $getUserInfoArray );
		}

		/**
		 * Delete a User
		 *
		 * @param $userid
		 *
		 * @return array|bool|string
		 */
		public function deleteAUser( $userid ) {
			$deleteAUserArray       = array();
			$deleteAUserArray['id'] = $userid;

			return $this->sendRequest( 'users/' . $userid, false, "DELETE" );
		}

		/**
		 * Get Meetings
		 *
		 * @param $host_id
		 *
		 * @return array
		 */
		public function listMeetings( $host_id ) {
			$listMeetingsArray              = array();
			$listMeetingsArray['page_size'] = 300;
			$listMeetingsArray              = apply_filters( 'vczapi_listMeetings', $listMeetingsArray );

			return $this->sendRequest( 'users/' . $host_id . '/meetings', $listMeetingsArray, "GET" );
		}

		/**
		 * Create A meeting API
		 *
		 * @param array $data
		 *
		 * @return array|bool|string|void|WP_Error
		 */
		public function createAMeeting( $data = array() ) {
			$post_time  = $data['start_date'];
			$start_time = gmdate( "Y-m-d\TH:i:s", strtotime( $post_time ) );

			$createAMeetingArray = array();

			if ( ! empty( $data['alternative_host_ids'] ) ) {
				if ( count( $data['alternative_host_ids'] ) > 1 ) {
					$alternative_host_ids = implode( ",", $data['alternative_host_ids'] );
				} else {
					$alternative_host_ids = $data['alternative_host_ids'][0];
				}
			}

			$createAMeetingArray['topic']      = $data['meetingTopic'];
			$createAMeetingArray['agenda']     = ! empty( $data['agenda'] ) ? $data['agenda'] : "";
			$createAMeetingArray['type']       = ! empty( $data['type'] ) ? $data['type'] : 2; //Scheduled
			$createAMeetingArray['start_time'] = $start_time;
			$createAMeetingArray['timezone']   = $data['timezone'];
			$createAMeetingArray['password']   = ! empty( $data['password'] ) ? $data['password'] : "";
			$createAMeetingArray['duration']   = ! empty( $data['duration'] ) ? $data['duration'] : 60;
			$createAMeetingArray['settings']   = array(
				'meeting_authentication' => ! empty( $data['meeting_authentication'] ) ? true : false,
				'join_before_host'       => ! empty( $data['join_before_host'] ) ? true : false,
				'host_video'             => ! empty( $data['option_host_video'] ) ? true : false,
				'participant_video'      => ! empty( $data['option_participants_video'] ) ? true : false,
				'mute_upon_entry'        => ! empty( $data['option_mute_participants'] ) ? true : false,
				'auto_recording'         => ! empty( $data['option_auto_recording'] ) ? $data['option_auto_recording'] : "none",
				'alternative_hosts'      => isset( $alternative_host_ids ) ? $alternative_host_ids : ""
			);

			$createAMeetingArray = apply_filters( 'vczapi_createAmeeting', $createAMeetingArray );
			if ( ! empty( $createAMeetingArray ) ) {
				return $this->sendRequest( 'users/' . $data['userId'] . '/meetings', $createAMeetingArray, "POST" );
			}

			return false;
		}

		/**
		 * Updating Meeting Info
		 *
		 * @param array $data
		 *
		 * @return array|bool|string|void|WP_Error
		 */
		public function updateMeetingInfo( $data = [] ) {

			$start_time = $data['start_date'] . 'Z';

			$updated = [];

			$updated['topic']      = $data['topic'];
			$updated['agenda']     = "";
			$updated['type']       = 2; //Scheduled
			$updated['start_time'] = $start_time;
			$updated['timezone']   = 'UTC';
			$updated['password']   = ! empty( $data['password'] ) ? $data['password'] : "";
			$updated['duration']   = 45;
			$updated['settings']   = array(
				'meeting_authentication' => true,
				'join_before_host'       => true,
				'host_video'             => ! empty( $data['option_host_video'] ),
				'participant_video'      => ! empty( $data['option_participants_video'] ),
				'mute_upon_entry'        => false,
				'auto_recording'         => "none",
				'alternative_hosts'      => "",
			);

			return $this->sendRequest( 'meetings/' . $data['meeting_id'], $updated, "PATCH" );

		}

		/**
		 * Get a Meeting Info
		 *
		 * @param int $id
		 *
		 * @return array
		 */
		public function getMeetingInfo( $id ) {
			$getMeetingInfoArray = array();
			$getMeetingInfoArray = apply_filters( 'vczapi_getMeetingInfo', $getMeetingInfoArray );

			return $this->sendRequest( 'meetings/' . $id, $getMeetingInfoArray, "GET" );
		}

		/**
		 * @param $meetingid
		 *
		 * @return array|bool|string|WP_Error
		 */
		public function getPastMeetingDetails( $meetingid ) {
			return $this->sendRequest( 'past_meetings/' . $meetingid . '/instances', false, 'GET' );
		}

		/**
		 * Delete A Meeting
		 *
		 * @param $meeting_id int
		 *
		 * @return array|bool|string|WP_Error
		 */
		public function deleteAMeeting( int $meeting_id ) {
			return $this->sendRequest( 'meetings/' . $meeting_id, false, "DELETE" );
		}

		/**
		 * Delete a Webinar
		 *
		 * @param $webinar_id
		 *
		 * @return array|bool|string|WP_Error
		 */
		public function deleteAWebinar( $webinar_id ) {
			return $this->sendRequest( 'webinars/' . $webinar_id, false, "DELETE" );
		}

		/*Functions for management of reports*/
		/**
		 * Get daily account reports by month
		 *
		 * @param $month
		 * @param $year
		 *
		 * @return bool|mixed
		 */
		public function getDailyReport( $month, $year ) {
			$getDailyReportArray          = array();
			$getDailyReportArray['year']  = $year;
			$getDailyReportArray['month'] = $month;
			$getDailyReportArray          = apply_filters( 'vczapi_getDailyReport', $getDailyReportArray );

			return $this->sendRequest( 'report/daily', $getDailyReportArray, "GET" );
		}

		/**
		 * Get ACcount Reports
		 *
		 * @param $zoom_account_from
		 * @param $zoom_account_to
		 *
		 * @return array
		 */
		public function getAccountReport( $zoom_account_from, $zoom_account_to ) {
			$getAccountReportArray              = array();
			$getAccountReportArray['from']      = $zoom_account_from;
			$getAccountReportArray['to']        = $zoom_account_to;
			$getAccountReportArray['page_size'] = 300;
			$getAccountReportArray              = apply_filters( 'vczapi_getAccountReport', $getAccountReportArray );

			return $this->sendRequest( 'report/users', $getAccountReportArray, "GET" );
		}

		public function registerWebinarParticipants( $webinar_id, $first_name, $last_name, $email ) {
			$postData               = array();
			$postData['first_name'] = $first_name;
			$postData['last_name']  = $last_name;
			$postData['email']      = $email;

			return $this->sendRequest( 'webinars/' . $webinar_id . '/registrants', $postData, "POST" );
		}

		/**
		 * List webinars
		 *
		 * @param $userId
		 *
		 * @return bool|mixed
		 */
		public function listWebinar( $userId ) {
			$postData              = array();
			$postData['page_size'] = 300;

			return $this->sendRequest( 'users/' . $userId . '/webinars', $postData, "GET" );
		}

		/**
		 * Create Webinar
		 *
		 * @param $userID
		 * @param array $data
		 *
		 * @return array|bool|string|void|WP_Error
		 */
		public function createAWebinar( $userID, $data = array() ) {
			$postData = apply_filters( 'vczapi_createAwebinar', $data );

			return $this->sendRequest( 'users/' . $userID . '/webinars', $postData, "POST", $this->zoom_api_key, $this->zoom_api_secret );
		}

		/**
		 * Update Webinar
		 *
		 * @param $webinar_id
		 * @param array $data
		 *
		 * @return array|bool|string|void|WP_Error
		 */
		public function updateWebinar( $webinar_id, $data = array() ) {
			$postData = apply_filters( 'vczapi_updateWebinar', $data );

			return $this->sendRequest( 'webinars/' . $webinar_id, $postData, "PATCH" );
		}

		/**
		 * Get Webinar Info
		 *
		 * @param $id
		 *
		 * @return array|bool|string|WP_Error
		 */
		public function getWebinarInfo( $id ) {
			$getMeetingInfoArray = array();

			return $this->sendRequest( 'webinars/' . $id, $getMeetingInfoArray, "GET" );
		}

		/**
		 * List Webinar Participants
		 *
		 * @param $webinarId
		 *
		 * @return bool|mixed
		 */
		public function listWebinarParticipants( $webinarId ) {
			$postData              = array();
			$postData['page_size'] = 300;

			return $this->sendRequest( 'webinars/' . $webinarId . '/registrants', $postData, "GET" );
		}

		/**
		 * Get recording by meeting ID
		 *
		 * @param $meetingId
		 *
		 * @return bool|mixed
		 */
		public function recordingsByMeeting( $meetingId ) {
			return $this->sendRequest( 'meetings/' . $meetingId . '/recordings', false, "GET" );
		}

		/**
		 * Get all recordings by USER ID ( REQUIRES PRO USER )
		 *
		 * @param $host_id
		 * @param $data array
		 *
		 * @return bool|mixed
		 */
		public function listRecording( $host_id, $data = array() ) {
			$from = date( 'Y-m-d', strtotime( '-1 year', time() ) );
			$to   = date( 'Y-m-d' );

			$data['from'] = ! empty( $data['from'] ) ? $data['from'] : $from;
			$data['to']   = ! empty( $data['to'] ) ? $data['to'] : $to;
			$data         = apply_filters( 'vczapi_listRecording', $data );

			return $this->sendRequest( 'users/' . $host_id . '/recordings', $data, "GET" );
		}
	}

	function zoom_conference() {
		return Zoom_Video_Conferencing_Api::instance();
	}

	zoom_conference();
}
