<?php
/*
 * Plugin Name:       APA OTS Importer
 * Plugin URI:        https://github.com/gepopp/apa-ots-importer.git
 * Description:       Uses the APA OTS API to list, search and import press releases as post.
 * Version:           0.0.1
 * Requires at least: 5.0
 * Requires PHP:      7.0
 * Author:            Gerhard Popp
 * Author URI:        https://poppgerhard.at/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       apa-ots-importer
 * Domain Path:       /languages
 */

add_action( 'admin_menu', function () {
	add_menu_page( 'APA OTS Importer',
		'APA OTS Importer',
		'manage_options',
		'apa-ots-importer',
		'apa_ots_importer_menu_page_content',
		'dashicons-welcome-widgets-menus');


	add_submenu_page( 'apa-ots-importer',
		'API Schlüssel',
		'API Schlüssel',
		'manage_options', 'apa-ots-importer-key',
		'apa_ots_key_page' );

} );

function apa_ots_importer_menu_page_content()
{
	global $wpdb;
	$importiert = $wpdb->get_col( 'SELECT meta_value FROM wp_postmeta WHERE meta_key = "ots_id"' );
	ob_start();
	?>
    <style>
        .image-placeholder {
            width: 150px;
            aspect-ratio: 16 / 9;
            background-color: #efefef;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .placeholder {
            width: 100%;
            aspect-ratio: 16 / 9;
            background-color: #efefef;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
    <script>
        const imported = <?php echo json_encode( $importiert ) ?>;
    </script>

    <div class="wrap">
        <h1>Neueste OTS Meldungen</h1>
        <div x-data="{
        'search': '',
        'ots': [],
        load_ots(){
            axios.post(ajaxurl, Qs.stringify({
                action: 'load_ots',
                search: this.search
            })).then((rsp) => this.ots = rsp.data.ergebnisse );
        },
        importing: false,
        import_ots(id){
            this.importing = true;
            axios.post(ajaxurl, Qs.stringify({
                action: 'import_ots',
                id: id
            })).then((rsp) => {
                this.imported.push(id);
                this.importing = false;
            });
        },
        imported: imported
    }" x-init="load_ots()">
            <div style="padding-bottom: 20px;">
                <p class="search-box">
                    <label class="screen-reader-text" for="post-search-input">OTS Meldungen durchsuchen:</label>
                    <input type="search" id="post-search-input" x-model="search">
                    <input type="button" id="search-submit" class="button" value="suchen" x-on:click="load_ots()">
                </p>
            </div>
            <div>
                <table class="wp-list-table widefat fixed striped table-view-list posts">
                    <thead>
                    <tr>
                        <th scope="col" id="title" class="manage-column">
                            Bilder
                        </th>
                        <th scope="col" id="author" class="manage-column">Aussendung</th>
                        <th scope="col" id="author" class="manage-column">Datum</th>
                        <th scope="col" id="author" class="manage-column">Herausgeber</th>
                        <th scope="col" id="author" class="manage-column">Importieren</th>
                    </tr>
                    </thead>

                    <template x-if="!ots.length">
                        <tr>
                            <td colspan="5">
                                <div class="placeholder">
                                    <p>Kein Suchergebnis, ändere die Suche</p>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template x-for="ots_meldung in ots">
                        <tr>
                            <td>
                                <template x-if="ots_meldung.ANHANG != null">
                                    <img :src="ots_meldung.ANHANG[0]['VORSCHAU']['thumb']" style="width: 150px;"/>
                                </template>
                                <template x-if="ots_meldung.ANHANG == null">
                                    <div class="image-placeholder">
                                        <p>Kein Bild</p>
                                    </div>
                                </template>
                            </td>
                            <td>
                                <h3 x-text="ots_meldung.TITEL"></h3>
                                <p x-text="ots_meldung.LEAD"></p>
                            </td>
                            <td x-text="ots_meldung.DATUM"></td>
                            <td x-text="ots_meldung.EMITTENT"></td>
                            <td>
                                <template x-if="!importing && !imported.includes(ots_meldung.SCHLUESSEL)">
                                    <input type="button" id="search-submit" class="button" value="importieren" x-on:click="import_ots( ots_meldung.SCHLUESSEL )">
                                </template>
                                <template x-if="imported.includes(ots_meldung.SCHLUESSEL)">
                                    <p>Bereits importiert</p>
                                </template>
                            </td>
                        </tr>
                    </template>
                </table>
            </div>
        </div>
    </div>

	<?php
	echo ob_get_clean();
}

add_action( 'wp_ajax_import_ots', function () {

	$api_key = get_option( 'apa_ots_key' );
	$ots_id  = sanitize_text_field( $_POST['id'] );

	$query_string = sprintf( 'https://www.ots.at/api/aussendung?app=%s&schluessel=%s&markup=1', $api_key, $ots_id );
	$results      = wp_remote_get( $query_string );

	$request_body = wp_remote_retrieve_body( $results );
	$request_body = json_decode( $request_body, ARRAY_A );

	$press_release = $request_body['ergebnisse'][0];

	$post = wp_insert_post( [
		'post_author'  => get_current_user_id(),
		'post_title'   => $press_release['TITEL'],
		'post_excerpt' => $press_release['UTL'],
		'post_content' => $press_release['INHALT'] . '<hr>' . $press_release['RUECKFRAGEHINWEIS'] . '<hr>' . $press_release['EMITTENT'],
		'post_status'  => 'draft',
	] );

	add_post_meta( $post, 'ots_id', $ots_id );

	if ( $press_release['ANHANG'] ) {
		add_image( $press_release['ANHANG'][0], $post );
	}


	echo $post;
	wp_die();

} );


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


add_action( 'wp_ajax_load_ots', function () {
	$api_key = get_option( 'apa_ots_key' );

	$search = sanitize_text_field( $_POST['search'] ) ?? bloginfo( 'name' );

	$query_string = sprintf( 'https://www.ots.at/api/liste?app=%s&query=(%s)&inhalt=alle&von=1557362684&anz=50&sourcetype=OTS&format=json&markup=1', $api_key, $search );

	$result = wp_remote_get( $query_string );

	echo wp_remote_retrieve_body( $result );

	wp_die();
} );


function apa_ots_key_page()
{
	ob_start();
	?>
    <h1>OTS API Schlüssel</h1>
    <form method="post" action="<?php echo admin_url( 'admin-post.php' ) ?>">
        <input type="hidden" name="action" value="update_apa_ots_key">
		<?php echo wp_nonce_field( 'update_apa_ots_key', 'update_apa_ots_key' ) ?>
        <table role="presentation" class="form-table">
            <tr>
                <th for="ots_api_key" scope="row">OTS API Schlüssel</th>
                <td>
                    <input type="password" value="<?php echo get_option( 'apa_ots_key' ) ?>" name="ots_api_key" class="regular-text" required/>
                </td>
            </tr>
        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Änderungen speichern"></p>
    </form>
	<?php
	echo ob_get_clean();
}


add_action( 'admin_post_update_apa_ots_key', function () {

	if ( ! wp_verify_nonce( $_POST['update_apa_ots_key'], 'update_apa_ots_key' ) ) {
		wp_die( 403 );
	}

	update_option( 'apa_ots_key', $_POST['ots_api_key'] );
	wp_safe_redirect( admin_url( 'admin.php?page=apa-ots-importer-key' ) );
} );


add_action( 'admin_enqueue_scripts', function () {
	$alpine = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
	wp_register_script( 'ots-qs-js', 'https://cdnjs.cloudflare.com/ajax/libs/qs/6.11.2/qs.min.js', [], false, false );
	wp_register_script( 'ots-axios-js', 'https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js', [], false, false );
	wp_register_script( 'ots-alpine-js', $alpine, [ 'ots-axios-js', 'ots-qs-js' ], false, false );
	wp_enqueue_script( 'ots-qs-js' );
	wp_enqueue_script( 'ots-axios-js' );
	wp_enqueue_script( 'ots-alpine-js' );
} );