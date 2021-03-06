<?php
/********************************************************************
 * Copyright (C) 2020 Darko Gjorgjijoski (https://codeverve.com)
 *
 * This file is part of WP Vimeo Videos
 *
 * WP Vimeo Videos is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * WP Vimeo Videos is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WP Vimeo Videos. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

/**
 * Class WP_DGV_Api_Helper
 *
 * Responsible for communicating with the Vimeo API
 *
 * @license GPLv2
 * @copyright Darko Gjorgjijoski <info@codeverve.com>
 * @since 1.0.0
 */
class  WP_DGV_Api_Helper {

	/**
	 * Is the api connected?
	 * @var null
	 */
	public $is_connected = null;

	/**
	 * Is authenticated connection?
	 *
	 * Authenticated connections are required.
	 *
	 * @since 1.5.0
	 *
	 * @var bool
	 */
	public $is_authenticated_connection = true;

	/**
	 * Return the vimeo instance
	 * @var null|\Vimeo\Vimeo
	 */
	public $api = null;

	/**
	 * List of the required scopes
	 * @var array
	 */
	public $scopes_required = array(
		'create',
		'interact',
		'private',
		'edit',
		'upload',
		'delete'
	);

	/**
	 * List of the missing scopes
	 * @var array
	 */
	public $scopes_missing = array();

	/**
	 * List of scopes tied to the authenticated user
	 * @var array
	 */
	public $scopes = array();

	/**
	 * The name of the user
	 * @var string
	 */
	public $user_name = '';

	/**
	 * The vimeo user uri
	 * @var string
	 */
	public $user_uri = '';

	/**
	 * The vimeo user link
	 * @var string
	 */
	public $user_link = '';

	/**
	 * Returns the user type
	 * @var string
	 */
	public $user_type = '';

	/**
	 * The upload quota
	 * @var array
	 */
	public $upload_quota = [];

	/**
	 * The headers
	 * @var array
	 */
	public $headers = [];

	/**
	 * The oAuth APP name
	 * @var string
	 */
	public $app_name = '';

	/**
	 * The oAuth APP URI
	 * @var string
	 */
	public $app_uri = '';

	/**
	 * Keeps the error if any.
	 * @var null
	 */
	public $error = null;

	/**
	 * The settings helper
	 * @var WP_DGV_Settings_Helper
	 */
	public $settings_helper;

	/**
	 * Cache key name when caching the vimeo user data
	 * @var string
	 */
	const CACHE_KEY = 'wvv_account_data';

	/**
	 * Cache time for the vimeo user data
	 * @var int
	 */
	private $cache_time = 120; // two minutes

	/**
	 * WP_DGV_Api_Helper constructor.
	 */
	public function __construct() {

		$this->settings_helper = new WP_DGV_Settings_Helper();

		$this->connect();
	}

	/**
	 * Connect to vimeo
	 *
	 * @param bool $flush_cache
	 */
	public function connect( $flush_cache = false ) {

		$client_id     = $this->settings_helper->get( 'dgv_client_id' );
		$client_secret = $this->settings_helper->get( 'dgv_client_secret' );
		$access_token  = $this->settings_helper->get( 'dgv_access_token' );
		$error         = null;

		if ( empty( $client_id ) || strlen( trim( $client_id ) ) === 0 ) {
			$error = __( 'Client ID is missing', 'wp-vimeo-videos' );
		} else if ( empty( $client_secret ) || strlen( trim( $client_secret ) ) === 0 ) {
			$error = __( 'Client Secret is missing', 'wp-vimeo-videos' );
		} else if ( empty( $access_token ) || strlen( trim( $access_token ) ) === 0 ) {
			$error = __( 'Access Token is missing', 'wp-vimeo-videos' );
		}

		if ( ! class_exists( '\Vimeo\Vimeo' ) ) {
			$error = __( 'Vimeo not loaded', 'wp-vimeo-videos' );
		}

		$this->error = $error;
		$this->api   = new \Vimeo\Vimeo( $client_id, $client_secret, $access_token );

		// Maybe flush cache?
		if ( $flush_cache ) {
			self::flush_cache();
		}

		// Cache the data
		$data = get_transient( self::CACHE_KEY );
		if ( false === $data ) {
			try {
				$data = $this->api->request( '/oauth/verify', [], 'GET' );
				set_transient( self::CACHE_KEY, $data, $this->cache_time );
			} catch ( \Exception $e ) {
				$data        = null;
				$this->error = $e->getMessage();
			}
		}

		// Verify the connection
		if ( ! is_null( $data ) && is_array( $data ) && isset( $data['status'] ) ) {
			$status = $data['status'];
			if ( $status === 200 ) {
				$this->is_connected = true;
				// If user object is not present assume this is unauthenticated connection.
				if ( ! isset( $data['body']['user'] ) ) {
					$this->is_authenticated_connection = false;
				}
			} else {
				$this->is_connected                = false;
				$this->is_authenticated_connection = false;
				if ( isset( $data['body']['developer_message'] ) ) {
					$this->error = $data['body']['developer_message'];
				}
			}
		} else {
			// Error is set in exception method.
			$this->is_connected = false;
		}

		if ( $this->is_connected ) {
			$this->user_name    = isset( $data['body']['user']['name'] ) ? $data['body']['user']['name'] : '';
			$this->user_uri     = isset( $data['body']['user']['uri'] ) ? $data['body']['user']['uri'] : '';
			$this->user_link    = isset( $data['body']['user']['link'] ) ? $data['body']['user']['link'] : '';
			$this->user_type    = isset( $data['body']['user']['account'] ) ? $data['body']['user']['account'] : '';
			$this->app_name     = isset( $data['body']['app']['name'] ) ? $data['body']['app']['name'] : '';
			$this->app_uri      = isset( $data['body']['app']['uri'] ) ? $data['body']['app']['uri'] : '';
			$_scopes            = isset( $data['body']['scope'] ) ? $data['body']['scope'] : '';
			$this->headers      = isset( $data['headers'] ) ? $data['headers'] : array();
			$this->upload_quota = isset( $data['body']['user']['upload_quota'] ) ? $data['body']['user']['upload_quota'] : array();
			if ( ! empty( $_scopes ) ) {
				$this->scopes         = explode( ' ', $_scopes );
				$this->scopes_missing = array_diff( $this->scopes_required, $this->scopes );
			}
		} else {
			$logger = new WP_DGV_Logger();
			$logtag = 'DGV-VIMEO-CONNECTION';
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$logger->log( sprintf( 'Error connecting to Vimeo: %s', $this->error ), $logtag );
			}
		}
	}

	/**
	 * Used to detect problems with active connection
	 *
	 * @return array
	 * @since 1.5.0
	 */
	public function find_problems() {
		$problems = array();

		// Check connection, if wrong bail immediately.
		if ( ! $this->is_authenticated_connection ) {
			array_push( $problems, array(
				'code' => 'unauthenticated',
				'info' => __( 'Your Access Token is of type "Unauthenticated". This will prevent normal operation of the plugin.', 'wp-vimeo-videos' ),
				"fix"  => sprintf( __( 'To fix the issue, go to Vimeo Developer Portal, select your application and remove your old Access Token. Generate new "Auhtneticated" Access Token and select the %s scopes. Once done, set the new Access Token in the Settings screen and Purge Cache.', 'wp-vimeo-videos' ), implode( ', ', $this->scopes_required ) )
			) );

			return $problems;
		}

		// Continue with scopes.
		if ( ! $this->can_upload() ) {
			array_push( $problems, array(
				'code' => 'cant_upload',
				'info' => __( 'Your Access Token is missing "Upload" scope. This will prevent uploading new Videos to Vimeo.', 'wp-vimeo-videos' ),
				"fix"  => sprintf( __( 'To fix the issue, go to Vimeo Developer Portal, select your application and remove your old Access Token. Generate new "Auhtneticated" Access Token and select the %s scopes. Once done, set the new Access Token in the Settings screen and Purge Cache.', 'wp-vimeo-videos' ), implode( ', ', $this->scopes_required ) )
			) );
		}
		if ( ! $this->can_edit() ) {
			array_push( $problems, array(
				'code' => 'cant_edit',
				'info' => __( 'Your Access Token is missing "Edit" scope. This will prevent editing Videos from the edit screen.', 'wp-vimeo-videos' ),
				"fix"  => sprintf( __( 'To fix the issue, go to Vimeo Developer Portal, select your application and remove your old Access Token. Generate new "Auhtneticated" Access Token and select the %s scopes. Once done, set the new Access Token in the Settings screen and Purge Cache.', 'wp-vimeo-videos' ), implode( ', ', $this->scopes_required ) )
			) );
		}
		if ( ! $this->can_delete() ) {
			array_push( $problems, array(
				'code' => 'cant_delete',
				'info' => __( 'Your Access Token is missing "Delete" scope. This will prevent deleting Videos from the admin dashboard.' ),
				"fix"  => sprintf( __( 'To fix the issue, go to Vimeo Developer Portal, select your application and remove your old Access Token. Generate new "Auhtneticated" Access Token and select the %s scopes. Once done, set the new Access Token in the Settings screen and Purge Cache.', 'wp-vimeo-videos' ), implode( ', ', $this->scopes_required ) )
			) );
		}

		return $problems;
	}

	/**
	 * Check if the current authenticated user can create.
	 * @return bool
	 */
	public function can_create() {
		return in_array( 'create', $this->scopes );
	}

	/**
	 * Check if the current authenticated user can edit.
	 * @return bool
	 */
	public function can_edit() {
		return in_array( 'edit', $this->scopes );
	}

	/**
	 * Check if the current authenticated user can upload.
	 * @return bool
	 */
	public function can_upload() {
		return in_array( 'upload', $this->scopes );
	}

	/**
	 * Check if the current authenticated user can delete.
	 * @return bool
	 */
	public function can_delete() {
		return in_array( 'delete', $this->scopes );
	}

	/**
	 * Return list of videos
	 * @url https://developer.vimeo.com/api/reference/videos#get_videos
	 *
	 * @param array $params
	 * @param bool $try_all
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 */
	public function get_uploaded_videos( $params = array(), $try_all = false ) {
		$videos = array();
		$query  = array(
			'fields'   => 'uri,name,description',
			'filter'   => 'embeddable',
			'per_page' => 100,
		);
		$query  = wp_parse_args( $params, $query );

		$query['page'] = 1;
		$response      = $this->api->request( '/me/videos', $query, 'GET' );

		if ( isset( $response['status'] ) && $response['status'] === 200 ) {
			$videos = array_merge( $videos, $response['body']['data'] );
			if ( $try_all ) {
				$query_params = array();
				if ( isset( $response['body']['paging']['last'] ) ) {
					wp_parse_str( $response['body']['paging']['last'], $query_params );
				}
				$last_page = isset( $query_params['page'] ) ? $query_params['page'] : 1;
				if ( isset( $response['headers']['X-RateLimit-Remaining'] ) && $response['headers']['X-RateLimit-Remaining'] > 5 && $last_page > 1 ) {
					for ( $i = 2; $i <= $last_page; $i ++ ) {
						$query['page'] = $i;
						$response      = $this->api->request( '/me/videos', $query, 'GET' );
						if ( isset( $response['status'] ) && $response['status'] === 200 ) {
							$videos = array_merge( $videos, $response['body']['data'] );
							if ( $response['headers']['X-RateLimit-Remaining'] < 5 ) {
								break;
							}
						}
					}
				}
			}
		} else {
			$videos = array();
		}

		return $videos;
	}

	/**
	 * Uploads/streams the video to vimeo
	 *
	 * @param $file_path
	 * @param array $params
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 * @throws \Vimeo\Exceptions\VimeoUploadException
	 * @throws \Exception
	 */
	public function upload( $file_path, $params ) {
		$response = $this->api->upload( $file_path, $params );

		do_action( 'dgv_after_upload', $response, $this->api );

		return array(
			'params'   => $params,
			'response' => $response,
		);
	}

	/**
	 * Upload via pull method. Only url to the file is required.
	 *
	 * @param string $file_url
	 * @param array $params
	 *
	 * @return array
	 * @throws Exception
	 */
	public function upload_pull( $file_url, $params ) {
		$params   = array_merge( array( 'upload' => array( 'approach' => 'pull', 'link' => $file_url ) ), $params );
		$response = $this->api->request( '/me/videos', $params, 'POST' );

		do_action( 'dgv_after_upload', $response, $this->api );

		return array(
			'params'   => $params,
			'response' => $response
		);
	}


	/**
	 * Deletes vimeo video from their api
	 *
	 * @param $uri
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 */
	public function delete( $uri ) {
		$response = $this->api->request( $uri, [], 'DELETE' );

		return $response;
	}

	/**
	 * Set the embed privacy
	 *
	 * @param $uri
	 * @param $privacy
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 */
	public function set_embed_privacy( $uri, $privacy ) {
		$response = $this->api->request( $uri, array(
			'privacy' => array(
				'embed' => $privacy
			)
		), 'PATCH' );

		return $response;
	}

	/**
	 * Add domain to embed whitelist for specific video
	 *
	 * @param $uri
	 * @param $domain
	 *
	 * @return array
	 * @throws \Vimeo\Exceptions\VimeoRequestException
	 */
	public function whitelist_domain_add( $uri, $domain ) {
		$request_uri = "{$uri}/privacy/domains/{$domain}";
		$response    = $this->api->request( $request_uri, [], 'PUT' );

		return $response;
	}

	/**
	 * Sync videos with vimeo api
	 * @throws Exception
	 */
	public function sync() {
		$videos = $this->get_uploaded_videos( array( 'per_page' => 100 ), true );

		if ( ! empty( $videos ) ) {

		}

	}


	/**
	 * Flushes user data cache
	 */
	public static function flush_cache() {
		delete_transient( self::CACHE_KEY );
	}
}