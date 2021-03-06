<?php
/*
Plugin Name: CouchPotato WP Video Management
Plugin URI: https://github.com/itsdevkay
Description: Content Management System for storing videos for movies and TV shows inside of your WordPress database. Also includes a custom api under /wp-json/cpvm/ to allow for 3rd party app usage in the case you wanted to create an app with WordPress as your CMS.
Version: 1.0
Author: Keanu Aaron
Author URI: http://virbuntu.com
License: GPLv2
*/

// Include custom api endpoints
include 'couchpotato-rest-api.php';

function cpvm_create_database_tables() {
    global $wpdb;
    $prefix = $wpdb->get_blog_prefix(); // . 'cpvm_entities';
    //$e_relationships = $wpdb->get_blog_prefix() . 'cpvm_entity_relationships';

    $entities = "CREATE TABLE IF NOT EXISTS " . $prefix . "cpvm_entities (
        `entity_id` int(20) unsigned NOT NULL AUTO_INCREMENT,
        `title` varchar(80) NOT NULL,
        `description` text NOT NULL,
        `thumbnail` text NOT NULL,
        PRIMARY KEY (entity_id)
        );";

    $videos_table = "CREATE TABLE IF NOT EXISTS " . $prefix . "cpvm_videos (
        `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
        `post_id` int(20) NOT NULL,
        `entity_id` int(20) NOT NULL,
        `title` varchar(80) NOT NULL,
        `thumbnail` text NOT NULL,
        `description` text NOT NULL DEFAULT '',
        `is_movie` tinyint NOT NULL DEFAULT 0,
        `runtime` int(10),
        `season` int(3) NOT NULL DEFAULT 0,
        `episode` int(4) NOT NULL DEFAULT 0,
        `video_src` text NOT NULL,
        `upload_date` timestamp NOT NULL DEFAULT now(),
        `release_date` datetime NOT NULL DEFAULT now(),
        `quality` varchar(10) NOT NULL DEFAULT '',
        `video_type` varchar(10) NOT NULL,
        `view_count` int(20),
        PRIMARY KEY (id)
        );";

    // require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $wpdb->query( $entities );
    $wpdb->query( $videos_table );
    file_put_contents( __DIR__ . '/my_loggg.txt', ob_get_contents() );
}
register_activation_hook( __FILE__, 'cpvm_create_database_tables' );

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
            'supports'           => array( 'title'/*, 'comments'*/, 'thumbnail' ),
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

    global $wp_rewrite;
    $wp_rewrite->flush_rules();
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
    global $wpdb;
    $prefix = $wpdb->get_blog_prefix();
    $results = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}cpvm_videos WHERE post_id=%d", $video->ID)
    );
    echo json_encode($results);

    $description    = strip_tags(esc_html(get_post_meta($video->ID, 'video_description', true)));
    $release_date   = esc_html(get_post_meta($video->ID, 'video_release_date', true));
    $episode_number = intval(get_post_meta($video->ID, 'video_episode_number', true));
    $season_number  = intval(get_post_meta($video->ID, 'video_season_number', true));
    $duration       = intval(get_post_meta($video->ID, 'video_duration', true));
    $video_url      = esc_html(get_post_meta($video->ID, 'video_url', true));
    $quality        = esc_html(get_post_meta($video->ID, 'video_quality', true));
    $video_type     = esc_html(get_post_meta($video->ID, 'video_type', true));

    ?>
    <table> 
    <tr>
            <td style="width:100%;">Video Description</td>
            <td>
                <textarea type="text" rows="4" col="50" style="width:100%;"
                    name="cpvm_video_description" placeholder="Start typing...">
                    <?php echo trim($description); ?>
                </textarea>
            </td>
        </tr>
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

function cpvm_handle_video_text_fields($field) {
    if (!intval($field)) {
        $text_field = sanitize_text_field($_POST[$field]);
        return $text_field;
    }
    $int_field = intval($_POST[$field]);
    return $int_field;
}

function cpvm_save_video_meta_data($video_id, $video) {
    // Check post type for videos
    if ('videos' == $video->post_type) {
        // saving series term_id and term_name to meta data for future reference
        global $wpdb;
        $prefix = $wpdb->get_blog_prefix();
        $series_tax = wp_get_post_terms($video_id, 'video_series');
        $thumbnail_url = cpvm_get_video_thumbnail($video_id);
        
        // echo $description[0] . '::::::::LOG:::::::::';

        /* This is the meta data I want to look out for with this video post_type. 
         */
        $url_args = array(
            'cpvm_video_url',
            'cpvm_video_quality',
            'cpvm_video_type',
            // 'cpvm_video_duration',
            'cpvm_release_date',
            'cpvm_season_number',
            'cpvm_episode_number',
            'cpvm_video_description'
        );

        // This loop takes every value in the url_args array and sends it to get updated in another method.
        foreach ($url_args as $meta_key) {
            if (isset($_POST[$meta_key])) {
                $meta_value = cpvm_handle_video_text_fields($meta_key);
                update_post_meta($video_id, $meta_key, $meta_value);
            }
        }

        if ($series_tax) { // shows
            // if shows use taxonomy description
            $terms = get_the_terms($video_id, 'video_series');
            // echo json_encode($terms);
            $description = $terms[0]->description;
            $video_description = get_post_meta($video_id, 'cpvm_video_description');
            $video_description = $video_description[0];
            echo "VIDEO_DESCRIPTION:::::::" . $video_description;

            // send video_term_name to entity title in entities table with this video_id as video_id
            $entity_id;
            $entity_title = $series_tax[0]->name;

            // Check if an entity_title = $entity_title exists in $prefix . 'cpvm_entities'
            $entity_exists = "SELECT * FROM " . $prefix . "cpvm_entities WHERE title = '$entity_title'";
            
            // if not then insert into $prefix . 'cpvm_entities'
            $entity_id = cpvm_table_manager($entity_exists, $description, $entity_title, $thumbnail_url);

            $video_data = array(
                'post_id' => $video_id,
                'title' => $video->post_title,
                'thumbnail' => cpvm_get_video_thumbnail($video_id),
                'description' => $video_description,
                'season' => get_post_meta($video_id, 'cpvm_season_number'),
                'episode' => get_post_meta($video_id, 'cpvm_episode_number'),
                'video_src' => get_post_meta($video_id, 'cpvm_video_url'),
                'release_date' => get_post_meta($video_id, 'cpvm_release_date'),
                'video_type' => get_post_meta($video_id, 'cpvm_video_type'),
                'quality' => get_post_meta($video_id, 'cpvm_video_quality'),
            );


            $video_data['is_movie'] = 0;
            $video_data['entity_id'] = $entity_id;
            $cpvm_video_id = cpvm_insert_postdata_into_videos($video_data);

 
        } else { // movies
            $entity_title   = $video->post_title;
            $description    = get_post_meta($video_id, 'cpvm_video_description');
            // $thumbnail_url  = (!$thumbnail_url)? '': $thumbnail_url;
            echo $thumbnail_url;
            $entity_exists  = "SELECT * FROM " . $prefix . "cpvm_entities WHERE title = '$entity_title'";
            
            // if not then insert into $prefix . 'cpvm_entities'
            $entity_id = cpvm_table_manager($entity_exists, $description, $entity_title, $thumbnail_url);
            
            $video_data = array(
                'post_id' => $video_id,
                'title' => $video->post_title,
                'thumbnail' => cpvm_get_video_thumbnail($video_id),
                'description' => $description,
                'season' => get_post_meta($video_id, 'cpvm_season_number'),
                'episode' => get_post_meta($video_id, 'cpvm_episode_number'),
                'video_src' => get_post_meta($video_id, 'cpvm_video_url'),
                'release_date' => get_post_meta($video_id, 'cpvm_release_date'),
                'video_type' => get_post_meta($video_id, 'cpvm_video_type'),
                'quality' => get_post_meta($video_id, 'cpvm_video_quality'),
            );
            $video_data['entity_id'] = $entity_id;
            $video_data['is_movie'] = 1;
            $cpvm_video_id = cpvm_insert_postdata_into_videos($video_data);
        }
        file_put_contents( __DIR__ . '/my_loggg.txt', ob_get_contents() );
    }
}


function cpvm_get_video_thumbnail($video_id) {
    try {
        $thumbnail_id = get_post_meta($video_id, '_thumbnail_id');
        $thumbnail_id = (isset($thumbnail_id))? $thumbnail_id : null;
        // $thumbnail_id =  json_encode($thumbnail_id);
        echo "THUMBNAIL_ID:::::" . $thumbnail_id[0] . PHP_EOL;

        // get thumbnail guid
        $thumbnail_guid = get_post(intval($thumbnail_id[0]));
        echo json_encode($thumbnail_guid);
        $thumbnail_guid = ($thumbnail_guid)? $thumbnail_guid->guid : '';
        echo json_encode($thumbnail_guid) .'LOG-thumbnail-url' . PHP_EOL;
        return $thumbnail_guid;
    } catch (exception $e) {
        echo 'ERROR: ' . $e;
        return false;
    }
}

// We are going to need to check if an entry with a $post_id, $entity_id, and $post_title exist.
// We will return a Boolean of true or false.
function cpvm_is_video_entry_present($post_id, $entity_id, $post_title) {
	// First we need to access the global $wpdb class and access the DB prefix.
	global $wpdb;
	$prefix = $wpdb->get_blog_prefix();

	// We need to get results and determine if any entities exis using these arguments.
	$results = $wpdb->get_row(
		$wpdb->prepare("
			SELECT * FROM {$wpdb->prefix}cpvm_videos
			WHERE post_id=%d
			AND entity_id=%d
            ", 
            $post_id,
            $entity_id,
        )
	);
	
	// Now we need to determine if it's empty. If empty, return true, else, false.
	// The idea here is so we can access the id of the video entry we want to update
	// in the case that the video entry already exists.
	if (empty($results)) {
		// The idea here is so we can access the id of the video entry we want to update
        // in the case that the video entry already exists.
        echo "RESULTS::::::" . json_encode($results);
		return [false, intval($results->id)];
	} else {
        echo "RESULTS NOT EMPTY::::::" . json_encode($results->id);
		return [true, intval($results->id)];
	}
}


/* This method takes values that it will be inserting into the database using the global $wpdb variable.
 */
function cpvm_table_manager($entity_exists, $description, $entity_title, $thumbnail_url) {
    global $wpdb;
    $prefix = $wpdb->get_blog_prefix();
    $query = $wpdb->query($entity_exists);
    // echo json_encode($description[0]) . ' : ' . $thumbnail_url;//[0] . '::::::::LOG:::::::::';
        if (!$query) {
            $data = array(
                'title'         => $entity_title, 
                'description'   => $description,
                'thumbnail'     => $thumbnail_url
            );
            // echo $data;
            $format = array('%s','%s', '%s');
            $wpdb->insert($prefix . 'cpvm_entities', $data, $format);
            $entity_id = $wpdb->insert_id;
            // echo $entity_id . ' : ENTITY ID';

            return $entity_id;
        } else {
            $results = $wpdb->get_results($entity_exists);
            $entity_id = $results[0]->entity_id;
            return $entity_id;
        }
}

/* I want to create a new function similiar to the one above that will insert
 * all my needed data into my custom videos table.
 */
function  cpvm_insert_postdata_into_videos($data) {
	global  $wpdb;
	$prefix = $wpdb->get_blog_prefix();
	// echo json_encode($data) . PHP_EOL;
	
	// So right here in this area, we need to add our call to the function we just created.
	// Depending on whether we get a true or false we will handle that in an if statement.
	$does_video_exist = cpvm_is_video_entry_present($data['post_id'], $data['entity_id'], $data['title']);
	$does_video_exist = $does_video_exist[0];
	$video_entry_id = $does_video_exist[1];
	
	$table_data = array(
		'post_id' => $data['post_id'],
		'entity_id' => $data['entity_id'],
		'title' => $data['title'],
		'thumbnail' => $data['thumbnail'],
		'description' => $data['description'],
		'is_movie' => intval($data['is_movie']),
		'season' => intval($data['season'][0]),
		'episode' => intval($data['episode'][0]),
		'video_src' => $data['video_src'][0],
		'release_date' => $data['release_date'][0],
		'video_type' => $data['video_type'][0],
		//'view_count' => $data['view_count'] // we shouldn't touch this when updating data at this point.
		'quality' => $data['quality'][0]
	);
	$format = array('%d', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s',);
	$where_array = array('id' => $video_entry_id);	
	$where_format = '%d';
	
	if ($does_video_exist) {
		// We need to perfom an UPDATE
		// We have the id of the custom videos table row with $video_entry_id.
		# wpdb::update(  string $table,  array $data,  array $where,  array|string $format = null,  array|string $where_format = null )
		$wpdb->update(
			$prefix . "cpvm_videos",
			$table_data,
			$where_array,
			$format,
			$where_format
		);
		$cpvm_videos_id = $video_entry_id;
	} else {
		// We need to perform an INSERT
		$wpdb->insert($prefix  .  'cpvm_videos', $table_data, $format);
		$cpvm_videos_id = $wpdb->insert_id;
	}
	return  $cpvm_videos_id;
}

add_action('save_post', 'cpvm_save_video_meta_data', 10, 2);

?>
