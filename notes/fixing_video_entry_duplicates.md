## 11.22.2020
Okay, let's start going one by one and finish this build.

Okay so there is an issue with the video description and other metabox data that is not showing on the `Edit Video` page.

Let's see whats happening in the code.

```php
<?php
// ...
function  cpvm_display_video_details_meta_box($video) {
	$description =  strip_tags(esc_html(get_post_meta($video->ID, 'video_description', true)));
	$video_url =  esc_html(get_post_meta($video->ID, 'video_url', true));
	$quality =  esc_html(get_post_meta($video->ID, 'video_quality', true));
	$video_type =  esc_html(get_post_meta($video->ID, 'video_type', true));
	$release_date =  esc_html(get_post_meta($video->ID, 'video_release_date', true));
	$duration =  intval(get_post_meta($video->ID, 'video_duration', true));
	$episode_number =  intval(get_post_meta($video->ID, 'video_episode_number', true));
	//...
	
}
// ...
?>
```

So, the first thing we have going on right now is the php code is calling to get the data for our custom meta box.

This is not so much needed now to get the data because we are storing this information inside of our custom table `wp_cpvm_videos`.

Videos are connected to their post by the `post_id` column in the table. Let's set an sql query to get that data.

I'm going to need to use the global $wpdb and `prepare()` in order to safely insert dynamic data.

I added a new snippet of code inside the function that is supposed to load when you enter a post. Here it is.

```php
<?php
// ...
// Display Settings for the Video's meta_box in Admin menu
function  cpvm_display_video_details_meta_box($video) {
	global  $wpdb;
	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT  *  FROM {$wpdb->prefix}cpvm_videos WHERE post_id=%d", $video->ID)
	);
	echo  json_encode($results);
	// ...
	}
// ...
```

Simply, what we are doing is accessing the global WordPress database through `$wpdb` and entering an SQL statement for what we want.

Finally, we just translate it to a string and display it on the page for debugging.

Here are the `$results`

```json
[
  {
    "id": "31",
    "post_id": "380",
    "entity_id": "2",
    "title": "episode 2 sherlock",
    "thumbnail": "http://localhost:8888/wp-content/uploads/2020/10/golf-ball-near-hole.jpg",
    "description": "A modern update finds the famous sleuth and his doctor partner solving crime in 21st century London.",
    "is_movie": "0",
    "runtime": null,
    "season": "0",
    "episode": "0",
    "video_src": "",
    "upload_date": "2020-11-17 20:40:58",
    "release_date": "0000-00-00 00:00:00",
    "quality": "",
    "video_type": "",
    "view_count": null
  },
  {
    "id": "32",
    "post_id": "380",
    "entity_id": "2",
    "title": "episode 2 sherlock",
    "thumbnail": "http://localhost:8888/wp-content/uploads/2020/10/golf-ball-near-hole.jpg",
    "description": "A modern update finds the famous sleuth and his doctor partner solving crime in 21st century London.",
    "is_movie": "0",
    "runtime": null,
    "season": "1",
    "episode": "2",
    "video_src": "asdfasdf",
    "upload_date": "2020-11-18 17:49:23",
    "release_date": "0000-00-00 00:00:00",
    "quality": "asdfasdf",
    "video_type": "asdfasd",
    "view_count": null
  },
  {
    "id": "33",
    "post_id": "380",
    "entity_id": "2",
    "title": "episode 2 sherlock",
    "thumbnail": "http://localhost:8888/wp-content/uploads/2020/10/golf-ball-near-hole.jpg",
    "description": "A modern update finds the famous sleuth and his doctor partner solving crime in 21st century London.",
    "is_movie": "0",
    "runtime": null,
    "season": "0",
    "episode": "0",
    "video_src": "",
    "upload_date": "2020-11-18 17:55:11",
    "release_date": "0000-00-00 00:00:00",
    "quality": "",
    "video_type": "",
    "view_count": null
  }
]
```

So looking at this, I have a problem. The data is repeated. When it shouldn't be. 

It's only different by a few values, but that should not be happening. 

The point was to insert that post if it doesn't exist and update it if it does.

I need to verify that the `entity_id`, `post_id`, and `title` are not the same before continue to make an INSERT or an UPDATE.

Let's find the code segment that deals with saving the meta box contents into the custom video. Here is the function.

```php
<?php
// ...
function  cpvm_insert_postdata_into_videos($data) {
	global  $wpdb;
	$prefix = $wpdb->get_blog_prefix();
	// echo json_encode($data) . PHP_EOL;
  
	// insert data into the database table.
	$insert_data = array(
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
	$wpdb->insert($prefix  .  'cpvm_videos', $insert_data, $format);
	$cpvm_videos_id = $wpdb->insert_id;

	return  $cpvm_videos_id;
}
// ..
?>
```

Here, the first thing we are doing is accessing the global `$wpdb` variable in this scope.

Using the `$data` from the function argument we are taking the values we want from it and add it to an array we are going to add into the custom database table.

Once the data array is inserted to the table we are taking the ID of that entry and returning it for later use.

So it's in this code that we have to expand in order to create a checksum on whether to update or insert an entry.

```php
<?php
// ...
// We are going to need to check if an entry with a $post_id, $entity_id, and $post_title exist.
// We will return a Boolean of true or false.
function cpvm_is_video_entry_present($post_id, $entity_id, $post_title) {
	// First we need to access the global $wpdb class and access the DB prefix.
	global $wpdb;
	$prefix = $wpdb->get_blog_prefix();

	// We need to get results and determine if any entities exis using these arguments.
	$results = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}cpvm_videos
			WHERE post_id=%d
			AND entity_id=%d",
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
		// echo  "RESULTS::::::"  .  json_encode($results);
		return [false, intval($results->id)];
	} else {
		// echo  "RESULTS NOT EMPTY::::::"  .  json_encode($results->id);
		return [true, intval($results->id)];
	}
}
// ...
?>
```

Now, that we have written this method, we can add it to the function that actually saves our data; `cpvm_insert_postdata_into_videos($data)`.

```php
<?php
// ...
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
// ...
?>
```

With these new additions to the code we should be able to now have a checksum on whether an entry exists already or not. If an entry does exist then we will UPDATE, and if not, we will INSERT. 

After that checksum has been completed, we will return the id of the entry we just changed/added.

If we run the code, open a post that is already existing and edit any value we can see that it wont add another because one already exists. 

If I change the title however, that doesn't seem to be updating properly. I'll have to look into that.
