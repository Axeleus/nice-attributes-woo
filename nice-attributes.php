<?php
/**
 *	Plugin Name: Nice Attributes
 *	Plugin URI: http://yellowduck.me/
 *	Description: Can make your WooCommerce site a little bit better
 *	Version: 0.1
 *	Requires at least: WP 4.8.1
 *	Author: Vitaly Kukin
 *	Author URI: http://yellowduck.me/
 *	License: GNU
 */

if ( ! defined( 'NA_PATH' ) ) define( 'NA_PATH', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'NA_URL' ) ) define( 'NA_URL', str_replace( array( 'https:', 'http:' ), '', plugins_url( 'nice-attributes' ) ) );

if ( ! is_admin() ) :

    require( NA_PATH . 'hooks.php' );

endif;