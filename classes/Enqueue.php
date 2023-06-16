<?php

namespace ApaOtsImporter;

class Enqueue
{
	public function __invoke()
	{
		add_action( 'admin_enqueue_scripts', function () {
			wp_register_script( 'ots-apa-js', APA_OTS_PLUGIN_URL . 'assets/js/apa-ots-admin.js', [], time(), false );
			wp_enqueue_script( 'ots-apa-js' );
		});
	}
}