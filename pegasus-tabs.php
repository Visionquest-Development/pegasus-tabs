<?php
/*
Plugin Name: Pegasus Tabs Plugin
Plugin URI:	 https://developer.wordpress.org/plugins/the-basics/
Description: This allows you to create tabs on your website with just a shortcode.
Version:	 1.0
Author:		 Jim O'Brien
Author URI:	 https://visionquestdevelopment.com/
License:	 GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wporg
Domain Path: /languages
*/

	global $wpdb;

	function pegasus_tabs_check_main_theme_name() {
		$current_theme_slug = get_option('stylesheet'); // Slug of the current theme (child theme if used)
		$parent_theme_slug = get_option('template');    // Slug of the parent theme (if a child theme is used)

		//error_log( "current theme slug: " . $current_theme_slug );
		//error_log( "parent theme slug: " . $parent_theme_slug );

		if ( $current_theme_slug == 'pegasus' ) {
			return 'Pegasus';
		} elseif ( $current_theme_slug == 'pegasus-child' ) {
			return 'Pegasus Child';
		} else {
			return 'Not Pegasus';
		}
	}

	function pegasus_tabs_menu_item() {
		if ( pegasus_tabs_check_main_theme_name() == 'Pegasus' || pegasus_tabs_check_main_theme_name() == 'Pegasus Child' ) {
			//do nothing
		} else {
			//echo 'This is NOT the Pegasus theme';
			add_menu_page(
				"Tabs", // Page title
				"Tabs", // Menu title
				"manage_options", // Capability
				"pegasus_tabs_plugin_options", // Menu slug
				"pegasus_tabs_plugin_settings_page", // Callback function
				null, // Icon
				93 // Position in menu
			);
		}
	}
	add_action("admin_menu", "pegasus_tabs_menu_item");

	function pegasus_tabs_plugin_settings_page() { ?>
	    <div class="wrap pegasus-wrap">
			<h1>pegasus_tabs Usage</h1>

			<div>
				<h3>pegasus_tabs Usage 1:</h3>
				<style>
					pre {
						background-color: #f9f9f9;
						border: 1px solid #aaa;
						page-break-inside: avoid;
						font-family: monospace;
						font-size: 15px;
						line-height: 1.6;
						margin-bottom: 1.6em;
						max-width: 100%;
						overflow: auto;
						padding: 1em 1.5em;
						display: block;
						word-wrap: break-word;
					}

					input[type="text"].code {
						width: 100%;
					}
				</style>
				<pre >[tabs]
	[tab class="first" title="Home"]
		Vivamus suscipit tortor eget felis porttitor volutpat.
	[/tab]
	[tab class="second" title="Profile"]
		Pellentesque in ipsum id orci porta dapibus.
	[/tab]
[/tabs]</pre>

				<input
					type="text"
					readonly
					value="<?php echo esc_html('[tabs][tab class="first" title="Home"]Vivamus suscipit tortor eget felis porttitor volutpat. [/tab][tab class="second" title="Profile"]Pellentesque in ipsum id orci porta dapibus.[/tab][/tabs]'); ?>"
					class="regular-text code"
					id="my-shortcode"
					onClick="this.select();"
				>
			</div>

			<p style="color:red;">MAKE SURE YOU DO NOT HAVE ANY RETURNS OR <?php echo htmlspecialchars('<br>'); ?>'s IN YOUR SHORTCODES, OTHERWISE IT WILL NOT WORK CORRECTLY</p>

		</div>
	<?php
	}

	//add_action("admin_menu", "pegasus_tabs_menu_item");
	//function pegasus_tabs_menu_item() {
		//add_menu_page("Tabs", "Tabs", "manage_options", "pegasus_tabs_plugin_options", "pegasus_tabs_plugin_settings_page", null, 99);
	//}


	/*
	function pegasus_tabs_plugin_settings_page() { ?>
		<div class="wrap pegasus-wrap">
		<h1>Tabs</h1>

		<p>Usage: <pre>[tabs][tab class="first" title="Home"]Vivamus suscipit tortor eget felis porttitor volutpat. [/tab][tab class="second" title="Profile"]Pellentesque in ipsum id orci porta dapibus. [/tab][/tabs]</pre> </p>

		</div>
	<?php
	}
	*/

	function pegasus_tabs_plugin_styles() {

		wp_register_style( 'tabs-css', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'css/tabs.css', array(), null, 'all' );
		//wp_enqueue_style( 'slippery-slider-css', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'css/slippery-slider.css', array(), null, 'all' );

	}
	add_action( 'wp_enqueue_scripts', 'pegasus_tabs_plugin_styles' );

	/**
	* Proper way to enqueue JS
	*/
	function pegasus_tabs_plugin_js() {

		//wp_enqueue_script( 'tabs-js', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'js/plugin.js', array( 'jquery' ), null, true );
		wp_register_script( 'pegasus-tabs-plugin-js', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'js/plugin.js', array( 'jquery' ), null, 'all' );

	} //end function
	add_action( 'wp_enqueue_scripts', 'pegasus_tabs_plugin_js' );

	/**
	* Tabs Short Code
	*/

	if ( ! class_exists( 'TabsClass' ) ) {
		class TabsClass {

			protected $_tabs_divs;

			public function __construct($tabs_divs = '') {
				$this->_tabs_divs = $tabs_divs;
				add_shortcode( 'tabs', array( $this, 'tabs_wrap') );
				add_shortcode( 'tab', array( $this,'tab_block') );
			}

			function tabs_wrap ( $args, $content = null ) {
				$output = '<div class="js-tab-widget"><ul class="tab-list" >' . do_shortcode($content) . '</ul>';

				//$output .= '<div class="pegasus-tabs-content">';
				$output .= $this->_tabs_divs;
				//$output .= '</div>'; //end tabs content
				$output .= '</div>'; //end pegasus-tabs


				wp_enqueue_style( 'tabs-css' );
				wp_enqueue_script( 'pegasus-tabs-plugin-js' );

				return $output;
			}

			function tab_block( $args, $content = null ) {
				extract(shortcode_atts(array(
					'id' => '',
					'title' => '',
					'class' => '',
				), $args));

				if ( '' === $id ) {
					$id = 1;
				}

				$output = '
					<li class="tab-item ' . $class . '">
						<a class="tab-link" href="#tab-' . $id . '" >' . $title . '</a>
					</li>
				';

				$this->_tabs_divs.= '<section id="tab-' . $id . '" class="tab-panel">' . $content . '</section>';

				$id++;

				return $output;
			}

		}
		new TabsClass;
	}


	?>
