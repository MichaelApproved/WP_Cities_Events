<?php
/*
Plugin Name: Custom Cities and Events
Plugin URI: http://www.yaconiello.com/
Description: A simple wordpress plugin for adding Event posts and managing city tax/terms
Version: 1.0
Author: Francis Yaconiello
Author URI: http://www.yaconiello.com
License: GPL2
*/
/*
Copyright 2012  Francis Yaconiello  (email : francis@yaconiello.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if(!class_exists('WP_Cities_Events'))
{
    class WP_Cities_Events
    {
        // Used to create the inner meta boxes and in the save function
        private $_event_meta = array(
            'when' => array(
                'label' => 'When',
                'help_text' => '',
                'widget' => 'text'
            ),
            'where' => array(
                'label' => 'Where',
                'help_text' => '',
                'widget' => 'text'
            ),
        );
        
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // register actions
            add_action('init', array(&$this, 'init'));
            add_action('admin_init', array(&$this, 'admin_init'));
        } // END public function __construct
        
        /**
         * Initialize the plugin
         */
        public function init()
        {
            // register a custom post type
            register_post_type('ce_event',
                array(
                    'labels' => array(
                        'name' => __(sprintf('%ss', ucwords(str_replace("_", " ", "ce_event")))),
                        'singular_name' => __(ucwords(str_replace("_", " ", "ce_event")))
                    ),
                    'public' => true,
                    'has_archive' => true,
                    'description' => __("Events associated by City"),
                    'supports' => array(
                        'title', 'editor', 'excerpt', 
                    ),
                )
            ); // http://codex.wordpress.org/Function_Reference/register_post_type for more options
            add_action('save_post', array(&$this, 'save_post'));
            
            // City Taxonomy args
            $args = array(
                'label' => 'Cities',
                'labels' => array(
                    'name' => 'Cities',
                    'singular_name' => 'City',
                    'search_items' => 'Search Cities',
                    'popular_items' => 'Popular Cities',
                    'all_items' => 'All Cities',
                    'parent_item' => 'Parent City',
                    'edit_item'  => 'Edit City',
                    'update_item' => 'Update City',
                    'add_new_item' => 'Add New City',
                    'new_item_name' => 'New City',
                    'separate_items_with_commas' => 'Separate Cities with commas',
                    'add_or_remove_items' => 'Add or remove Cities',
                    'choose_from_most_used' => 'Choose from most used Cities'
                ),
                'public' => false,
                'hierarchical' => true,
                'show_ui' => true,
                'show_in_nav_menus' => false,
                'args' => array('orderby' => 'term_order'),
                'rewrite' => array('slug' => 'city'),
                'query_var' => true
            );
            // attach City taxonomy to the Event Post Type
            register_taxonomy('ce_city', 'ce_event', $args); 
            // http://codex.wordpress.org/Function_Reference/register_taxonomy for more options
        }
        
        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {			
            // Add metaboxes
            add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
        } // END public function admin_init()

        /**
         * hook into WP's add_meta_boxes action hook
         */
        public function add_meta_boxes()
        {
            // Add this metabox to every selected post
            add_meta_box( 
                'id_wp_ce_events_section',
                sprintf('Event Information'),
                array(&$this, 'add_inner_meta_boxes'),
                'ce_event'
            );					
        } // END public function add_meta_boxes()

		/**
		 * called off of the add_meta_boxes function
		 */		
		public function add_inner_meta_boxes($post)
		{		
			// Render the job order metabox
			$event_meta = $this->_event_meta;
			include(sprintf("%s/templates/ce_event_metabox.php", dirname(__FILE__)));			
		} // END public function add_inner_meta_boxes($post)
		
        /**
        * Save the metaboxes for this custom post type
        */
        public function save_post($post_id)
        {
            // verify if this is an auto save routine. 
            // If it is our form has not been submitted, so we dont want to do anything
            if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            {
                return;
            }

            if($_POST['post_type'] == 'ce_event' && current_user_can('edit_post', $post_id))
            {
                foreach($this->_event_meta as $field => $data)
                {
                    // Update the post's meta field
                    update_post_meta($post_id, $field, $_POST[$field]);
                }
            }
            else
            {
                return;
            } // if($_POST['post_type'] == 'ce_event' && current_user_can('edit_post', $post_id))
        } // END public function save_post($post_id)

        /**
         * Activate the plugin
         */
        public static function activate()
        {
            // Do nothing
        } // END public static function activate

        /**
         * Deactivate the plugin
         */        
        public static function deactivate()
        {
            // Do nothing
        } // END public static function deactivate
    } // END class WP_Cities_Events
} // END if(!class_exists('WP_Cities_Events'))

if(class_exists('WP_Cities_Events'))
{
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('WP_Cities_Events', 'activate'));
    register_deactivation_hook(__FILE__, array('WP_Cities_Events', 'deactivate'));

    // instantiate the plugin class
    $wp_cities_events_plugin = new WP_Cities_Events();     
}