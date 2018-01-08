<?php

/*
 * Setup WordPress
 */
    function custom_wordpress_setup() {

        // Enable tags for Pages (@see: https://wordpress.org/support/topic/enable-tags-screen-for-pages#post-29500520
        //register_taxonomy_for_object_type('post_tag', 'page');

        // Enable excerpts for pages
        add_post_type_support('page', 'excerpt');

    }
    add_action('init', 'custom_wordpress_setup');

/*
 * Setup theme
 */
    function custom_theme_setup() {

	    // Turn on menus
		add_theme_support('menus');

		// Enable HTML5 support
		add_theme_support('html5');

	}
	add_action( 'after_setup_theme', 'custom_theme_setup' );

/*
 * Enqueue Custom Scripts
 */
    function custom_scripts() {
		$has_bundle = file_exists(get_template_directory() . '/static/bundle.js');
		$has_dev_bundle = file_exists(get_template_directory() . '/static/bundle.dev.js');

		// if WP_DEBUG is on, prefer dev bundle but fallback to prod
		// do the opposite when WP_DEBUG is off.
		$bundle_path = $has_bundle ? '/static/bundle.js' : '/static/bundle.dev.js';
		if ( WP_DEBUG ){
			$bundle_path = $has_dev_bundle ? '/static/bundle.dev.js' : '/static/bundle.js';
		}

		// enqueue proper bundle
		wp_enqueue_script('bundle', get_template_directory_uri() . $bundle_path, array(), custom_latest_timestamp(), true);
    }
    add_action('wp_enqueue_scripts', 'custom_scripts', 10);

/*
 * Convenience function to generate timestamp based on latest edits. Used to automate cache updating
 */
    function custom_latest_timestamp() {

		// set base, find top level assets of static dir
        $base =  get_template_directory();
		$assets = array_merge(glob($base . '/static/*.js'), glob($base . '/static/*.css'));

		// get m time of each asset
		$stamps = array_map(function($path){
			return filemtime($path);
		}, $assets);

		// if valid return time of latest change, otherwise current time
        return rsort($stamps) ? reset($stamps) : time();
    }

/*
 * Style login page and dashboard
 */
	// Style the login page
	function custom_loginpage_logo_link($url) {
	     // Return a url; in this case the homepage url of wordpress
	     return get_bloginfo('url');
	}
	function custom_loginpage_logo_title($message) {
	     // Return title text for the logo to replace 'wordpress'; in this case, the blog name.
	     return get_bloginfo('name');
	}
	function custom_loginpage_styles() {
        wp_enqueue_style( 'login_css', get_template_directory_uri() . '/static/css/login.css' );
	}
	function custom_admin_styles() {
        wp_enqueue_style('admin-stylesheet', get_template_directory_uri() . '/static/css/admin.css');
	}
	add_filter('login_headerurl','custom_loginpage_logo_link');
	add_filter('login_headertitle','custom_loginpage_logo_title');
	add_action('login_head','custom_loginpage_styles');
    add_action('admin_print_styles', 'custom_admin_styles');

/*
 * Add post thumbnail into RSS feed
 */
    function rss_post_thumbnail($content) {
        global $post;

        if( has_post_thumbnail($post->ID) ) {
            $content = '<p><a href='.get_permalink($post->ID).'>'.get_the_post_thumbnail($post->ID).'</a></p>'.$content;
        }

		return $content;
	}
	add_filter('the_excerpt_rss', 'rss_post_thumbnail');

/*
 * Allow SVG uploads
 */
    function add_mime_types($mimes) {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }
    add_filter('upload_mimes', 'add_mime_types');


/*
 * Second featured image uploader (requires changes to admin.js too).
 * @see: https://codex.wordpress.org/Javascript_Reference/wp.media
 */
function custom_second_featured_image($post){

    // Meta key (need to update the save_metabox function below to reflect this too!)
    $meta_key = 'second_post_thumbnail';

    // Get WordPress' media upload URL
    $upload_link = esc_url( get_upload_iframe_src( 'image', $post->ID ) );

    // See if there's a media id already saved as post meta
    $image_id = get_post_meta( $post->ID, $meta_key, true );

    // Get the image src
    $image_src = wp_get_attachment_image_src( $image_id, 'post-thumbnail' );

    // For convenience, see if the array is valid
    $has_image = is_array( $image_src );

    ?>

        <div class="custom-meta custom-image-uploader">

            <!-- A hidden input to set and post the chosen image id -->
            <input class="custom-image-id" name="<?php echo $meta_key; ?>" type="hidden" value="<?php echo $image_id; ?>" />

            <!-- Image container, which is manipulated with js -->
            <div class="custom-image-container">
                <?php if ( $has_image ) : ?>
                    <img src="<?php echo $image_src[0] ?>"/>
                <?php endif; ?>
            </div>

            <!-- Add & remove image links -->
            <p class="hide-if-no-js">
                <a class="upload-custom-image <?php if ( $has_image  ) { echo 'hidden'; } ?>" href="<?php echo $upload_link ?>">
                    <?php _e('Set second featured image') ?>
                </a>
                <a class="delete-custom-image <?php if ( ! $has_image  ) { echo 'hidden'; } ?>" href="#">
                    <?php _e('Remove image') ?>
                </a>
            </p>

        </div>

    <?php
}
