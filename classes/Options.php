<?php

namespace ApaOtsImporter;

class Options
{


	public function __invoke()
	{
		add_action( 'admin_post_update_apa_ots_key', function () {

			$this->verify_nonce()
			     ->update_api_key()
			     ->update_default_category()
			     ->update_default_author();

			set_transient('ots_settings_errors', get_settings_errors(), 30);

			wp_safe_redirect( admin_url( 'admin.php?page=apa-ots-importer-key' ) );

		} );
	}


	public function verify_nonce()
	{

		$nonce = sanitize_text_field( $_POST['update_apa_ots_key'] );

		if ( ! wp_verify_nonce( $nonce, 'update_apa_ots_key' ) ) {
			wp_die( 403 );
		}

		return $this;
	}


	public function update_api_key()
	{
		$key = sanitize_text_field( $_POST['ots_api_key'] );

		if(empty($key)){
			add_settings_error('ots_api_default_category', 'error', 'Bitte geben Sie einen gültigen API Schlüssel ein.');
		}

		update_option( 'apa_ots_key', $key );

		return $this;
	}


	public function update_default_category()
	{

		$category_id = sanitize_text_field( $_POST['ots_api_default_category'] ?? null );

		if(!empty($category_id)){
			update_option( 'apa_ots_default_category', (int) $category_id );
		}

		return $this;
	}


	public function update_default_author()
	{
		$author_id = sanitize_text_field( $_POST['ots_api_default_author'] ?? null );

		if(!empty($author_id)){
			update_option( 'apa_ots_default_author', (int) $author_id );
		}

		return $this;
	}

}