<?php
/*
 * Plugin Name:       APA OTS Importer
 * Plugin URI:        https://github.com/gepopp/apa-ots-importer.git
 * Description:       Uses the APA OTS API to list, search and import press releases as post.
 * Version:           0.1.1
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
define('APA_OTS_PLUGIN_DIR', plugin_dir_path(__FILE__ ));
define('APA_OTS_PLUGIN_URL', acf_plugin_dir_url(__FILE__ ));


require 'vendor/autoload.php';
$plugin = new \ApaOtsImporter\Boot();
$plugin();










