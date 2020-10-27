<?php
/*
Plugin Name: CouchPotato WP Video Management
Plugin URI: https://github.com/itsdevkay
Description: Content Management System for storing videos for movies and TV shows inside of your WordPress database. Also includes a custom api under /wp-json/cpvm/ to allow for 3rd party app usage in the case you wanted to create an app with WordPress as your CMS.
Version: 1.0
Author: itsDevKay
Author URI: http://virbuntu.com
License: GPLv2
*/

// Include custom api endpoints
include 'couchpotato-rest-api.php';

// Custom genre taxonomy rules
function cpvm_register_genres_taxonomy() {
    $labels = array(
        'name'                       => _x( 'Genres', 'taxonomy general name', 'textdomain' ),
        'singular_name'              => _x( 'Genre', 'taxonomy singular name', 'textdomain' ),
        'search_items'               => __( 'Search Genres', 'textdomain' ),
        'popular_items'              => __( 'Popular Genres', 'textdomain' ),
        'all_items'                  => __( 'All Genres', 'textdomain' ),
        'parent_item'                => null,
        'parent_item_colon'          => null,
        'edit_item'                  => __( 'Edit Genre', 'textdomain' ),
        'update_item'                => __( 'Update Genre', 'textdomain' ),
        'add_new_item'               => __( 'Add New Genre', 'textdomain' ),
        'new_item_name'              => __( 'New Genre Name', 'textdomain' ),
        'separate_items_with_commas' => __( 'Separate genres with commas', 'textdomain' ),
        'add_or_remove_items'        => __( 'Add or remove genres', 'textdomain' ),
        'choose_from_most_used'      => __( 'Choose from the most used genres', 'textdomain' ),
        'not_found'                  => __( 'No genres found.', 'textdomain' ),
        'menu_name'                  => __( 'Genres', 'textdomain' ),
    );
    $args = array(
        'hierarchical'          => true,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'genres' ),
    );

    register_taxonomy( 'video_genres', 'videos', $args );
}

// Custom series taxonomy rules
function cpvm_register_series_taxonomy() {
    $labels = array(
        'name'                       => _x( 'Series', 'taxonomy general name', 'textdomain' ),
        'singular_name'              => _x( 'Series', 'taxonomy singular name', 'textdomain' ),
        'search_items'               => __( 'Search Series', 'textdomain' ),
        'popular_items'              => __( 'Popular Series', 'textdomain' ),
        'all_items'                  => __( 'All Series', 'textdomain' ),
        'parent_item'                => null,
        'parent_item_colon'          => null,
        'edit_item'                  => __( 'Edit Series', 'textdomain' ),
        'update_item'                => __( 'Update Series', 'textdomain' ),
        'add_new_item'               => __( 'Add New Series', 'textdomain' ),
        'new_item_name'              => __( 'New Series Name', 'textdomain' ),
        'separate_items_with_commas' => __( 'Separate series with commas', 'textdomain' ),
        'add_or_remove_items'        => __( 'Add or remove series', 'textdomain' ),
        'choose_from_most_used'      => __( 'Choose from the most used series', 'textdomain' ),
        'not_found'                  => __( 'No series found.', 'textdomain' ),
        'menu_name'                  => __( 'Series', 'textdomain' ),
    );
    $args = array(
        'hierarchical'          => true,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'series' ),
    );

    register_taxonomy( 'video_series', 'videos', $args );
}

// Custom category taxonomy rules
function cpvm_register_category_taxonomy() {
    $labels = array(
        'name'                       => _x( 'Categories', 'taxonomy general name', 'textdomain' ),
        'singular_name'              => _x( 'Category', 'taxonomy singular name', 'textdomain' ),
        'search_items'               => __( 'Search Categories', 'textdomain' ),
        'popular_items'              => __( 'Popular Categories', 'textdomain' ),
        'all_items'                  => __( 'All Categories', 'textdomain' ),
        'parent_item'                => null,
        'parent_item_colon'          => null,
        'edit_item'                  => __( 'Edit Category', 'textdomain' ),
        'update_item'                => __( 'Update Category', 'textdomain' ),
        'add_new_item'               => __( 'Add New Category', 'textdomain' ),
        'new_item_name'              => __( 'New Category Name', 'textdomain' ),
        'separate_items_with_commas' => __( 'Separate categories with commas', 'textdomain' ),
        'add_or_remove_items'        => __( 'Add or remove categories', 'textdomain' ),
        'choose_from_most_used'      => __( 'Choose from the most used categories', 'textdomain' ),
        'not_found'                  => __( 'No series found.', 'textdomain' ),
        'menu_name'                  => __( 'Category', 'textdomain' ),
    );
    $args = array(
        'hierarchical'          => true,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'video-category' ),
    );
 
    register_taxonomy( 'video_category', 'videos', $args );
}

// Custom post type rules
function cpvm_register_post_type() {
    register_post_type( 'Videos',
        array(
            'labels'    => array(
                'name'                  => 'Videos',
                'singular_name'         => 'Video',
                'add_new'               => 'Add New',
                'add_new_item'          => 'Add New Video',
                'edit'                  => 'Edit',
                'edit_item'             => 'Edit video',
                'view'                  => 'View',
                'view_item'             => 'View video',
                'all_items'             => 'All videos',
                'search_items'          => 'Search videos',
                'not_found'             => 'No videos found.',
                'not_found_in_trash'    => 'No videos found in Trash.',
                'parent'                => 'Parent Video'
            ),
            'public'             => true,
            'has_archive'        => true,
            'menu_position'      => 20,
            'show_in_rest'       => true,
            'supports'           => array( 'title', 'editor', 'comments', 'thumbnail', 'custom-fields' ),
            'taxonomies'         => array('video_genres', 'video_series', 'video_category'),  
            'rewrite'            => array( 'slug' => 'video' ),   
            'menu_icon'          => plugins_url('videos20x20.png', __FILE__),   
        )
    );
}

// Registering custom taxonomies and post types together.
function cpvm_create_custom_types() {
    cpvm_register_genres_taxonomy();
    cpvm_register_series_taxonomy();
    cpvm_register_category_taxonomy();
    cpvm_register_post_type();

    // global $wp_rewrite;
    // $wp_rewrite->flush_rules();
}
add_action('init', 'cpvm_create_custom_types');

// Admin page initializer
function cpvm_admin_init() {
    add_meta_box(
        'cpvm_video_details_meta_box',
        'Video Details',
        'cpvm_display_video_details_meta_box',
        'videos', 'normal', 'high'
    );
}

// Display Settings for the Video's meta_box in Admin menu
function cpvm_display_video_details_meta_box($video) {
    /*
     * Metadata needed:
     * + cpvm_release_date (Y-m-d)      √
     * + cpvm_episode_number (int)      √
     * + cpvm_season_number (int)       √
     * + cpvm_duration (int)            √
     * + cpvm_ad_break_one (h:m:s)      Automate this based on duration and translate to (h:m:s)
     * + cpvm_ad_break_two (h:m:s)      Automate this based on duration and translate to (h:m:s)
     * + cpvm_ad_break_three (h:m:s)    Automate this based on duration and translate to (h:m:s)
     * + cpvm_video_url (str)           √
     * + cpvm_quality (str)             √
     * + cpvm_video_type (str)          √
     */

    $release_date   = esc_html(get_post_meta($video->ID, 'video_release_date', true));
    $episode_number = intval(get_post_meta($video->ID, 'video_episode_number', true));
    $season_number  = intval(get_post_meta($video->ID, 'video_season_number', true));
    $duration       = intval(get_post_meta($video->ID, 'video_duration', true));
    // $ad_break_one   = esc_html(get_post_meta($video->ID, 'cpvm_ad_break_one', true));
    // $ad_break_two   = esc_html(get_post_meta($video->ID, 'cpvm_ad_break_two', true));
    // $ad_break_three = esc_html(get_post_meta($video->ID, 'cpvm_ad_break_three', true));
    $video_url      = esc_html(get_post_meta($video->ID, 'video_url', true));
    $quality        = esc_html(get_post_meta($video->ID, 'video_quality', true));
    $video_type     = esc_html(get_post_meta($video->ID, 'video_type', true));

    ?>
    <table>
        <tr>
            <td style="width:100%;">Video Url</td>
            <td>
                <input type="text" size="80"
                    name="cpvm_video_url" placeholder="https://"
                    value="<?php echo $video_url; ?>"
                />
            </td>
        </tr>
        <tr>
            <td style="width:150px;">Quality Type</td>
            <td>
                <input type="text" size="80"
                    name="cpvm_video_quality" placeholder="HD"
                    value="<?php echo $quality; ?>"
                />
            </td>
        </tr>
        <tr>
            <td style="width:150px;">Video Format</td>
            <td>
                <input type="text" size="80"
                    name="cpvm_video_type" placeholder="HLS"
                    value="<?php echo $video_type; ?>"
                />
            </td>
        </tr>
        <tr>
            <td style="width:150px;">Release Date</td>
            <td>
                <input type="text" size="80"
                    name="cpvm_release_date" placeholder="2020-03-11"
                    value="<?php echo $release_date; ?>"
                />
            </td>
        </tr>
        <tr>
            <td style="width:150px;">Video Duration</td>
            <td>
                <input type="number" 
                    name="cpvm_video_duration" placeholder="5419"
                    value="<?php echo $duration; ?>"
                /> minutes
            </td>
        </tr>
        <tr>
            <td colspan="5"><hr /></td>
        </tr>
        <tr>
            <td colspan="2" style="width:100%; text-align:center;font-size: 12px;color:rgba(0,0,0,0.65);">If video is NOT part of a season or an episode, leave at 0.</td>
        </tr>
        <tr>
            <td style="width:150px;">Season Number</td>
            <td>
                <input type="number" name="cpvm_season_number"
                    value="<?php echo $season_number; ?>"
                />
            </td>
        </tr>
        <tr>
            <td style="width:150px;">Episode Number</td>
            <td>
                <input type="number" name="cpvm_episode_number"
                    value="<?php echo $episode_number; ?>"
                />
            </td>
        </tr>
    </table>
    <?php
}
add_action('admin_init', 'cpvm_admin_init');

function cpvm_save_video_meta_data($video_id, $video) {
    // Check post type for videos
    if ('videos' == $video->post_type) {
        // Store data in post meta table if present in post data
        if (isset($_POST['cpvm_video_url'])) {
            update_post_meta($video_id, 'video_url', 
                sanitize_text_field($_POST['cpvm_video_url'])
            );
        }
        if (isset($_POST['cpvm_video_quality'])) {
            update_post_meta($video_id, 'video_quality', 
                sanitize_text_field($_POST['cpvm_video_quality'])
            );
        }
        if (isset($_POST['cpvm_video_type'])) {
            update_post_meta($video_id, 'video_type', 
                sanitize_text_field($_POST['cpvm_video_type'])
            );
        }
        if (isset($_POST['cpvm_video_duration'])) {
            update_post_meta($video_id, 'video_duration',
                intval($_POST['cpvm_video_duration'])
            );
        }
        if (isset($_POST['cpvm_release_date'])) {
            update_post_meta($video_id, 'video_release_date',
                sanitize_text_field($_POST['cpvm_release_date'])
            );
        }
        if (isset($_POST['cpvm_season_number'])) {
            update_post_meta($video_id, 'video_season_number',
                intval($_POST['cpvm_season_number'])
            );
        }
        if (isset($_POST['cpvm_episode_number'])) {
            update_post_meta($video_id, 'video_episode_number',
                intval($_POST['cpvm_episode_number'])
            );
        }
    }
}
add_action('save_post', 'cpvm_save_video_meta_data', 10, 2);

?>
