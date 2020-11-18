A lot of my work suffers most of the time because of one underlying key factor. _Planning_.

If I had to name one of m biggest weakness', it's not preparing a plan. I've always, as it was said to me yesterday, thought short-term. In the sense of, I'll figure it out as I go. It usually works, however the issue arises when I get stuck in the loop of "okay, what needs to be done left?"

Without a proper plan, I'm not reaching towards an end-goal, and without an end-goal, where am I going?

## 11.17.2020

Let's discuss the end goal and what the ideal scenario is.

A user logs in to verify they're allowed to create/edit/delete posts.

The user should be able to enter the required data and hit save.

Once that save button is pressed, the data will be stored in the default `wp_posts` and `wp_postmeta`. Those values also need to be `INSERT`ed `INTO wp_cpvm_videos` table.

Once the actual video post_type is deleted, (e.x. hook => `delete_post`), we need to grab our $video->ID and `DELETE FROM wp_cpvm_videos WHERE post_id = $post->ID`

When that is completed, that is all that needs to be done as far as getting the data into the database.

The next task to work on is to jump to the API. Let's go over that now.

Our custom database table is not, by default, part of the WordPress Database Engine, because of this, I can't use much of the very helpful WordPress functions and classes. Instead, I'll have to make very good use of SQL queries and the `global $wpdb` variable which allows me to run SQL queries.

The most ideal situation as the custom API needs to return a JSON Object is to:  

**Make a request to** `/wp-json/cpvm/all` : The returned JSON object should look similar to this:

```
[{
	id: 1,
	title: $title,
	synopsis: $synopsis,
	thumbnail: $thumbnail,
	isMovie: 0,
	content: [
		seasons: [
			0: [{
				episodes: [
					0: [{
						title: $ep_title,
						synopsis: $ep_desc,
						thumbnail: $thumbnail,
						episode: $episode,
						upload_date: $upload_date,
						release_date: $release_date,
						video_src: $video_src,
						duration: $ep_duration,
						quality: $ep_quality,
						video_type: $video_type,
						rating: $ep_rating,
						view_count: $view_count,
					}],
					1: [],
				],
			}],
			1: [],
		],
	],
},
{
	id: 2,
	title: $title,
	synopsis: $synopsis,
	thumbnail: $thumbnail,
	isMovie: 1,
	content: [{
		upload_date: $upload_date,
		release_date: $release_date,
		video_src: $video_src,
		duration: $ep_duration,
		quality: $ep_quality,
		video_type: $video_type,
		rating: $ep_rating,
		view_count: $view_count,
	}]
},]
```

There may be some revisions needed to this JSON Object. Maybe there is even a better way, I just can't see it right now as I'm multitasking.

> ==As of now, I'm thinking I should ignore trying to load everything into a single call. The load would be tremendous. Remember, I once had over 50,000 entities. Imagine calling all that and then formatting properly into a JSON Object. It would take forever. The ideal scenario for that API call should be rethought.==

## 11.18.2020
Last night I added more code to the custom plugin. I integrated the ability to insert the video data inside of the custom `wp_cpvm_videos` table.

There are a few things left that need to be done before continuing on with the API.

- When entering a description for a Series Taxonomy, like Sherlock Holmes for example, I want to take that description and on that taxonomy save, put that description with the entity it belongs to.

Simultaneously, the saved should verify whether the entry in `wp_cpvm_entities` exists. 

It not, it should create that entity.

However this may cause an issue in a different department.

Currently the plugin runs through this function during a `post_save` hook

```php
<?php
...
function cpvm_table_manager($entity_exists, $description, $entity_title, $thumbnail_url) {
	global $wpdb;
	$prefix = $wpdb->get_blog_prefix(); // returns 'wp_'
	$query = $wpdb->query($entity_exists); // @boolean

	// If false, then create the entity entry.
	if (!$query) {
		$data = array( // Using the save_{taxonomy} hook, I can get
			'title' => $entity_title, // all of this data & put it in
			'description' => $description, // the entities database.
			'thumbnail' => $thumbnail_url // except for thumbnail
		); // I'll need to add thumbnail functionality to taxonomies

		// echo $data;
		$format = array('%s','%s', '%s');
		$wpdb->insert($prefix . 'cpvm_entities', $data, $format);
		$entity_id = $wpdb->insert_id;
		return $entity_id;
	} else { // if entry does exist, return it's id value.
		$results = $wpdb->get_results($entity_exists);
		$entity_id = $results[0]->entity_id;
		return $entity_id;
	}
}
...
?>
```

**This is part of the code that calls the above function.**

```php
<?php
function cpvm_save_video_meta_data($video_id, $video) {
	// Check post type for videos
	if ('videos' == $video->post_type) {
		...
		if ($series_tax) { // shows
			// if shows use taxonomy description
			$terms = get_the_terms($video_id, 'video_series');
			$description = $terms[0]->description;

			// send video_term_name to entity title in entities table with this video_id as video_id
			$entity_id;
			$entity_title = $series_tax[0]->name;

			// Check if an entity_title = $entity_title exists in $prefix . 'cpvm_entities'
			$entity_exists = "SELECT * FROM " . $prefix . "cpvm_entities WHERE title = '$entity_title'";

			// if not then insert into $prefix . 'cpvm_entities'
			cpvm_table_manager($entity_exists, $description, $entity_title, $thumbnail_url);
		} else { // movies
			...
		}
		...
	}
}
...
?>
```

So I guess I need to pay attention more to my code. As you can see under the If statement inside the second block of code, there is a section that looks like this:

```php
<?php
...
if ($series_tax) { // shows
	// if shows use taxonomy description
	$terms = get_the_terms($video_id, 'video_series');
	$description = $terms[0]->description;
...
?>
```

So what's going on here is that on a `post_save` hook, if the video has been attached to a `video_series` taxonomy, it means that it's part of a show.

During the post_save, this snippet of code will attempt to get the description of that taxonomy term using the $video_id variable.

_Here's where the issue rises currently_

If you look at that snippet again, the snippet automatically assumes there is a description. It's looking in the place where it would find it if it was already filled in with data.

There are times in the WordPress dashboard that you may want to on-the-fly create a new series taxonomy and then attach it to the video you are currently working on.

In that case a description for that `video_series` taxonomy term doesn't exist. It has no value. At the moment of writing this, I'm not entirely sure if it'll return `NULL` or if it will return an empty string.

So, at the end of the `post_save` you can have only this info for the entity:  

- id => 1
- title => 'Sherlock Holmes',
- description => '',
- thumbnail => 'https://yourwebsite.com/path/to/image.jpg'

I think this issue can be resolved during a `save_{taxonomy}` hook. 

If that hook was already created inside the video post_type, It will show up under the `Series` tab in the custom plugin's dashboard. 

It is from here that the information can be fill and on that `save_{taxonomy}` hook, it will already have _its own_ id to reference and can call an `UPDATE` query since it already exists.
