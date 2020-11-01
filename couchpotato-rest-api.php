<?php

// Register Custom Routes
add_action('rest_api_init', 'cpvm_register_routes');
function cpvm_register_routes() {
    /* Collects all video post_types with no filters */
    register_rest_route('cpvm', 'videos/all', array(
        'methods'   => WP_REST_Server::READABLE,
        'callback'  => 'cpvm_collect_all_videos',
    ));

    /* Collects all video post_types with no filters
     * aside from category, (ex. ../movies/all, ../series/all).
     */
    // register_rest_route('cpvm', 'videos/movies/all', array(
    //     'methods'   => WP_REST_Server::READABLE,
    //     'callback'  => 'cpvm_collect_all_movies',
    // ));
    register_rest_route('cpvm', 'videos/series/all', array(
        'methods'   => WP_REST_Server::READABLE,
        'callback'  => 'cpvm_collect_all_series',
    ));
    register_rest_route('cpvm', 'videos/shorts/all', array(
        'methods'   => WP_REST_Server::READABLE,
        'callback'  => 'cpvm_collect_all_series',
    ));

    /* Collects all video post_types with no filters 
     * aside from categories and the genre searched for.
     */
    register_rest_route('cpvm', 'videos/all/genre=(?P<genre>[a-zA-Z0-9-]+)', array(
        'methods'   => WP_REST_Server::READABLE,
        'callback'  => 'cpvm_collect_videos_by_genre',
    ));
    register_rest_route('cpvm', 'videos/movies/genre=(?P<genre>[a-zA-Z0-9-]+)', array(
        'methods'   => WP_REST_Server::READABLE,
        'callback'  => 'cpvm_collect_movies_by_genre',
    ));
    register_rest_route('cpvm', 'videos/series/genre=(?P<genre>[a-zA-Z0-9-]+)', array(
        'methods'   => WP_REST_Server::READABLE,
        'callback'  => 'cpvm_collect_series_by_genre',
    ));
    register_rest_route('cpvm', 'videos/shorts/genre=(?P<genre>[a-zA-Z0-9-]+)', array(
        'methods'   => WP_REST_Server::READABLE,
        'callback'  => 'cpvm_collect_shorts_by_genre',
    ));

    register_rest_route('cpvm', 'videos/search/all/q=(?P<q>[a-zA-Z0-9-]+)', array(
        'methods'   => WP_REST_Server::READABLE,
        'callback'  => 'cpvm_search_videos',
    ));

}

// function cpvm_collect_videos_by_alphabet($data) {
// }
// function cpvm_collect_movies_by_alphabet($data) {
// }
// function cpvm_collect_series_by_alphabet($data) {
// }
// function cpvm_collect_shorts_by_alphabet($data) {
// }

// function cpvm_search_videos($data) {
// }
// function cpvm_search_movies($data) {
// }
// function cpvm_search_series($data) {
// }
// function cpvm_search_shorts($data) {
// }

// function cpvm_generate_roku_json() {
// }

/* Gathers all custom post types that are 
 * movies, series, shorts, etc. and filters them
 * by the genre taxonomy.
 * 
 * @args - query data (ex. /cpvm/videos/movies/genre=action)
 */
function cpvm_collect_videos_by_genre($data) {
    $genre = trim($data['genre']);
    $args = array(
        'numberposts'      => -1,
        'orderby'          => 'date',
        'order'            => 'DESC',
        'post_type'        => 'videos',
        'tax_query' => array(
            array(
                'taxonomy'  => 'video_genres',
                'field'     => 'slug',
                'terms'     => strtolower($genre)
            )
        )
    );
    $response = cpvm_organize_videos($args);
    return rest_ensure_response($response);
}

/* Gathers all custom post types that are 
 * categorized as 'series' and filters them
 * by the genre taxonomy.
 * 
 * @args - query data (ex. /cpvm/videos/series/genre=comedy)
 */
function cpvm_collect_series_by_genre($data) {
    $genre = trim($data['genre']);
    $args = array(
        'numberposts'      => -1,
        'orderby'          => 'date',
        'order'            => 'DESC',
        'post_type'        => 'videos',
        'tax_query' => array(
            array(
                'taxonomy'  => 'video_genres',
                'field'     => 'slug',
                'terms'     => strtolower($genre)
            ),
            array(
                'taxonomy' => 'video_category',
                'field' => 'slug',
                'terms' => 'series'
            )
        )
    );
    $response = cpvm_organize_videos($args);
    return rest_ensure_response($response);
}
/* Gathers all custom post types that are 
 * categorized as 'series' and filters them
 * by the genre taxonomy.
 * 
 * @args - query data (ex. /cpvm/videos/series/genre=comedy)
 */
function cpvm_collect_shorts_by_genre($data) {
    $genre = trim($data['genre']);
    $args = array(
        'numberposts'      => -1,
        'orderby'          => 'date',
        'order'            => 'DESC',
        'post_type'        => 'videos',
        'tax_query' => array(
            array(
                'taxonomy'  => 'video_genres',
                'field'     => 'slug',
                'terms'     => strtolower($genre)
            ),
            array(
                'taxonomy' => 'video_category',
                'field' => 'slug',
                'terms' => 'shorts'
            )
        )
    );
    $response = cpvm_organize_videos($args);
    return rest_ensure_response($response);
}

/* Gathers all custom post types that are 
 * categorized as 'movies' and filters them
 * by the genre taxonomy.
 * 
 * @args - query data (ex. /cpvm/videos/movies/genre=action)
 */
function cpvm_collect_movies_by_genre($data) {
    $genre = trim($data['genre']);
    $args = array(
        'numberposts'      => -1,
        'orderby'          => 'date',
        'order'            => 'DESC',
        'post_type'        => 'videos',
        'tax_query' => array(
            array(
                'taxonomy'  => 'video_genres',
                'field'     => 'slug',
                'terms'     => strtolower($genre)
            ),
            array(
                'taxonomy' => 'video_category',
                'field' => 'slug',
                'terms' => 'movies'
            )
        )
    );
    $response = cpvm_organize_videos($args);
    return rest_ensure_response($response);
}

/* Gathers all custom post types that are 
 * categorized as 'movies'
 */
function cpvm_collect_all_movies() {
    $args = array(
        'numberposts'      => -1,
        'orderby'          => 'date',
        'order'            => 'DESC',
        'post_type'        => 'videos',
        'tax_query' => array(
            array(
                'taxonomy' => 'video_category',
                'field' => 'slug',
                'terms' => 'movies'
            )
        )
    );
    $response = cpvm_organize_videos($args);
    return rest_ensure_response($response);
}

/* Gathers all custom post types that are 
 * categorized as 'series'
 */
function cpvm_collect_all_series() {
    $args = array(
        'numberposts'      => -1,
        'orderby'          => 'date',
        'order'            => 'DESC',
        'post_type'        => 'videos',
        'tax_query' => array(
            array(
                'taxonomy' => 'video_category',
                'field' => 'slug',
                'terms' => 'series'
            )
        )
    );
    $response = cpvm_organize_videos($args);
    return rest_ensure_response($response);
}

/* Gathers all custom post types that are 
 * categorized as 'shorts'
 */
function cpvm_collect_all_shorts() {
    $args = array(
        'numberposts'      => -1,
        'orderby'          => 'date',
        'order'            => 'DESC',
        'post_type'        => 'videos',
        'tax_query' => array(
            array(
                'taxonomy' => 'video_category',
                'field' => 'slug',
                'terms' => 'shorts'
            )
        )
    );
    $response = cpvm_organize_videos($args);
    return rest_ensure_response($response);
}

/* Gathers all custom post types of videos */
function cpvm_collect_all_videos() {
    global $wpdb;
    $prefix = $wpdb->get_blog_prefix();
    $query = "SELECT * FROM " . $prefix . "cpvm_entities";
    $response = $wpdb->get_results($query);

    return rest_ensure_response($response);
}

/* Gathers all post based on the arguments
 * the function receives, then organizes
 * the data properly into the JSON object. 
 * 
 * @args - arguments for 'get_post' function
 * @return - returns the structured JSON object
 */
function cpvm_organize_videos($args) {
    $posts = get_posts($args);
    $response = array();

    foreach ($posts as $post) {
        $video_url = get_post_meta($post->ID, 'video_url', true);
        $video_quality = get_post_meta($post->ID, 'video_quality', true);
        $video_duration = get_post_meta($post->ID, 'video_duration', true);
        $video_type = get_post_meta($post->ID, 'video_type', true);
        $video_season_number = get_post_meta($post->ID, 'video_season_number', true);
        $video_episode_number = get_post_meta($post->ID, 'video_episode_number', true);
        $video_release_date = get_post_meta($post->ID, 'video_release_date', true);
        
        $video_category = get_the_terms($post->ID, 'video_category');
        $video_series = get_the_terms($post->ID, 'video_series');
        $video_genres = get_the_terms($post->ID, 'video_genres');

        $video_category = cpvm_filter_through_list($video_category);
        $video_series   = cpvm_filter_through_list($video_series);
        $video_genres   = cpvm_filter_through_list($video_genres);
        
        $post_response = array(
            'ID'                => $post->ID,
            'post_date'         => $post->post_date,
            'post_title'        => $post->post_title,
            'url_string'        => $post->post_name,
            'post_content'      => stripslashes(strip_tags($post->post_content)),
            'post_modified'     => $post->post_modified,
            'post_type'         => $post->post_type,
            'video_url'         => $video_url,
            'video_quality'     => $video_quality,
            'video_duration'    => $video_duration,
            'video_type'        => $video_type,
            'video_season_number'   => $video_season_number,
            'video_episode_number'  => $video_episode_number,
            'video_release_date'    => $video_release_date,
            'video_category'        => $video_category,
            'video_series'          => $video_series,
            'video_genres'          => $video_genres,
        );

        array_push($response, $post_response);
    }
    json_encode($response);
    return $response;
}

/* Used as a filter to get post terms
 * and return a list of term names.
 * 
 * @args - WP_Terms object from 'get_the_terms'
 * @return - returns a list of just the terms name.
 */
function cpvm_filter_through_list($terms) {
    $filtered_terms = array();

    foreach ((array) $terms as $term) {
        try {
            array_push(
                $filtered_terms, $term->name
            );
        } catch (exception $e) {
           // echo $e;
        }
    }
    return $filtered_terms;
}

?>
