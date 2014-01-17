<?php
/*
Plugin Name: Simple UpVote
Plugin URI: http://royscheeren.com
Description: Simple plugin to give your readers the ability to UpVote your post.
Version: 1.0
Author: Roy Scheeren
Author URI: http://www.royscheeren.com

License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

*/

if(!class_exists( 'WPL_Simple_Upvote' )) {

    class WPL_Simple_Upvote {

        public static function init() {

            /* Add javascript */
            add_action( 'wp_enqueue_scripts', __CLASS__ . '::wpl_upv_load_scripts'  );

            /* Add AJAX call to list of actions */
            add_action( 'wp_ajax_wpl_upvote_ajax_request', __CLASS__ . '::wpl_upvote_post' );
            add_action( 'wp_ajax_nopriv_wpl_upvote_ajax_request', __CLASS__ . '::wpl_upvote_post' );
        }

        public static function wpl_upv_load_scripts() {
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'wpl_upv_js', plugins_url( 'js/wpl_upv_js.js', __FILE__), array( 'jquery' ), '1.0', true );

            /* Pass ajaxurl and nonce using wp_localize_script */
            $vars['ajaxurl'] = admin_url( 'admin-ajax.php' );
            $vars['nonce']  = wp_create_nonce( 'wpl_upvote' );
            wp_localize_script( 'wpl_upv_js', 'vars', $vars );
        }

        public static function wpl_upv_display() {
            global $post;

            /* Get number of upvotes or set to 0 if there are none */
            $upvotes = get_post_meta( $post->ID, '_wpl_upvotes', true );
            if ( $upvotes == '' )
                $upvotes = 0;

            /* Display upvotes and link */
            echo '<div class="wpl-upvotes"><span class="wpl-upv-votes num-votes-'. $post->ID .'">' . $upvotes . '</span><span class="wpl-upvote wpl-upvote-'.$post->ID.'" data-post_id="'.$post->ID.'" >Up Vote</span></div>';
        }

        public static function wpl_upvote_post() {
            global $wpdb;
            /* Get post data from AJAX call */
            $post_id = $_POST['post_id'];
            $nonce = $_POST['nonce'];

            /* Verify nonce for security */
            if ( ! wp_verify_nonce( $nonce, 'wpl_upvote' ) )
                die( 'Security check' );

            /* Check if this user has voted before by IP */
            $user_ip = $_SERVER['REMOTE_ADDR'];

            $has_user_voted = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value from $wpdb->postmeta WHERE post_id = %d and meta_key = '_wpl_upv_user_ip'", $post_id ) );
            $upvotes = get_post_meta( $post_id, '_wpl_upvotes', true );

            if( "" == $has_user_voted ) {
                /* Update the post meta to save upvote */
                $upvotes = intval( $upvotes ) + 1;
                $upvote = update_post_meta( $post_id, '_wpl_upvotes', $upvotes );
                $user_has_voted = update_post_meta( $post_id, '_wpl_upv_user_ip', $user_ip );
            }

            /* Return new number of upvotes to page */
            echo $upvotes;
            die();
        }
    }

    $wpl_upvotes = WPL_Simple_Upvote::init();

}
?>