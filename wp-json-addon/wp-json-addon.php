<?php
/**
 * Plugin Name: WP JSON Addons
 * Plugin URI: http://coachbirgit.de
 * Description: extends WP-API
 * Version: 1.0
 * Author: Birgit Olzem
 * Author URI: http://coachbirgit.de
 */

// extends the WP API for the plugin food-and-drink-menu

class fdm_menu
{
    public function __construct()
    {
        $version = '2.0';
        $namespace = 'speisekarte/' . $version;
        $base = 'fdm-menu';
        register_rest_route($namespace, '/' . $base, array(
            'methods' => 'GET',
            'callback' => array($this, 'get_fdm_menu_items'),
        ));
    }

    public function get_fdm_menu_items($object)
    {
	   $cats = array();
		foreach ( get_terms('fdm-menu-section') as $term ) {
			$cat = new stdClass;
			$cat->ID = (int)$term->term_id;
			$cat->name = $term->name;
			$cat->description = $term->description;
			$cat->term_order = $term->term_order;
			$cats[] = $cat;
		}
		$items = array();
		$posts_list = get_posts( array('post_type' => 'fdm-menu-item', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'menu_order post_title', 'order' => 'ASC') );
		foreach ( $posts_list as $post ) {
			$item = new stdClass;
			$item->ID = $post->ID;
			$item->category = wp_get_object_terms($post->ID, 'fdm-menu-section', array('fields' => 'ids'));
			$item->title = $post->post_title;
			$item->description = $post->post_content;
			$item->price = get_post_meta($post->ID, 'fdm_item_price', true);
			$item->price2 = get_post_meta($post->ID, 'fdm_item_price_reduced', true);
			$items[] = $item;
		}
		$data = new stdClass;
		$data->categories = $cats;
		$data->items = $items;
        return new WP_REST_Response($data, 200);
    }
}

add_action('rest_api_init', function () {
    $fdm_menu = new fdm_menu;
});
