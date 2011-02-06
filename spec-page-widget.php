<?php
/*
 Plugin Name: Spectacu.la Page Widget
 Plugin URI: http://spectacu.la
 Description: Show the content of a selected page in a widget. Also gives you control over title behaviour and the page's visibility elsewhere in Wordpress.
 Version: 1.0.8
 Author: James R Whitehead of Spectacu.la
 Author URI: http://www.interconnectit.com

 Release notes: 1.0.0 Initial release
				1.0.1 Added an option to include a clear block at the end of the content. Helpful when you have a page with some floated elements in it and quite short content.
				1.0.2 Tidied up the widget display name in the widget admin page. Also got rid of the unneeded word "widget" from the widget. Added option to show the widget even when viewing the page that's set to show in the widget.
				1.0.3 Edited readme tag and descriptions.
				1.0.4 Fixed issue with wp_list_pages_excludes not respecting other plug-ins wishes.
				1.0.5 Very minor change to bypass a problem I had where a page_id is passed to register_sidebar as part of another plug-in I'm working on and thus interrupts my page_id for this plug-in.
				1.0.6 Found a problem with some of my logic that resulted in the widget not showing up when it would otherwise be expected to. Fixed it.
				1.0.7 Added an option to the widget interface to allow you to add extra CSS classes to the widget.
				1.0.8 To avoid depricated calls messages I've changed the way we load the translation files.
*/

define ( 'SPEC_PAGEWIDGET_VER', 2.8 );
define ( 'SPEC_PAGEWIDGET_DOM', 'spec-page-widget' );
define ( 'SPEC_PAGEWIDGET_OPT', 'specpagewidgets' );

if ( ! class_exists( 'spec_page_widget' ) ) {
	class spec_page_widget extends WP_Widget {

		var $defaults = array(
							  'title_toggle' => true,
							  'link_toggle' => true,
							  'hide_toggle' => false,
							  'excerpt_toggle' => false,
							  'page_id' => 0,
							  'title' => '',
							  'clear_toggle' => false,
							  'self_show' => false,
							  'class' => ''
							);

		/*
		 constructor.
		*/
		function spec_page_widget( ) {
			$locale = get_locale( );
			if ( file_exists( dirname( __FILE__ ) . '/lang/' . SPEC_PAGEWIDGET_DOM . '-' . $locale . '.mo' ) )
				load_textdomain( SPEC_PAGEWIDGET_DOM, dirname( __FILE__ ) . '/lang/' . SPEC_PAGEWIDGET_DOM . '-' . $locale . '.mo' );

			$widget_ops = array( 'classname' => 'spec_page_widget', 'description' => __( 'Show the content of a selected page in a widget. Gives you control over title behaviour and the page\'s visibility elsewhere in Wordpress.', SPEC_PAGEWIDGET_DOM ) );
			$this->WP_Widget( SPEC_PAGEWIDGET_OPT, __( 'Spectacu.la Page', SPEC_PAGEWIDGET_DOM ), $widget_ops, array( 'width' => 450 ) );

			if ( ! is_admin( ) )
				add_filter( 'wp_list_pages_excludes', array( &$this, 'excludes_pages' ) );

		}


		function widget( $args, $instance ) {
			global $post;
			extract( (array )$instance, EXTR_SKIP );
			extract( $args, EXTR_SKIP );

			if ( ! empty( $class ) )
				$before_widget = $this->add_class_attrib( $before_widget, $this->clean_classes( $class ) );

			// Check that the page chosen exists.
			if ( ( ( $post->ID == $page_id ) && $self_show ) || ( $post->ID != $page_id ) ) {
				$page  = get_post( $page_id );

				if ( is_wp_error( $page ) || $page->ID != $page_id )
					return false;

				if ( $title_toggle ) {
					$title = $alt_title != '' ? apply_filters( 'the_title', $alt_title, $page_id ) : apply_filters( 'the_title', $page->post_title, $page_id );
					if ( $link_toggle ){
						// Use class instead of ID as page could be on display in more than one place.
						$title = '<a href="' . get_permalink( $page_id ) . '" class="' . sanitize_title( $page->post_title ) . '-' . $page->ID . '">' . $title . '</a>';
					}
				}

				$clear = $clear_toggle ? '<div style="clear:both;height:0;overflow:hidden;visibility:hidden"></div>' : '';

				if ( ! post_password_required( $page_id ) ) {
					if ( $excerpt_toggle ) {
						$content = $page->post_excerpt ? apply_filters( 'the_excerpt', $page->post_excerpt ) : $this->excerptify( $page->post_content );
					} else {
						$content = apply_filters( 'the_content', $page->post_content );
					}
				} else {
					$content = __( 'Password protected page', SPEC_PAGEWIDGET_DOM );
				}

				echo $before_widget;

				echo ! empty( $title ) ? $before_title . $title . $after_title : '';
				echo $content . $clear;

				echo $after_widget;
			}
		}


		function update( $new_instance = array( ), $old_instance = array( ) ) {

			extract( $this->defaults, EXTR_SKIP );

			$title_toggle	= isset( $new_instance[ 'title_toggle' ] )	&& ! empty( $new_instance[ 'title_toggle' ] );
			$link_toggle	= isset( $new_instance[ 'link_toggle' ] )	&& ! empty( $new_instance[ 'link_toggle' ] );
			$self_show		= isset( $new_instance[ 'self_show' ] )		&& ! empty( $new_instance[ 'self_show' ] );
			$excerpt_toggle	= isset( $new_instance[ 'excerpt_toggle' ] )&& ! empty( $new_instance[ 'excerpt_toggle' ] );
			$clear_toggle	= isset( $new_instance[ 'clear_toggle' ] )	&& ! empty( $new_instance[ 'clear_toggle' ] );
			$hide_toggle 	= isset( $new_instance[ 'hide_toggle' ] ) 	&& ! empty( $new_instance[ 'hide_toggle' ] );

			$page_id		= intval( $new_instance[ 'page_id' ] ) ? intval( $new_instance[ 'page_id' ] ) : 0;
			$page_id_old	= intval( $old_instance[ 'page_id' ] ) ? intval( $old_instance[ 'page_id' ] ) : 0;

			$alt_title		= ! empty( $new_instance[ 'alt_title' ] ) ? sanitize_text_field( $new_instance[ 'alt_title' ] ) : '';
			$class			= $this->clean_classes( $new_instance[ 'class' ] );

			// Add to our page exclusions array if we need to.
			$exclusions = $this->excludes_pages( );

			if ( $hide_toggle && ! in_array( $page_id, $exclusions ) ) {
				$exclusions[ ] = $page_id;

				// Update
				if ( ! update_option( SPEC_PAGEWIDGET_OPT, $exclusions ) )
					add_option( SPEC_PAGEWIDGET_OPT, $exclusions );

			} elseif( ! $hide_toggle && in_array( $page_id, $exclusions ) ) {
				$index = array_search( $page_id, $exclusions );
				unset( $exclusions[ $index ] );

				// Update
				if ( ! update_option( SPEC_PAGEWIDGET_OPT, $exclusions ) )
					add_option( SPEC_PAGEWIDGET_OPT, $exclusions );
			}

			if ( $page_id != $page_id_old ) {
				$index = array_search( $page_id_old, $exclusions );
				unset( $exclusions[ $index ] );

				// Update
				if ( ! update_option( SPEC_PAGEWIDGET_OPT, $exclusions ) )
					add_option( SPEC_PAGEWIDGET_OPT, $exclusions );
			}

			return compact( 'title_toggle', 'link_toggle', 'self_show', 'excerpt_toggle', 'clear_toggle', 'page_id', 'alt_title', 'class', 'hide_toggle' );
		}


		function form( $instance = array( ) ) {
			$instance = array_merge( $this->defaults, ( array )$instance );
			extract( $instance, EXTR_SKIP );
			unset( $disabled );

			$this->pages = get_pages( );
			$this->page_ids = array_map( create_function( '$a', 'return $a->ID;' ), $this->pages );

			// Set up the display name for the widget admin page
			$page = $page_id > 0 ? get_post( $page_id ) : null; ?>
			<input id="display-title" type="hidden" value="<?php echo ! empty( $alt_title )? apply_filters( 'the_title', $alt_title, $page_id ) : ( $page_id > 0 ? apply_filters( 'the_title', $page->post_title, $page_id ) : __( 'None', SPEC_PAGEWIDGET_DOM ) );?>"/>

			<p>
				<label for="<?php echo $this->get_field_id( 'page_id' ); ?>"><strong><?php _e( 'Select the page:', SPEC_PAGEWIDGET_DOM ); ?></strong></label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'page_id' ); ?>" name="<?php echo $this->get_field_name( 'page_id' ); ?>" >
					<option value="0"<?php echo $page_id == 0 ? ' selected="selected"' : ''; ?>><?php _e( 'None', SPEC_PAGEWIDGET_DOM );?></option> <?php

					foreach( $this->pages as $page ) {
						echo '<option value="' . $page->ID . '"' . ( $page_id == $page->ID ? ' selected="selected"' : '' ) . '>' . $page->post_title . '</option>';
					} ?>

				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'title_toggle' ); ?>"><?php _e( 'Show title:', SPEC_PAGEWIDGET_DOM ); ?></label>
				<input onchange="specFieldToggle( '#<?php echo $this->get_field_id( 'title_toggle' ); ?>', '#<?php echo $this->get_field_id( 'title_stuff' ); ?>' )" type="checkbox"<?php echo $title_toggle ? ' checked="checked"' : '' ; ?> id="<?php echo $this->get_field_id( 'title_toggle' ); ?>" name="<?php echo $this->get_field_name( 'title_toggle' ); ?>" value="1"/>
			</p>

			<fieldset id="<?php echo $this->get_field_id( 'title_stuff' ); ?>" style="border:solid 1px #ccc;padding: 10px;margin-bottom:1em;-moz-border-radius: 4px;">
				<p>
					<label for="<?php echo $this->get_field_id( 'link_toggle' ); ?>"><?php _e( 'Title should link to source page:', SPEC_PAGEWIDGET_DOM ); ?></label>
					<input type="checkbox"<?php echo $link_toggle ? ' checked="checked"' : '' ; ?> id="<?php echo $this->get_field_id( 'link_toggle' ); ?>" name="<?php echo $this->get_field_name( 'link_toggle' ); ?>" value="1"/>
				</p>

				<p>
					<label for="<?php echo $this->get_field_id( 'alt_title' ); ?>"><?php _e( 'Use this text instead of the page title:', SPEC_PAGEWIDGET_DOM ); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id( 'alt_title' ); ?>" name="<?php echo $this->get_field_name( 'alt_title' ); ?>" type="text" value="<?php echo ! empty( $alt_title ) ? $alt_title : ''; ?>" />
				</p>
			</fieldset>

			<script type="text/javascript" language="JavaScript">
				//<![CDATA[
				function specFieldToggle( trigger, target ) {
					if( typeof jQuery != "undefined" ){
						 if ( jQuery( trigger ).attr( 'checked' ) ){
							jQuery( target ).css( { color:'#000' } ).find( 'input' ).attr( { disabled:'' } );
						} else {
							jQuery( target ).css( { color:'#ccc' } ).find( 'input' ).attr( { disabled:'disabled' } );
						}
					}
				}

				specFieldToggle( '#<?php echo $this->get_field_id( 'title_toggle' ); ?>', '#<?php echo $this->get_field_id( 'title_stuff' ); ?>' );
				//]]>
			</script>

			<p>
				<label for="<?php echo $this->get_field_id( 'excerpt_toggle' ); ?>"><?php _e( 'Use excerpt rather than full content:', SPEC_PAGEWIDGET_DOM ); ?></label>
				<input type="checkbox"<?php checked( $excerpt_toggle ); ?> id="<?php echo $this->get_field_id( 'excerpt_toggle' ); ?>" name="<?php echo $this->get_field_name( 'excerpt_toggle' ); ?>" value="1"/>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'clear_toggle' ); ?>"><?php _e( 'Add a clear block at the end of the content:', SPEC_PAGEWIDGET_DOM ); ?></label>
				<input type="checkbox"<?php checked( $clear_toggle ); ?> id="<?php echo $this->get_field_id( 'clear_toggle' ); ?>" name="<?php echo $this->get_field_name( 'clear_toggle' ); ?>" value="1"/>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'self_show' ); ?>"><?php _e( 'Show this widget if on the page that matched the ID set above:', SPEC_PAGEWIDGET_DOM ); ?></label>
				<input type="checkbox"<?php checked( $self_show ); ?> id="<?php echo $this->get_field_id( 'self_show' ); ?>" name="<?php echo $this->get_field_name( 'self_show' ); ?>" value="1"/>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'class' ); ?>"><?php _e( 'CSS Classes:', SPEC_PAGEWIDGET_DOM ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'class' ); ?>" name="<?php echo $this->get_field_name( 'class' ); ?>" type="text" value="<?php echo $class; ?>" />
			</p>


			<p>
				<label for="<?php echo $this->get_field_id( 'hide_toggle' ); ?>"><?php _e( 'Exclude page from wp_list_pages:', SPEC_PAGEWIDGET_DOM ); ?></label>
				<input type="checkbox" <?php checked( $hide_toggle ); ?> id="<?php echo $this->get_field_id( 'hide_toggle' ); ?>" name="<?php echo $this->get_field_name( 'hide_toggle' ); ?>" value="1"/>
			</p> <?php
		}


		function excerptify( $text = '' ) {
			if ( $text == '' )
				return '';

			$text = strip_shortcodes( $text );
			$text = apply_filters( 'the_content', $text );
			$text = str_replace( ']]>', ']]&gt;', $text );
			$text = strip_tags( $text );

			$excerpt_length = apply_filters( 'excerpt_length', 55 );
			$words = explode( ' ', $text, $excerpt_length + 1 );
			if ( count( $words ) > $excerpt_length ) {
				array_pop( $words );
				array_push( $words, '[...]' );
				$text = implode( ' ', $words );
			}

			return apply_filters( 'the_excerpt', $text );
		}


		function excludes_pages( $output = array( ) ) {
			$this->exclusions = get_option( SPEC_PAGEWIDGET_OPT, array( ) );
			return array_merge( $this->exclusions, $output );
		}


		function add_class_attrib( $tag, $class ) {
			$output = preg_replace( '/(^[^<]*?<\w+\s?[^>]*?)(?:class=[\'"])?([^\'"]*?)[\'"]?(>.*)/is', '$1 class="' . strtolower( $class ) . ' $2"$3', $tag );
			$output = preg_replace( '/\s+/is', ' ', $output );
			return $output;
		}


		function clean_classes( $classes = '' ) {
			$tmp = array( );
			foreach( ( array ) explode( ' ', $classes ) as $class ) {
				$tmp[ ] = preg_replace( array( '/^[^a-zA-Z]+/i', '/[^a-zA-Z0-9-_]*?/i', '/[^a-zA-Z0-9]+$/i' ), '', trim( $class ) );
			}
			$classes = implode( ' ', $tmp );
			return $classes;
		}
	}


	/*
	 Only load the plug-in if we're running a version of WP that'll not break things.
	*/
	if ( version_compare( $wp_version, SPEC_PAGEWIDGET_VER, 'ge' ) );
		add_action( 'widgets_init', create_function( '', 'return register_widget( "spec_page_widget" );' ) );
}

?>
