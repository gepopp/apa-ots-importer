<?php

namespace ApaOtsImporter;

class Api
{

	public $api_key;


	public $endpoint;


	public $amount = 100;


	public $search;

	public function __construct( $endpoint = null )
	{
		$this->api_key = get_option( 'apa_ots_key' );
		$this->setEndpoint( $endpoint ?? sprintf( 'https://www.ots.at/api/liste?app=%s&sourcetype=OTS&format=json&markup=1&anz=%s', $this->api_key, $this->amount ));
	}

	public function get_release()
	{
		$results = wp_remote_get( $this->endpoint );
		$request_body = wp_remote_retrieve_body( $results );
		$request_body = json_decode( $request_body, ARRAY_A );

		return $request_body['ergebnisse'][0];
	}


	public function search()
	{
		$this->build_query_string();
		$result = wp_remote_get( $this->endpoint );
		$body   = json_decode( wp_remote_retrieve_body( $result ), ARRAY_A );


		if ( array_key_exists( 'error', $body ) ) {
			wp_send_json_error( [ 'message' => $body['error'] ], 404 );
		}

		$filtered = [];

		foreach ( $body['ergebnisse'] as $result ) {
			$filtered[ serialize( $result ) ] = $result;
		}

		$filtered = array_values( $filtered );

		wp_send_json( $filtered );
	}

	public function build_query_string()
	{
		$search = sanitize_text_field( $_POST['search'] ?? bloginfo( 'name' ) );
		$this->add_to_query( 'query', $search );

		$after = sanitize_text_field( $_POST['after'] ?? '' );
		if ( ! empty( $after ) ) {
			$time = strtotime( $after );
			$this->add_to_query( 'von', $time );
		}

		$before = sanitize_text_field( $_POST['before'] );
		if ( ! empty( $before ) ) {
			$time = strtotime( $before );
			$this->add_to_query( 'bis', $time );
		}

		if ( rest_sanitize_boolean( $_POST['picture'] ?? false ) ) {
			$this->add_to_query( 'inhalt', 'bilder' );
		}

		if ( ! empty( $channel = sanitize_text_field( $_POST['channel'] ?? '' ) ) ) {
			$this->add_to_query( 'channel', $channel );
		}

	}


	public function add_to_query( $arg, $value )
	{
		$this->endpoint = add_query_arg( $arg, $value, $this->endpoint );
	}

	public function setEndpoint( $endppoint )
	{
		$this->endpoint = $endppoint;
	}
}