<?php

/**
 * Post Type creating library for WordPress
 * @author Sachit Tandukar
 * @version 1.1.0
 */
namespace Moksha;

use ICanBoogie\Inflector;

session_start();

class PostType
{

    /**
     * @var Inflector
     */
    private $inflector;

    /**
     * The holder of custom post type and their custom details
     * @var string
     */
    private $customPostType = array();

    /**
     * Initialize inflector class for pluralizing words
     */
    public function __construct()
    {
        $this->inflector = Inflector::get();
        add_action('plugins_loaded', array($this, 'l10n'));
    }

    /**
     * Sets default values, registers the passed custom post type
     *
     * @param string $name The name of the desired post type.
     * @param array @custom_post_type_args Override the options.
     */
    public function createPostType($name)
    {
        if (!isset($_SESSION['taxonomy_data'])) {
            $_SESSION['taxonomy_data'] = array();
        }
        $this->customPostType = $name;
        $this->init(array(&$this, 'registerPostType'));
    }

    /**
     * Helper method, that attaches a passed function to the 'init' WP action
     *
     * @param array $callback Passed callback function.
     */
    public function init($callback)
    {
        add_action('init', $callback, 999);
    }


    /**
     * Registers a new post type in the WP db.
     */
    public function registerPostType()
    {
        foreach ($this->customPostType as $postType => $postTypeArgs) {
            $singularName = ucwords(strtolower($postType));
            $pluralName = $this->inflector->pluralize($singularName);
            $labels = array(
                'name' => $pluralName,
                'singular_name' => $singularName,
                'add_new' => sprintf(__('Add New %s', '_mok'), $singularName),
                'add_new_item' => sprintf(__('Add New %s', '_mok'), $singularName),
                'edit_item' => sprintf(__('Edit %s', '_mok'), $singularName),
                'new_item' => sprintf(__('New %s', '_mok'), $singularName),
                'all_items' => sprintf(__('All %s', '_mok'), $pluralName),
                'view_item' => sprintf(__('View %s', '_mok'), $singularName),
                'search_items' => sprintf(__('Search %s', '_mok'), $pluralName),
                'not_found' => sprintf(__('No %s', '_mok'), $pluralName),
                'not_found_in_trash' => sprintf(__('No %s found in Trash', '_mok'), $pluralName),
                'parent_item_colon' => sprintf(__('Parent %s:', '_mok'), $singularName),
                'menu_name' => $pluralName,
            );
            $args = array(
                'labels' => $labels,
                'supports' => array('title', 'editor', 'thumbnail'),
                'taxonomies' => array(),
                'hierarchical' => false,
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_nav_menus' => true,
                'show_in_admin_bar' => true,
                'menu_position' => 20,
                'can_export' => true,
                'has_archive' => true,
                'exclude_from_search' => false,
                'publicly_queryable' => true,
                'rewrite' => array('slug' => strtolower(str_replace(' ', '-', $singularName))),
                'capability_type' => 'post',
            );
            $args = array_merge($args, $postTypeArgs);
            register_post_type(strtolower(str_replace(' ', '-', $singularName)), $args);
        }
    }


    /**
     * Registers a new taxonomy, associated with the instantiated post type.
     *
     * @param array $customPostTaxonomy Custom post types taxonomy and its defaults
     *
     */
    public function addTaxonomy($customPostTaxonomy)
    {
        foreach ($customPostTaxonomy as $postType => $taxonomy) {
            foreach ($taxonomy as $taxonomyName => $taxonomyOptions) {
                $this->init(function () use ($taxonomyName, $postType, $taxonomyOptions) {
                    $singularName = ucwords(strtolower($taxonomyName));
                    $pluralName = (string)$this->inflector->pluralize($singularName);
                    $options = array_merge(
                        array(
                            'labels' => array(
                                'name' => $pluralName,
                                'singular_name' => $singularName,
                                'search_items' => sprintf(__('Search %s', '_mok'), $pluralName),
                                'all_items' => sprintf(__('All %s', '_mok'), $pluralName),
                                'parent_item' => sprintf(__('Parent %s', '_mok'), $singularName),
                                'parent_item_colon' => sprintf(__('Parent %s:', '_mok'), $singularName),
                                'edit_item' => sprintf(__('Edit %s', '_mok'), $singularName),
                                'update_item' => sprintf(__('Update %s', '_mok'), $singularName),
                                'add_new_item' => sprintf(__('Add New %s', '_mok'), $singularName),
                                'new_item_name' => sprintf(__('New %s Name', '_mok'), $singularName),
                                'popular_items' => sprintf(__('Popular %s', '_mok'), $pluralName),
                                'separate_items_with_commas' => sprintf(__('Separate %s with commas', '_mok'),
                                    $pluralName),
                                'add_or_remove_items' => sprintf(__('Add or remove %s', '_mok'), $pluralName),
                                'choose_from_most_used' => sprintf(__('Choose from the most used %s', '_mok'),
                                    $pluralName),
                                'menu_name' => sprintf(__('%s', '_mok'), $pluralName),
                                'view_item' => sprintf(__('View %s', '_mok'), $singularName),
                                'not_found' => __('Not Found', '_mok')
                            ),
                            'hierarchical' => true,
                            'show_ui' => true,
                            'query_var' => true,
                            'show_in_menu' => null,
                            'show_in_nav_menus' => null,
                            'show_tagcloud' => null,
                            'show_admin_column' => false,
                            'rewrite' => array('slug' => strtolower(str_replace(' ', '-', $singularName)))
                        ), $taxonomyOptions);
                    register_taxonomy(strtolower(str_replace(' ', '-', $singularName)), $postType, $options);
                });
            }
        }
    }

    public function l10n()
    {
        $locale = apply_filters('plugin_locale', get_locale(), '_mok');
        $mofile = dirname(__FILE__) . '../languages/mok-cpt-' . $locale . '.mo';
        load_textdomain('_mok', $mofile);
    }
}