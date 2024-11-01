<?php
/*
 Plugin Name: Widget Locationizer
 Description: Widget Locationizer permits you to define where you want your widgets to appear.  You may specify the tags, categories, and page/post IDs for which a widget will be displayed.  It also provides an option to exclude the widget from being displayed on selected tags, categories, and post/page IDs.  Furthermore, you can assign a nofollow or dofollow status for a widget's contents too.
 Plugin URI: http://tomuse.com/wordpress/widget-locationizer
 Author: ToMuse.com
 Version: 1.2.2
 Author URI: http://www.plugin-developer.com/
 */

if( !class_exists( 'Widget_Locationizer' ) ) {

	class Widget_Locationizer {
		
		var $options;
		var $linkHtmlRegex = "/<a((\s+\w+(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)>(.*?)<\/a>/";

		/**
		 * Default constructor adds all appropriate actions and filters.
		 *
		 * @return Widget_Locationizer
		 */
		function Widget_Locationizer( ) {
			register_deactivation_hook( __FILE__, array( &$this, 'deleteOptions' ) );
			
			add_action( 'sidebar_admin_setup', array( &$this, 'onSidebarAdminSetup' ) );
			add_action( 'wp_head', array( &$this, 'onWpHead' ) );
			add_action( 'admin_menu', array( &$this, 'onAdminMenu' ) );
			
			$this->options = $this->getOptions( );
		}

		/**
		 * Intercept Posted information to store information about the widgets locationizer settings.  Also setup the widgets appropriately
		 * in order to provide the correct callbacks for widget controls.
		 *
		 */
		function onSidebarAdminSetup( ) {
			global $wp_registered_widgets, $wp_registered_widget_controls;
			$followSetting = $this->options[ 'use-nofollow-settings' ];
			$locationizerSettings = $this->options[ 'use-locationizer-settings' ];
			
			// if we're just updating the widgets, just read in the widget logic settings - makes this WP2.5+ only i think
			if( 'post' === strtolower( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
				foreach( ( array )$_POST[ 'widget-id' ] as $widgetId ) {
					if( isset( $_POST[ $widgetId . '-widget-locationizer-tags' ] ) || isset( $_POST[ $widgetId . '-widget-locationizer-follow-type' ] ) ) {
						$widgetOptions = array( 'tags' => $_POST[ $widgetId . '-widget-locationizer-tags' ], 'categories' => $_POST[ $widgetId . '-widget-locationizer-categories' ], 'posts' => $_POST[ $widgetId . '-widget-locationizer-posts' ], 'no-posts' => $_POST[ $widgetId . '-widget-locationizer-no-posts' ], 'other-pages' => $_POST[ $widgetId . '-widget-locationizer-other-pages' ], 'follow-type' => $_POST[ $widgetId . '-widget-locationizer-follow-type' ] );
						$this->options[ $widgetId ] = $widgetOptions;
					}
				}
				
				$registeredAndNewWidgets = array_merge( array_keys( $wp_registered_widgets ), array_values( ( array )$_POST[ 'widget-id' ] ) );
				foreach( array_keys( $this->options ) as $key ) {
					if( !in_array( $key, $registeredAndNewWidgets ) ) {
						unset( $this->options[ $key ] );
					}
				}
			} else {
				foreach( $wp_registered_widgets as $id => $widget ) {
					
					// There is no control callback for the widget
					if( !$wp_registered_widget_controls[ $id ] ) {
						wp_register_widget_control( $id, $widget[ 'name' ], array( &$this, 'emptyControl' ) );
					}
					
					if( !array_key_exists( 0, $wp_registered_widget_controls[ $id ][ 'params' ] ) || is_array( $wp_registered_widget_controls[ $id ][ 'params' ][ 0 ] ) ) {
						$wp_registered_widget_controls[ $id ][ 'params' ][ 0 ][ 'original-id' ] = $id;
					} else { // some older widgets put number in to params directly (which messes up the 'templates' in WP2.5)
						array_push( $wp_registered_widget_controls[ $id ][ 'params' ], $id );
					}
					
					// do the redirection
					$wp_registered_widget_controls[ $id ][ 'original-callback' ] = $wp_registered_widget_controls[ $id ][ 'callback' ];
					$wp_registered_widget_controls[ $id ][ 'callback' ] = array( &$this, 'widgetLocationizerControl' );
				}
			}
			
			// After all processing is complete, make sure we save to the database and restore the settings for nofollow/locationizer use
			$this->options[ 'use-nofollow-settings' ] = $followSetting;
			$this->options[ 'use-locationizer-settings' ] = $locationizerSettings;
			$this->saveOptions( );
		}

		/**
		 * Adds the locationizer settings page.
		 *
		 */
		function onAdminMenu( ) {
			add_options_page( __( 'Widget Location and Following' ), __( 'Widget Location and Following' ), 8, __FILE__, array( &$this, 'settingsPage' ) );
		}

		/**
		 * Does processing on options and include the appropriate HTML.
		 *
		 */
		function settingsPage( ) {
			if( !empty( $_POST ) ) {
				$this->options[ 'use-locationizer-settings' ] = $_POST[ 'use-locationizer-settings' ];
				$this->options[ 'use-nofollow-settings' ] = $_POST[ 'use-nofollow-settings' ];
				$this->saveOptions( );
			}
			
			include ( path_join( dirname( __FILE__ ), 'views/settings.view.php' ) );
		}

		/**
		 * Intercepts registered widgets, iterates over them, and assigns a new callback that determines whether content should
		 * be displayed or not.
		 *
		 */
		function onWpHead( ) {
			global $wp_registered_widgets;
			foreach( $wp_registered_widgets as $id => $widget ) {
				array_push( $wp_registered_widgets[ $id ][ 'params' ], $id );
				$wp_registered_widgets[ $id ][ 'original-callback' ] = $wp_registered_widgets[ $id ][ 'callback' ];
				$wp_registered_widgets[ $id ][ 'callback' ] = array( &$this, 'widgetLocationizerDisplay' );
			}
		}

		/**
		 * Surrounds a function callback in output buffering calls and then
		 * filters the output if necessary.
		 *
		 * @param callable $callback
		 * @param array $parameters
		 */
		function doTheCallback( $callback, $functionParameters, $followType = '' ) {
			ob_start( );
			call_user_func_array( $callback, $functionParameters );
			$output = ob_get_clean( );
			print $this->doReplaceOnOutput( $output, $followType );
		}

		/**
		 * Redirected callback that decides whether or not to call a widgets content function based on the widget
		 * locationizer settings for that widget.
		 *
		 */
		function widgetLocationizerDisplay( ) {
			
			global $wp_registered_widgets;
			$functionParameters = func_get_args( );
			$id = array_pop( $functionParameters );
			$callback = $wp_registered_widgets[ $id ][ 'original-callback' ]; // find the real callback
			

			// If the widget was put up before this plugin was activated, let it go ahead and be used
			if( !isset( $this->options[ $id ] ) || !$this->checkLocationizerOn( ) ) {
				$this->doTheCallback( $callback, $functionParameters, $this->options[ $id ][ 'follow-type' ] );
			}
			
			$tags = $this->options[ $id ][ 'tags' ];
			$categories = $this->options[ $id ][ 'categories' ];
			$posts = $this->options[ $id ][ 'posts' ];
			$noPosts = $this->options[ $id ][ 'no-posts' ];
			$otherPages = $this->options[ $id ][ 'other-pages' ];
			
			$theTags = array_map( 'trim', explode( ',', trim( $tags ) ) );
			$theCategories = array_map( 'trim', explode( ',', trim( $categories ) ) );
			$thePosts = array_map( 'trim', explode( ',', trim( $posts ) ) );
			$theNoPosts = array_map( 'trim', explode( ',', trim( $noPosts ) ) );
			
			$showOnOtherPages = ( $otherPages == 1 ) && '' == $thePosts[ 0 ];
			$isSingle = is_single( ) || is_page( );
			$inTags = is_tag( $theTags );
			$inCategories = is_category( $theCategories );
			$inANoPost = ( $noPosts != '' ) && ( is_single( $theNoPosts ) || is_page( $theNoPosts ) );
			
			$callFunction = false;
			if( $inANoPost ) {
				return;
			}
			if( $inTags ) {
				$callFunction = true;
			} else if( $inCategories ) {
				$callFunction = true;
			} else if( $isSingle && ( is_single( $thePosts ) || is_page( $thePosts ) ) ) {
				$callFunction = true;
			} else if( $isSingle && $showOnOtherPages ) {
				$callFunction = true;
			} else if( !is_single( ) && !is_page( ) && 1 == $otherPages ) {
				$callFunction = true;
			} else if( ( $isSingle ) ) {
				global $post;
				$postTags = get_the_tags( $post->ID );
				$postCategories = get_the_category( $post->ID );
				if( !is_array( $postTags ) ) {
					$postTagSlugs = array();
					$postTagNames = array();
				} else {
					$_tags = $postTags;
					$postTagSlugs = array();
					$postTagNames = array();
					foreach( $_tags as $tag ) {
						$postTagSlugs[ ] = $tag->slug;
						$postTagNames[ ] = $tag->name;
					}
				}
				
				$postCategorySlugs = array();
				$postCategoryNames = array();
				if( is_array( $postCategories ) ) {
					$_categories = $postCategories;
					$postCategories = array();
					foreach( $_categories as $cat ) {
						$postCategorySlugs[ ] = $cat->slug;
						$postCategoryNames[ ] = $cat->name;
					}
				}
				
				$tagSlugIntersection = array_intersect( $theTags, $postTagSlugs );
				$tagNameIntersection = array_intersect( $theTags, $postTagNames );
				$categorySlugIntersection = array_intersect( $theCategories, $postCategorySlugs );
				$categoryNameIntersection = array_intersect( $theCategories, $postCategoryNames );
				$callFunction = !empty( $tagSlugIntersection ) || !empty( $categorySlugIntersection ) || !empty( $tagNameIntersection ) || !empty( $categoryNameIntersection );
			}
			
			if( $callFunction ) {
				$this->doTheCallback( $callback, $functionParameters, $this->options[ $id ][ 'follow-type' ] );
			}
		}

		/**
		 * Makes all links in widgets nofollow as long as the appropriate options has been set.
		 *
		 * @param string $output
		 * @return string
		 */
		function doReplaceOnOutput( $output, $followType ) {
			$replacedOutput = $output;
			if( $this->checkNofollowOn( ) ) {
				if( 'nofollow' == $followType || 'dofollow' == $followType ) {
					$replacedOutput = preg_replace_callback( $this->linkHtmlRegex, array( &$this, $followType . 'Links' ), $output );
					return $replacedOutput;
				}
			}
			return $replacedOutput;
		}

		/**
		 * Given a set of matches from the linkHtmlRegex and some text, return a new link that is nofollowed.
		 *
		 * @param array $matches
		 * return string
		 */
		function nofollowLinks( $matches ) {
			$attributes = $matches[ 1 ];
			$linkText = $matches[ 4 ];
			$attributes = preg_replace( '/rel=".*?"/', '', $attributes );
			$attributes = 'rel="nofollow" ' . $attributes;
			return "<a $attributes>$linkText</a>";
		}

		/**
		 * Given a set of matches from the linkHtmlRegex and some text, return a new link that is dofollowed.
		 *
		 * @param array $matches
		 * return string
		 */
		function dofollowLinks( $matches ) {
			$attributes = $matches[ 1 ];
			$linkText = $matches[ 4 ];
			$attributes = preg_replace( '/rel=".*?"/', '', $attributes );
			$attributes = 'rel="dofollow" ' . $attributes;
			return "<a $attributes>$linkText</a>";
		}

		/**
		 * This is a simple placeholder callback that is put in place to make sure that there aren't any errors
		 * generated when we later call the original callback for a widget.
		 */
		function emptyControl( ) {}

		/**
		 * Redirected callback function that alters a widgets output.
		 *
		 */
		function widgetLocationizerControl( ) {
			global $wp_registered_widget_controls;
			$functionParameters = func_get_args( );
			
			// find the widget id that we have sneaked into the params
			$id = ( is_array( $functionParameters[ 0 ] ) ) ? $functionParameters[ 0 ][ 'original-id' ] : array_pop( $functionParameters );
			$id_disp = $id;
			
			// Get the original function and call it
			$callback = $wp_registered_widget_controls[ $id ][ 'original-callback' ];
			if( is_callable( $callback ) ) {
				call_user_func_array( $callback, $functionParameters );
			}
			
			$widgetOptions = $this->options[ $id ];
			
			// Get the number for multiple widgets (if this is one)
			if( is_array( $functionParameters[ 0 ] ) && isset( $functionParameters[ 0 ][ 'number' ] ) ) {
				$number = $functionParameters[ 0 ][ 'number' ];
			}
			if( $number == -1 ) {
				$number = "%i%";
				$widgetOptions = array();
			}
			
			if( isset( $number ) ) {
				$id_disp = $wp_registered_widget_controls[ $id ][ 'id_base' ] . '-' . $number;
			}
			
			include ( path_join( dirname( __FILE__ ), 'widget-locationizer-controls.php' ) );
		}

		/**
		 * Returns a boolean value indicating whether links should be transformed to use NoFollow
		 *
		 * @return boolean
		 */
		function checkNofollowOn( ) {
			return 1 == $this->options[ 'use-nofollow-settings' ];
		}

		/**
		 * Returns a boolean value indicating whether location settings should be used.
		 *
		 * @return boolean
		 */
		function checkLocationizerOn( ) {
			return 1 == $this->options[ 'use-locationizer-settings' ];
		}

		/**
		 * Obtains the WordPress Locationizer options from the database.  If there are none present,
		 * then an empty array is returned.
		 *
		 * @return array
		 */
		function getOptions( ) {
			if( false === ( $options = get_option( 'Widget Locationizer Options' ) ) ) {
				return array( 'use-locationizer-settings' => 1, 'use-nofollow-settings' => 1 );
			} else {
				return $options;
			}
		}

		/**
		 * Saves the current instance variable options to the WordPress database.
		 *
		 */
		function saveOptions( ) {
			update_option( 'Widget Locationizer Options', $this->options );
		}

		/**
		 * Removes the Widget Locationizer options from the WordPress database.
		 *
		 */
		function deleteOptions( ) {

		}
	
	}
}

if( class_exists( 'Widget_Locationizer' ) ) {
	$widget_locationizer = new Widget_Locationizer( );
}