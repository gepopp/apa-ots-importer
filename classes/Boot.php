<?php

namespace ApaOtsImporter;


class Boot
{
	public function __invoke()
	{
		( new AdminPages() )();
		( new Enqueue() )();
		( new Options() )();
		( new Import() )();



		add_action( 'wp_ajax_load_ots', function () {
			( new Api() )->search();
		} );

	}
}