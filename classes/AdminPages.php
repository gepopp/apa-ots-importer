<?php

namespace ApaOtsImporter;

class AdminPages
{
	public function __invoke()
	{
		add_action( 'admin_menu', function () {
			add_menu_page( 'APA OTS Importer',
				'APA OTS Importer',
				'edit_posts',
				'apa-ots-importer',
				[ $this, 'apa_ots_importer_menu_page_content' ],
				'dashicons-welcome-widgets-menus' );


			add_submenu_page( 'apa-ots-importer',
				'Einstellungen',
				'Einstellungen',
				'edit_posts',
				'apa-ots-importer-key',
				[ $this, 'apa_ots_key_page' ] );

		} );
	}


	function apa_ots_importer_menu_page_content()
	{
		include_once APA_OTS_PLUGIN_DIR . '/templates/importer.php';
		echo ob_get_clean();
	}

	function apa_ots_key_page()
	{
		ob_start();
		include_once APA_OTS_PLUGIN_DIR . '/templates/options-page.php';
		echo ob_get_clean();
	}

}