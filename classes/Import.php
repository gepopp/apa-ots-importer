<?php

namespace ApaOtsImporter;

class Import
{

	public function __invoke()
	{
		add_action( 'wp_ajax_import_ots', function () {

			$ots_id = sanitize_text_field( $_POST['id'] );

			if ( empty( $ots_id ) ) {
				return;
			}

			$query_string  = sprintf( 'https://www.ots.at/api/aussendung?app=%s&schluessel=%s&markup=1', $api_key, $ots_id );
			$api           = new Api( $query_string );
			$press_release = $api->get_release();


			$post = wp_insert_post( [
				'post_author'   => get_option('apa_ots_default_author') ?? get_current_user_id(),
				'post_title'    => $press_release['TITEL'],
				'post_excerpt'  => $press_release['UTL'],
				'post_content'  => $press_release['INHALT'] . '<hr>' . $press_release['RUECKFRAGEHINWEIS'] . '<hr>' . $press_release['EMITTENT'],
				'post_status'   => 'draft',
				'post_category' => [ get_option( 'apa_ots_default_category' ) ],
			] );

			add_post_meta( $post, 'ots_id', $ots_id );

			if ( $press_release['ANHANG'] ) {
				$this->add_image( $press_release['ANHANG'][0], $post );
			}

			echo $post;
			wp_die();

		} );

	}


	function add_image( $image, $post_id )
	{
		$image_url = $image['VORSCHAU']['full'];

		$upload_dir = wp_upload_dir();

		$image_data = file_get_contents( $image_url );

		$filename = basename( $image_url );

		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

		file_put_contents( $file, $image_data );

		$wp_filetype = wp_check_filetype( $filename, null );

		$attachment = [
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_parent'    => $post_id,
		];


		$attach_id = wp_insert_attachment( $attachment, $file );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		update_post_meta( $post_id, '_thumbnail_id', $attach_id );
	}


}