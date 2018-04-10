<?php
/*
 * Add vue.js router to rest-easy data
 */
	function add_routes_to_json($jsonData){

        // Get the name of the category base. Default to "categories"
        $category_base = get_option('category_base');
		if( empty($category_base) ){
			$category_base = 'category';
		}

		// Find all pages with custom_vuepress_template set
		$args = array(
			'posts_per_page'   => -1,
			'orderby'          => 'menu_order',
			'order'            => 'ASC',
			'meta_key'         => 'custom_vuepress_template',
			'meta_value'       => '',
			'post_type'        => array('post', 'page')
		);
		$all_pages_with_custom_templates = get_posts($args);

		// Filter out pages with vuepress custom template set to Default
		$filtered_pages_with_custom_templates = array_filter(
			$all_pages_with_custom_templates,
			function( $page ){
				return $page->custom_vuepress_template !== 'Default';
			}
		);

		// Build out router for pages with custom templates
		$custom_template_routes = array();
		foreach( $filtered_pages_with_custom_templates as $page ){
			$page_url = get_permalink($page);
			$custom_template_routes[wp_make_link_relative($page_url)] = $page->custom_vuepress_template;
		}

        // build out router table to be used with Vue
        $programmed_routes = array(

			// Your custom routes go here.
			// If a custom route is defined for a specific post, it will override any entry in this array.

            // Per-site
            // '/path'                              => 'VueComponent',
            // '/path/:var'                         => 'ComponentWithVar'
            // '/path/*/:var'                       => 'WildcardAndVar'

            // Probably unchanging
            // ''                                      => 'FrontPage',
            // '/' . $category_base                    => 'Archive',

			// Fallback
            '*'                                		=> 'Default'

        );

		$jsonData['routes'] = array_merge( $custom_template_routes, $programmed_routes );

		return $jsonData;
	}
	add_filter('rez_build_all_data', 'add_routes_to_json');
