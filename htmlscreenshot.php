<?php
/*
Plugin Name: HtmlScreenshot
Plugin URI: https://elaborin.com/playground
Description: Allows any user to send a screenshot of the page to the site administrators.
Version: 0.1
Author: Laborin
Author URI: http://elaborin.com/playground
*/

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

if ( ! class_exists( 'HtmlScreenshot' ) ) {
	class HtmlScreenshot
	{
		/**
		 * Tag identifier used by file includes and selector attributes.
		 * @var string
		 */
		protected $tag = 'htmlscreenshot';

		/**
		 * User friendly name used to identify the plugin.
		 * @var string
		 */
		protected $name = 'HtmlScreenshot';

		/**
		 * Current version of the plugin.
		 * @var string
		 */
		protected $version = '0.1';


		/**
		 * Initiate the plugin by setting the default values and assigning any
		 * required actions and filters.
		 *
		 * @access public
		 */
		public function __construct()
		{
			add_action( 'wp_ajax_'.$this->tag, array(&$this, 'htmlscreenshot_save') );
			add_action( 'wp_ajax_nopriv_'.$this->tag, array(&$this, 'htmlscreenshot_save') );

			if ( is_admin() ) {
				add_action( 'admin_menu', array( &$this, 'el_menu' ) );
			}else {
				
				$this->_enqueue();
			}
		}

		/**
		 * Receives the image data via AJAX.
		 *
		 * @access public
		 */
		public function htmlscreenshot_save() {
			$base64Data = $_POST['base64Data'];
			$img = str_replace('data:image/jpeg;base64,', '', $base64Data);
			$img = str_replace(' ', '+', $img);
			$fileData = base64_decode($img);
			$directory = plugin_dir_path( __FILE__ ).'screenshots/';
			$fileName = $directory.time().'.jpg';
			if (!file_exists($directory)) {
				mkdir($directory, 0777, true);
			}
			file_put_contents($fileName, $fileData);
			echo "{success: true}";
			wp_die();
		}


		/**
		 * Add the setting fields to the General settings page.
		 *
		 * @access public
		 */
		public function el_menu()
		{
			add_options_page(
				$this->name,
				$this->name,
				'read',
				'htmlscreenshots_list',
				function(){
					echo "<h3>Screenshots sent by users:</h3>";
					echo "<ul>";
					if ($handle = opendir(plugin_dir_path( __FILE__ ).'screenshots/')) {

						while (false !== ($entry = readdir($handle))) {
							if ($entry != "." && $entry != "..") {
								echo "<li><a target='_BLANK' href='".plugin_dir_url( __FILE__ )."/screenshots/$entry'>$entry</a></li>";
							}
						}

						closedir($handle);
					}
					echo "</ul>";
				}
			);
		}



		/**
		 * Enqueue the required scripts and styles, only if they have not
		 * previously been queued.
		 *
		 * @access public
		 */
		protected function _enqueue()
		{
	 		// Define the URL path to the plugin...
			$plugin_path = plugin_dir_url( __FILE__ );
	 		// Enqueue the styles in they are not already...
			if ( !wp_style_is( $this->tag, 'enqueued' ) ) {
				wp_enqueue_style(
					$this->tag,
					$plugin_path . 'htmlscreenshot.css',
					array(),
					$this->version
				);
			}
	 		// Enqueue the scripts if not already...
			if ( !wp_script_is( $this->tag, 'enqueued' ) ) {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script(
					'html2canvas',
					$plugin_path . 'html2canvas.js',
					array( 'jquery' ),
					'0.1.2'
				);
				wp_register_script(
					$this->tag,
					$plugin_path . 'htmlscreenshot.js',
					array( 'html2canvas'),
					$this->version
				);
				wp_localize_script( $this->tag, 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'action' =>  $this->tag) );
				wp_enqueue_script( $this->tag );
			}
		}

	}
	new HtmlScreenshot;
}
