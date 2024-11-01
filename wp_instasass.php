<?php 

/*
Plugin Name: WP instasass
Plugin URI: https://instasass.lc.lv
Description: A plugin that makes our fantasy a reality. It makes scss usable as if it were css. It is an on the fly compiler based on the c++ library.
Version: 1.0.5
Author: SIA FIXCMS
*/

// Security reasons
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Attempt to enable remote file open
ini_set("allow_url_fopen", 1);

// Tell the user what to do
if( !ini_get('allow_url_fopen') ) {
    die('In your PHP.INI - allow_url_fopen is disabled. The plug-in API depends on this - please enable it!');
}

// Load the autoloader
require_once(plugin_dir_path( __FILE__ ) . 'vendor/autoload.php');

// Use the appropriate class
use Instasass\instasassapi;

// We need the settings
$aSettings = get_option( 'fixcms_options' );

// Do only if we are in front end
if( !is_admin() ) {

	// Initialize class and connect to the service
	try {
		$oInstaSass = new instasassapi($aSettings['fixcms_field_apikey']);
	} catch( Exception $e ) {
		// Do not kill the whole thing
	}

	// Set the class variable global
	global $oInstaSass;

	// See what option has been chosen
	if( isset($aSettings['fixcms_field_output_implementation']) ) {
		switch($aSettings['fixcms_field_output_implementation']) {
			case 'local_links':

				// Enque scripts autoloading from a folder
				if ($handle = opendir(get_stylesheet_directory() . '/scss/')) {
				    while (false !== ($sStyleFile = readdir($handle))) {

				    	// Check if the file is not . or .. and check if it is a scss file ignore the rest
				    	if( !in_array($sStyleFile, array('.', '..')) && preg_match('/\.scss/', $sStyleFile) ) {

				    		// Get the resulting css
				    		$sCss = $oInstaSass->sass2cssSaved(get_stylesheet_directory() . '/scss/' . $sStyleFile, true, false, $aSettings['fixcms_field_compilation_mode'], $aSettings['fixcms_field_go_around_cdn']);

				    		// Create the file
				    		file_put_contents(get_stylesheet_directory() . '/scss/css/' . str_replace('.scss', '.css', $sStyleFile), $sCss);

				    		// Ask the API kindly for a css and enque it in wordpress
				    		if( empty($aSettings['fixcms_field_dont_enque_scripts']) ) {
				    			add_action('wp_head', function() use($sStyleFile){
				    				echo '<link rel="stylesheet" id="' . str_replace('.scss', '', $sStyleFile) . '"  href="' . get_template_directory_uri() . '/scss/css/' . str_replace('.scss', '.css', $sStyleFile) . '" type="text/css" media="all" />';
				        		}, PHP_INT_MAX);
				        	}
				    	}
				    }

				    // Close the dir handler
				    closedir($handle);
				}

				break;

			case 'inline':

				// Enque scripts autoloading from a folder
				$sResultingCss = '';
				if ($handle = opendir(get_stylesheet_directory() . '/scss/')) {
				    while (false !== ($sStyleFile = readdir($handle))) {

				    	// Check if the file is not . or .. and check if it is a scss file ignore the rest
				    	if( !in_array($sStyleFile, array('.', '..')) && preg_match('/\.scss/', $sStyleFile) ) {

				    		// Get the resulting css
				    		$sCss .= $oInstaSass->sass2cssSaved(get_stylesheet_directory() . '/scss/' . $sStyleFile, true, false, $aSettings['fixcms_field_compilation_mode'], $aSettings['fixcms_field_go_around_cdn']);
				    		$sResultingCss .= $sCss;

				    		// Save them if so required
				    		if( $aSettings['fixcms_field_save_css_copy_localy'] ) {

				    			// Create the file
				    			file_put_contents(get_stylesheet_directory() . '/scss/css/' . str_replace('.scss', '.css', $sStyleFile), $sCss);

				    		}

				    	}
				    }

				    // Close the dir handler
				    closedir($handle);
				}

				// Add them in the end of the head
				add_action('wp_head', function(){
					global $sResultingCss;
					echo '<style type="text/css">' . $sResultingCss . '</style>';
				}, 1000);

				break;

			default:

				// Enque scripts autoloading from a folder
				if ($handle = opendir(get_stylesheet_directory() . '/scss/')) {
				    while (false !== ($sStyleFile = readdir($handle))) {

				    	// Check if the file is not . or .. and check if it is a scss file ignore the rest
				    	if( !in_array($sStyleFile, array('.', '..')) && preg_match('/\.scss/', $sStyleFile) ) {

				    		// Save them if so required
				    		if( $aSettings['fixcms_field_save_css_copy_localy'] ) {

				    			// Get the resulting css
					    		$sCss .= $oInstaSass->sass2cssSaved(get_stylesheet_directory() . '/scss/' . $sStyleFile, true, false, $aSettings['fixcms_field_compilation_mode'], $aSettings['fixcms_field_go_around_cdn']);

				    			// Create the file
				    			file_put_contents(get_stylesheet_directory() . '/scss/css/' . str_replace('.scss', '.css', $sStyleFile), $sCss);

				    		}

				    		// Ask the API kindly for a css and enque it in wordpress
				    		if( empty($aSettings['fixcms_field_dont_enque_scripts']) ) {
				    			add_action('wp_head', function() use($sStyleFile){
				    				global $oInstaSass, $aSettings;
				    				echo '<link rel="stylesheet" id="' . str_replace('.scss', '', $sStyleFile) . '"  href="' . $oInstaSass->sass2cssSaved(get_stylesheet_directory() . '/scss/' . $sStyleFile, false, false, $aSettings['fixcms_field_compilation_mode'], $aSettings['fixcms_field_go_around_cdn']) . '" type="text/css" media="all" />';
				        		}, PHP_INT_MAX);
				        	}
				    	}
				    }

				    // Close the dir handler
				    closedir($handle);
				}

				break;
		}
	}
}

if( is_admin() ) {

	function instasass_styles() {
		return '<style>
			input#fixcms_field_apikey {
				padding: 5px;
				width: 410px;
			}
			.red {
				color: red;
			}
			.green {
				color: green;
			}
			.form-table {
				background-color: #fff;
				padding: 10px;
			}
			.form-table td, .form-table th {
				padding: 10px;
			}
		</style>';
	}

	function instasass_javascript() {
		return "<script>
			jQuery(document).ready(function(){

				// Select all in the key field
				jQuery('#fixcms_field_apikey').bind('focus', function(){
					jQuery(this).select();
				});

			});
		</script>";
	}

	/**
	* Init the settings page
	*/
	function fixcms_settings_init() {

		// Go global for settings
		global $aSettings;

		// In case it didn't do it in activation
		activate_wp_instasass();

		// register a new setting for "fixcms" page
		register_setting('fixcms', 'fixcms_options');

		// register a new section in the "fixcms" page
		add_settings_section(
			'fixcms_section_developers',
			__( 'Please get the API key from <a href="https://instasass.lc.lv" target="_blank">https://instasass.lc.lv</a>', 'fixcms' ),
			function( $args ){

				// Do the media includes
				echo instasass_styles();
				echo instasass_javascript();

				// Info
				echo '<p id="' . esc_attr( $args['id'] ) . '">' . esc_html_e( 'The first 25 unique requests are free so you can set up. After that please choose the plan you would like to use. PRO or UNLIMITED. We recommend UNLIMITED.', 'fixcms' ) . '</p>';

				echo 'Put all your scss files in <strong>' . get_stylesheet_directory() . '/scss/</strong>';

				// Delimiter
				echo " | ";

				// Info
				echo 'All your saved css files will be available in <strong>' . get_stylesheet_directory() . '/scss/css/</strong>';

				echo '<br />';
				
				// Get scss file count
				$iSCSSFileCount = 0;
				$aFiles = glob(get_stylesheet_directory() . '/scss/' . "*.scss");
				if ($aFiles){
					$iSCSSFileCount = count($aFiles);
				}

				// Tell the user that there are too many files!
				if( $iSCSSFileCount > 3 ) {
					echo '<span class="red">' . sprintf('You have %s scss files included. You should consider combining them into less files.', $iSCSSFileCount) . '</span>';
				} else {
					echo '<span class="green">' . sprintf('You have %s scss files included. All seams fine!', $iSCSSFileCount) . '</span>';
				}

				// Delimiter
				echo " | ";

				// Get css file count
				$iCSSFileCount = 0;
				$aFiles = glob(get_stylesheet_directory() . '/scss/css/' . "*.css");
				if ($aFiles){
					$iCSSFileCount = count($aFiles);
				}

				// Tell the user that there are too many files!
				if( $iSCSSFileCount < $iCSSFileCount ) {
					echo '<span class="red">' . sprintf('You have %s css files saved locally. You should check which ones youa re still using.', $iCSSFileCount) . '</span>';
				} else {
					echo '<span class="green">' . sprintf('You have %s css files saved locally. All seams fine!', $iCSSFileCount) . '</span>';
				}

				echo '<br /><br />';
			},
			'fixcms'
		);

		// register a new field in the "fixcms_section_developers" section, inside the "fixcms" page
		add_settings_field(
			'fixcms_field_apikey',
			__('API KEY', 'fixcms'),
			function( $args ){

				// Go global for settings
				global $aSettings;

				// Create field
				echo '<input id="' . esc_attr( $args['label_for'] ) . '" data-custom="' . esc_attr( $args['fixcms_custom_data'] ) . '" type="text" name="fixcms_options[' . esc_attr( $args['label_for'] ) . ']" value="' . $aSettings[$args['label_for']] . '" />';
			},
			'fixcms',
			'fixcms_section_developers',
			array(
				'label_for' => 'fixcms_field_apikey',
				'class' => 'fixcms_row',
				'fixcms_custom_data' => 'custom',
			)
		);

		// register a new checkbox in the "fixcms_section_developers" section, inside the "fixcms" page
		add_settings_field(
			'fixcms_field_use_cdn',
			__('Use the CDN (Default setting)', 'fixcms'),
			function( $args ){

				// Go global for settings
				global $aSettings;

				// Create field
				echo '<input id="' . esc_attr( $args['label_for'] ) . '" data-custom="' . esc_attr( $args['fixcms_custom_data'] ) . '" type="radio" name="fixcms_options[fixcms_field_output_implementation]" value="0" ' . ( empty($aSettings['fixcms_field_output_implementation']) ? 'checked="checked"' : '' ) . ' />';

			},
			'fixcms',
			'fixcms_section_developers',
			array(
				'label_for' => 'fixcms_field_use_cdn',
				'class' => 'fixcms_row',
				'fixcms_custom_data' => 'custom',
			)
		);

		// register a new checkbox in the "fixcms_section_developers" section, inside the "fixcms" page
		add_settings_field(
			'fixcms_field_local_links',
			__('Save css localy (Do not use CDN)', 'fixcms'),
			function( $args ){

				// Go global for settings
				global $aSettings;

				// Create field
				echo '<input id="' . esc_attr( $args['label_for'] ) . '" data-custom="' . esc_attr( $args['fixcms_custom_data'] ) . '" type="radio" name="fixcms_options[fixcms_field_output_implementation]" value="local_links" ' . ( !empty($aSettings['fixcms_field_output_implementation']) && $aSettings['fixcms_field_output_implementation'] == 'local_links' ? 'checked="checked"' : '' ) . ' />';

			},
			'fixcms',
			'fixcms_section_developers',
			array(
				'label_for' => 'fixcms_field_local_links',
				'class' => 'fixcms_row',
				'fixcms_custom_data' => 'custom',
			)
		);

		// register a new checkbox in the "fixcms_section_developers" section, inside the "fixcms" page
		add_settings_field(
			'fixcms_field_inline', // Speed and render blocking consideration
			__('Include CSS inline to prevent render blocking', 'fixcms'),
			function( $args ){

				// Go global for settings
				global $aSettings;

				// Create field
				echo '<input id="' . esc_attr( $args['label_for'] ) . '" data-custom="' . esc_attr( $args['fixcms_custom_data'] ) . '" type="radio" name="fixcms_options[fixcms_field_output_implementation]" value="inline" ' . ( !empty($aSettings['fixcms_field_output_implementation']) && $aSettings['fixcms_field_output_implementation'] == 'inline' ? 'checked="checked"' : '' ) . ' />';

			},
			'fixcms',
			'fixcms_section_developers',
			array(
				'label_for' => 'fixcms_field_inline',
				'class' => 'fixcms_row',
				'fixcms_custom_data' => 'custom',
			)
		);

		// register a new checkbox in the "fixcms_section_developers" section, inside the "fixcms" page
		add_settings_field(
			'fixcms_field_save_css_copy_localy', // Speed and render blocking consideration
			__('Save css compiled resulting copies locally', 'fixcms'),
			function( $args ){

				// Go global for settings
				global $aSettings;

				// Create field
				echo '<input id="' . esc_attr( $args['label_for'] ) . '" data-custom="' . esc_attr( $args['fixcms_custom_data'] ) . '" type="checkbox" name="fixcms_options[fixcms_field_save_css_copy_localy]" value="1" ' . ( !empty($aSettings['fixcms_field_save_css_copy_localy']) ? 'checked="checked"' : '' ) . ' />';

			},
			'fixcms',
			'fixcms_section_developers',
			array(
				'label_for' => 'fixcms_field_save_css_copy_localy',
				'class' => 'fixcms_row',
				'fixcms_custom_data' => 'custom',
			)
		);

		// register a new field in the "fixcms_section_developers" section, inside the "fixcms" page
		add_settings_field(
			'fixcms_field_compilation_mode',
			__('Compilation mode', 'fixcms'),
			function( $args ){

				// Go global for settings
				global $aSettings;

				// Create field
				echo '<select id="' . esc_attr( $args['label_for'] ) . '" data-custom="' . esc_attr( $args['fixcms_custom_data'] ) . '" name="fixcms_options[' . esc_attr( $args['label_for'] ) . ']">
					<option value="compressed" ' . ( $aSettings['fixcms_field_compilation_mode'] == 'compressed' ? 'selected="selected"' : '' ) . '>Compressed</option>
					<option value="nested" ' . ( $aSettings['fixcms_field_compilation_mode'] == 'nested' ? 'selected="selected"' : '' ) . '>Nested</option>
					<option value="expanded" ' . ( $aSettings['fixcms_field_compilation_mode'] == 'expanded' ? 'selected="selected"' : '' ) . '>Expanded</option>
					<option value="compact" ' . ( $aSettings['fixcms_field_compilation_mode'] == 'compact' ? 'selected="selected"' : '' ) . '>Compact</option>
				</select>';
			},
			'fixcms',
			'fixcms_section_developers',
			array(
				'label_for' => 'fixcms_field_compilation_mode',
				'class' => 'fixcms_row',
				'fixcms_custom_data' => 'custom',
			)
		);

		// register a new checkbox in the "fixcms_section_developers" section, inside the "fixcms" page
		add_settings_field(
			'fixcms_field_dont_enque_scripts', // Speed and render blocking consideration
			__('Do not autoenque scripts', 'fixcms'),
			function( $args ){

				// Go global for settings
				global $aSettings;

				// Create field
				echo '<input id="' . esc_attr( $args['label_for'] ) . '" data-custom="' . esc_attr( $args['fixcms_custom_data'] ) . '" type="checkbox" name="fixcms_options[fixcms_field_dont_enque_scripts]" value="1" ' . ( !empty($aSettings['fixcms_field_dont_enque_scripts']) ? 'checked="checked"' : '' ) . ' />';

			},
			'fixcms',
			'fixcms_section_developers',
			array(
				'label_for' => 'fixcms_field_dont_enque_scripts',
				'class' => 'fixcms_row',
				'fixcms_custom_data' => 'custom',
			)
		);

		// register a new checkbox in the "fixcms_section_developers" section, inside the "fixcms" page
		add_settings_field(
			'fixcms_field_go_around_cdn', // Speed and render blocking consideration
			__('Go around CDN entirely (do not use the CDN URLs)', 'fixcms'),
			function( $args ){

				// Go global for settings
				global $aSettings;

				// Create field
				echo '<input id="' . esc_attr( $args['label_for'] ) . '" data-custom="' . esc_attr( $args['fixcms_custom_data'] ) . '" type="checkbox" name="fixcms_options[fixcms_field_go_around_cdn]" value="1" ' . ( !empty($aSettings['fixcms_field_go_around_cdn']) ? 'checked="checked"' : '' ) . ' />';

			},
			'fixcms',
			'fixcms_section_developers',
			array(
				'label_for' => 'fixcms_field_go_around_cdn',
				'class' => 'fixcms_row',
				'fixcms_custom_data' => 'custom',
			)
		);
	}

	/**
	* register our fixcms_settings_init to the admin_init action hook
	*/
	add_action( 'admin_init', 'fixcms_settings_init' );

	/**
	* top level menu
	*/
	function fixcms_options_page() {

		// add top level menu page
		add_options_page(
			'InstaSASS settings',
			'InstaSASS settings',
			'manage_options',
			'fixcms',
			'fixcms_options_page_html'
		);

	}

	/**
	* register our fixcms_options_page to the admin_menu action hook
	*/
	add_action( 'admin_menu', 'fixcms_options_page' );
	function fixcms_options_page_html() {

		// check user capabilities
		if ( !current_user_can('manage_options') ) return;

		// add error/update messages

		// check if the user have submitted the settings
		if ( isset($_GET['settings-updated']) ) {

			// add settings saved message with the class of "updated"
			add_settings_error( 'fixcms_messages', 'fixcms_message', __( 'Settings Saved', 'fixcms' ), 'updated' );
		}

		// show error/update messages
		settings_errors( 'fixcms_messages' );

		// Generate the output
		echo '<div class="wrap">
		<h1>' . esc_html( get_admin_page_title() ) . '</h1>
		<form action="options.php" method="post">';
		settings_fields( 'fixcms' );
		do_settings_sections( 'fixcms' );
		submit_button( 'Save Settings' );
		echo '</form></div>';

	}

	function activate_wp_instasass() {

		// Create the directory if it doesn't exist
		if( !file_exists(get_stylesheet_directory() . '/scss') ) {
			mkdir(get_stylesheet_directory() . '/scss');
			touch(get_stylesheet_directory() . '/scss/0main.scss');
		}

		// Create the css saving repo
		if( !file_exists(get_stylesheet_directory() . '/scss/css') ) {
			mkdir(get_stylesheet_directory() . '/scss/css');
		}
		
	}
}