<?php
function register_my_session()
{
  if( !session_id() )
  {
    session_start();
  }
}
add_action('init', 'register_my_session');
function my_deregister_javascript() {
	wp_deregister_script( 'nu-scripts' );
}

require_once('custom-search-acf-wordpress.php');

function activity_posted_by() {
	printf( __( '<span class="byline">%1$s</span>', 'nuthemes' ),
		sprintf( '<span class="author vcard">%1$s</span>',
			esc_html( get_the_author() )
		)
	);
}

/**
 * Use ACF image field as avatar
 * @author Mike Hemberger
 * @link http://thestizmedia.com/acf-pro-simple-local-avatars/
 * @uses ACF Pro image field (tested return value set as Array )
 */
add_filter('get_avatar', 'tsm_acf_profile_avatar', 10, 5);
function tsm_acf_profile_avatar( $avatar, $id_or_email, $size, $default, $alt ) {

    // Get user by id or email
    if ( is_numeric( $id_or_email ) ) {

        $id   = (int) $id_or_email;
        $user = get_user_by( 'id' , $id );

    } elseif ( is_object( $id_or_email ) ) {

        if ( ! empty( $id_or_email->user_id ) ) {
            $id   = (int) $id_or_email->user_id;
            $user = get_user_by( 'id' , $id );
        }

    } else {
        $user = get_user_by( 'email', $id_or_email );
    }

    if ( ! $user ) {
        return $avatar;
    }

    // Get the user id
    $user_id = $user->ID;

    // Get the file id
    $image_id = get_user_meta($user_id, 'hub_logo', true); // CHANGE TO YOUR FIELD NAME

    // Bail if we don't have a local avatar
    if ( ! $image_id ) {
      $avatar_url = get_stylesheet_directory_uri().'/img/ggc-arrows-96x104.png';
    } else {
      // Get the file size
      $image_url  = wp_get_attachment_image_src( $image_id, 'medium' ); // Set image size by name
      // Get the file url
      $avatar_url = $image_url[0];
    }

    // Get the img markup
    $avatar = '<img alt="' . $alt . '" src="' . $avatar_url . '" class="avatar avatar-' . $size . '" height="' . $size . '" width="' . $size . '"/>';

    // Return our new avatar
    return $avatar;
}

function auth_link_filter( $return, $author, $comment_id ) {
    $comment = get_comment( $comment_id );
    $id_or_email = $comment->user_id;
    // Get user by id or email
    if ( is_numeric( $id_or_email ) ) {

        $id   = (int) $id_or_email;
        $user = get_user_by( 'id' , $id );

    } elseif ( is_object( $id_or_email ) ) {

        if ( ! empty( $id_or_email->user_id ) ) {
            $id   = (int) $id_or_email->user_id;
            $user = get_user_by( 'id' , $id );
        }

    } else {
        $user = get_user_by( 'email', $id_or_email );
    }

    if ( ! $user ) {
        return $id_or_email;
    }

    // Get the user id
    $url = "/author/".$user->user_nicename."/";
		return "<a href='$url' rel='external nofollow' class='url'>$author</a>";
}
add_filter( 'get_comment_author_link', 'auth_link_filter', 10, 3 );

// Adds shortcode for hub email as recipient for hub contact form
function custom_wpcf7_special_mail_tag( $output, $name, $html  ) {

	$name = preg_replace( '/^wpcf7\./', '_', $name ); // for back-compat

	$submission = WPCF7_Submission::get_instance();

	if ( ! $submission ) {
        return $output;
    }

	if ( '_url' == $name ) {
        if ( $url = $submission->get_meta( 'url' ) ) {
            return esc_url( $url );
        } else {
            return '';
        }
    }

    if ( '_hub_email' == $name) {
    	$url = $submission->get_meta( 'url' );
  		$tokens = explode('/', $url);
  		$youser = $tokens[sizeof($tokens)-2];

  		$hub = get_user_by('slug', $youser);

  		if ($hub) {
  			$hubemail = $hub->user_email;
		    return $hubemail;
  		} else {
  			return "no email ".$username;
  		}

    }

}

add_filter( 'wpcf7_special_mail_tags', 'custom_wpcf7_special_mail_tag', 20, 3 );

add_action( 'wp_print_scripts', 'my_deregister_javascript', 100 );
// OPEN GRAPH
function doctype_opengraph($output) {
    return $output . '
    xmlns:og="http://opengraphprotocol.org/schema/"
    xmlns:fb="http://www.facebook.com/2008/fbml"';
}
add_filter('language_attributes', 'doctype_opengraph');
function fb_opengraph() {
    global $post;

    ?>
    <meta property="fb:app_id" content="1699080173711636"/>
    <?php

	if ( is_singular( 'bordr' ) ) {
        if(get_field('brdr_image')) {
			$image = get_field('brdr_image');
			$img_src = $image['sizes'][ 'large' ];
			if ($image['sizes'][ 'large-width' ] < 200 || $image['sizes'][ 'large-height' ] < 200) {
				$img_src = get_stylesheet_directory_uri() . '/img/egc_bg-cremesoda_400x300.jpg';
			}
        } else {
            $img_src = get_stylesheet_directory_uri() . '/img/egc_bg-cremesoda_400x300.jpg';
        }
        if(get_field('brdr_story') != '') {
			$excerpt = get_field('brdr_story');
            $excerpt = strip_tags(str_replace("", "'", $excerpt));
        } else {
            $excerpt = strip_tags(get_bloginfo('description'));
        }
        ?>

  <meta property="og:title" content="<?php echo the_title(); ?>"/>
  <meta property="og:description" content="<?php echo $excerpt; ?>"/>
  <meta property="og:type" content="article"/>
  <meta property="og:url" content="<?php echo the_permalink(); ?>"/>
  <meta property="og:site_name" content="<?php echo get_bloginfo(); ?>"/>
  <meta property="og:image" content="<?php echo $img_src; ?>"/>

	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:site" content="@glograndcentral">
	<meta name="twitter:title" content="<?php echo the_title(); ?>">
	<meta name="twitter:description" content="<?php echo $excerpt; ?>">
	<meta name="twitter:image" content="<?php echo $img_src; ?>">

<?php
    } else if ( is_singular( 'activity' ) ) {
        if(get_field('departure_images')) {
			$image = get_field('departure_images');
			$img_src = $image[0]['sizes']['large'];
			if ($image[0]['sizes'][ 'large-width' ] < 200 || $image[0]['sizes'][ 'large-height' ] < 200) {
				$img_src = get_stylesheet_directory_uri() . '/img/egc_bg-cremesoda_400x300.jpg';
			}
        } else {
            $img_src = get_stylesheet_directory_uri() . '/img/egc_bg-cremesoda_400x300.jpg';
        }
        if(get_field('brief_description') != '') {
			$excerpt = get_field('brief_description');
            $excerpt = strip_tags(str_replace("", "'", $excerpt));
        } else {
            $excerpt = strip_tags(get_bloginfo('description'));
        }
        ?>

  <meta property="og:title" content="<?php echo the_title(); ?>"/>
  <meta property="og:description" content="<?php echo $excerpt; ?>"/>
  <meta property="og:type" content="article"/>
  <meta property="og:url" content="<?php echo the_permalink(); ?>"/>
  <meta property="og:site_name" content="<?php echo get_bloginfo(); ?>"/>
  <meta property="og:image" content="<?php echo $img_src; ?>"/>

 	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:site" content="@glograndcentral">
	<meta name="twitter:title" content="<?php echo the_title(); ?>">
	<meta name="twitter:description" content="<?php echo $excerpt; ?>">
	<meta name="twitter:image" content="<?php echo $img_src; ?>">

<?php
    } else if ( is_page() ) {
        if(has_post_thumbnail() ) {
			$image_src = the_post_thumbnail_url('large');
        } else {
            $img_src = get_stylesheet_directory_uri() . '/img/egc_bg-cremesoda_400x300.jpg';
        }
        if(get_the_excerpt()) {
			$excerpt = get_the_excerpt();
            $excerpt = strip_tags(str_replace("", "'", $excerpt));
        } else {
            $excerpt = strip_tags(get_bloginfo('description'));
        }
        ?>

  <meta property="og:title" content="<?php echo the_title(); ?>"/>
  <meta property="og:description" content="<?php echo $excerpt; ?>"/>
  <meta property="og:type" content="article"/>
  <meta property="og:url" content="<?php echo the_permalink(); ?>"/>
  <meta property="og:site_name" content="<?php echo get_bloginfo(); ?>"/>
  <meta property="og:image" content="<?php echo $img_src; ?>"/>

	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:site" content="@glograndcentral">
	<meta name="twitter:title" content="<?php echo the_title(); ?>">
	<meta name="twitter:description" content="<?php echo $excerpt; ?>">
	<meta name="twitter:image" content="<?php echo $img_src; ?>">

<?php
    } else if ( is_post_type_archive( 'bordr' ) ) {
		$img_src = get_stylesheet_directory_uri() . '/img/egc_logo_600x340.jpg';
?>

  <meta property="og:title" content="Bordr Stories"/>
  <meta property="og:description" content="Bordrs are stories, impressions, experiences of a border."/>
  <meta property="og:type" content="website"/>
  <meta property="og:url" content="<?php echo esc_url( get_post_type_archive_link( 'bordr' ) ); ?>"/>
  <meta property="og:site_name" content="<?php echo get_bloginfo(); ?>"/>
  <meta property="og:image" content="<?php echo $img_src; ?>"/>

	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:site" content="@glograndcentral">
	<meta name="twitter:title" content="Bordr Stories">
	<meta name="twitter:description" content="Bordrs are stories, impressions, experiences of a border.">
	<meta name="twitter:image" content="<?php echo $img_src; ?>">

<?php
    } else if ( is_author() ) {
        if(get_field('organization_logo')) {
			$image = get_field('organization_logo');
			$img_src = $image[0]['sizes']['large'];
			if ($image[0]['sizes'][ 'large-width' ] < 200 || $image[0]['sizes'][ 'large-height' ] < 200) {
				$img_src = get_stylesheet_directory_uri() . '/img/egc_bg-cremesoda_400x300.jpg';
			}
        } else {
            $img_src = get_stylesheet_directory_uri() . '/img/egc_bg-cremesoda_400x300.jpg';
        }
        if(get_field('organization_profile') != '') {
			$excerpt = get_field('organization_profile');
            $excerpt = strip_tags(str_replace("", "'", $excerpt));
        } else {
            $excerpt = strip_tags(get_bloginfo('description'));
        }
		$excerpt = strip_tags(get_bloginfo('description'));
?>

  <meta property="og:title" content="<?php echo get_field('organization_name'); ?>"/>
  <meta property="og:description" content="<?php echo $excerpt; ?>"/>
  <meta property="og:type" content="article"/>
  <meta property="og:url" content="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"/>
  <meta property="og:site_name" content="<?php echo get_bloginfo(); ?>"/>
  <meta property="og:image" content="<?php echo $img_src; ?>"/>

 	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:site" content="@glograndcentral">
	<meta name="twitter:title" content="<?php echo get_field('organization_name'); ?>">
	<meta name="twitter:description" content="<?php echo $excerpt; ?>">
	<meta name="twitter:image" content="<?php echo $img_src; ?>">

<?php
    } else if ( is_home() ) {
		$img_src = get_stylesheet_directory_uri() . '/img/egc_logo_600x340.jpg';
		$excerpt = strip_tags(get_bloginfo('description'));
?>

  <meta property="og:title" content="Global Grand Central"/>
  <meta property="og:description" content="<?php echo $excerpt; ?>"/>
  <meta property="og:type" content="website"/>
  <meta property="og:url" content="<?php echo esc_url( home_url() ); ?>"/>
  <meta property="og:site_name" content="<?php echo get_bloginfo(); ?>"/>
  <meta property="og:image" content="<?php echo $img_src; ?>"/>

	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:site" content="@glograndcentral">
	<meta name="twitter:title" content="Global Grand Central">
	<meta name="twitter:description" content="<?php echo $excerpt; ?>">
	<meta name="twitter:image" content="<?php echo $img_src; ?>">

<?php
    } else {
        return;
    }
}
add_action('wp_head', 'fb_opengraph', 5);
// ADMIN FUNCTIONS
add_filter('manage_bordr_posts_columns', 'bordr_table_head');
function bordr_table_head( $defaults ) {
    $defaults['related_activity'] = 'Related Activity';
    return $defaults;
}
/**
 * Fill custom field value
 */
add_action('manage_bordr_posts_custom_column', 'bordr_table_content', 10, 2);
function bordr_table_content( $column_name, $post_id ) {
  switch ($column_name) {
    case 'title':
      $brdr_from = get_post_meta( $post_id, 'brdr_from', true );
      $brdr_to = get_post_meta( $post_id, 'brdr_to', true );
      echo $first_name . ' > ' . $last_name;
      break;
    case 'related_activity':
      $activity = get_post_meta( $post_id, 'related_activity', true );
	  $activity = get_post($activity);
      echo $activity->post_title;
      break;
  }
}
add_filter('the_title', 'bordr_meta_on_title',10, 2);
function bordr_meta_on_title($title, $id) {
  if('bordr' == get_post_type($id)) {
      return get_post_meta( $id, 'brdr_from', true ).' > '.get_post_meta( $id, 'brdr_to', true );
   }
  else {
      return $title;
  }
}
add_action( 'submitpost_box', 'hidden_type_title' );
function hidden_type_title() {
    global $current_user, $post, $post_type;
    // If the current type supports the title, nothing to done, return
    if( post_type_supports( $post_type, 'title' ) )
        return;
    ?>
    <input type="hidden" name="post_title" value="" id="title" />
    <?php
}
function remove_quick_edit($actions, $post) {
    if(get_post_type() == 'activity' || get_post_type() == 'bordr') {
        unset($actions['inline hide-if-no-js']);
    }
    return $actions;
}
add_filter('post_row_actions','remove_quick_edit', 10, 2);
// END ADMIN FUNCTIONS

function brdr_archive_random( $query ) {

    if( $query->is_main_query() && !is_admin() && (is_post_type_archive( 'bordr' ))) {

	  	$seed = $_SESSION['seed'];
		  if (empty($seed)) {
		   $seed = rand();
		   $_SESSION['seed'] = $seed;
		  }
        $query->set( 'orderby', 'rand('.$seed.')' );
    }

}
add_action( 'pre_get_posts', 'brdr_archive_random' );

function bordr_infinite_scroll_paging( $args ) {
    if ( 'bordr' === $args['post_type'] ) {
        $args['paged']++;
    }
    return $args;
}
add_filter( 'infinite_scroll_query_args', 'bordr_infinite_scroll_paging', 100 );

function my_acf_init() {
	acf_update_setting('google_api_key', 'AIzaSyD46ZIXV0LS1gBcNiXMkV-Td66f0HpgNUY');
}
add_action('acf/init', 'my_acf_init');
function wpsites_home_page_cpt_filter($query) {
  if ( !is_admin() && $query->is_main_query() && is_home() ) {
    $query->set('post_type', array( 'activity', 'bordr' ) );
  }
}
add_action('pre_get_posts','wpsites_home_page_cpt_filter',20);
/**
 * Sort our repeater fields array by date subfield descending
 * @param  mixed $a first
 * @param  mixed $b second
 * @return value
 */
function sort_by_date_descending($a, $b) {
    if (strtotime($a['event_date']) == strtotime($b['event_date'])) {
        return 0;
    }
    return (strtotime($a['event_date']) > strtotime($b['event_date'])) ? -1 : 1;
}
/**
 * Sort our repeater fields array by date subfield ascending
 * @param  mixed $a first
 * @param  mixed $b second
 * @return value
 */
function sort_by_date_ascending($a, $b) {
    if (strtotime($a['event_date']) == strtotime($b['event_date'])) {
        return 0;
    }
    return (strtotime($a['event_date']) < strtotime($b['event_date'])) ? -1 : 1;
}
add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_style('fontawesome', '//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css');
});
function custom_rewrite_tag() {
  add_rewrite_tag('%travdept%', '([^&]+)');
  add_rewrite_tag('%story%', '([^&]+)');
}
add_action('init', 'custom_rewrite_tag', 10, 0);
function custom_rewrite_rule() {
  add_rewrite_rule('^crossing/([^/]*)/?','index.php?page_id=219&travdept=$matches[1]','top');
}
add_action('init', 'custom_rewrite_rule', 10, 0);
add_filter( 'wp_nav_menu_items', 'my_custom_menu_item', 10, 2 );
function my_custom_menu_item( $items, $args ) {
	if ( isset( $args ) && $args->theme_location === 'primary' ) {
        $user=wp_get_current_user();
        $name=$user->display_name; // or user_login , user_firstname, user_lastname
		$loginurl=$user->user_login; // or user_login , user_firstname, user_lastname
        $items .= '<li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="' . home_url() . '/author/' . $loginurl . '/" data-toggle="dropdown" class="dropdown-toggle">Hello, '.$name.' <i class="fa fa-angle-down" aria-hidden="true"></i></a><ul role="menu" class="dropdown-menu"><li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="' . home_url() . '/author/' . $loginurl . '/">View Your Hub Profile</a>';
        $items .= '<li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="/wp-admin/profile.php">Edit Your Hub Profile</a></li><li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="/wp-admin/edit.php?post_type=activity">Edit Activities</a></li><li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="/wp-admin/edit.php?post_type=bordr">Edit Bordr Stories</a></li>';
        $items .= '<li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="/wp-admin/edit-comments.php">Moderate Comments</a></li><li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="'.wp_logout_url( get_permalink() ).'">Logout</a></li></ul></li>';
	}
	return $items;
}
// Remove Bio box
add_action( 'personal_options', array ( 'T5_Hide_Profile_Bio_Box', 'start' ) );
/**
 * Captures the part with the biobox in an output buffer and removes it.
 *
 * @author Thomas Scholz, <info@toscho.de>
 *
 */
class T5_Hide_Profile_Bio_Box
{
    /**
     * Called on 'personal_options'.
     *
     * @return void
     */
    public static function start()
    {
        $action = ( IS_PROFILE_PAGE ? 'show' : 'edit' ) . '_user_profile';
        add_action( $action, array ( __CLASS__, 'stop' ) );
        ob_start();
    }
    /**
     * Strips the bio box from the buffered content.
     *
     * @return void
     */
    public static function stop()
    {
        $html = ob_get_contents();
        ob_end_clean();
        // remove the headline
        $html = str_replace( '<h2>Name</h2>', '<h2>Name of you or your hub</h2>', $html );
        $headline = __( IS_PROFILE_PAGE ? 'About Yourself' : 'About the user' );
        $html = str_replace( '<h2>' . $headline . '</h2>', '', $html );
        $html = str_replace( '<label for="nickname">Nickname', '<label for="nickname">Hub Name', $html );
        $html = str_replace( 'Biographical Info', 'Short overview of who you are (as a Hub) and what you do?', $html );
        $html = str_replace( 'Share a little biographical information to fill out your profile. This may be shown publicly.', '', $html );
        // remove the table row
//         $html = preg_replace( '~<tr class="user-description-wrap">\s*<th><label for="description".*</tr>~imsUu', '', $html );
        print $html;
    }
}
// Image upload function
function sanitize_filename_on_upload($filename) {
	$ext = end(explode('.',$filename));
	// Replace all weird characters
	$sanitized = preg_replace('/[^a-zA-Z0-9-_.]/','', substr($filename, 0, -(strlen($ext)+1)));
	// Replace dots inside filename
	$sanitized = str_replace('.','-', $sanitized);
	return strtolower($sanitized.'.'.$ext);
}
add_filter('sanitize_file_name', 'sanitize_filename_on_upload', 10);
// ONLY SHOW TO HUBS CONTENT THAT IS RELEVANT TO THEM
add_filter( 'ajax_query_attachments_args', 'show_current_user_attachments' );
function show_current_user_attachments( $query ) {
    $user_id = get_current_user_id();
	if( !current_user_can( 'edit_others_posts' ) ) {
        $query['author'] = $user_id;
	}
    return $query;
}
function posts_for_current_author($query) {
	global $pagenow;
	if( 'edit.php' != $pagenow || !$query->is_admin )
	    return $query;
	if( !current_user_can( 'edit_others_posts' ) ) {
		global $user_ID;
		$query->set('author', $user_ID );
	}
	return $query;
}
add_filter('pre_get_posts', 'posts_for_current_author');
function comments_for_current_author($query) {
	global $pagenow;
	if( 'edit-comments.php' != $pagenow )
	    return $query;
	if( !current_user_can( 'edit_others_posts' ) ) {
		global $user_ID, $wpdb;
		$clauses['join'] = ", ".$wpdb->base_prefix."posts";
		$clauses['where'] .= " AND ".$wpdb->base_prefix."posts.post_author = ".$user_ID." AND ".$wpdb->base_prefix."comments.comment_post_ID = ".$wpdb->base_prefix."posts.ID";
        return $clauses;
    	}
	return $query;
}
add_filter('comments_clauses', 'comments_for_current_author');
// END FILTER
// HIDE SOME PROFILE ELEMENTS FOR HUBS
// remove personal options block
if(is_admin()){
  add_action( 'personal_options', 'prefix_hide_personal_options' );
}
function prefix_hide_personal_options() {
?>
<script type="text/javascript">
  jQuery(document).ready(function( $ ){
    $("#your-profile .form-table:first, #your-profile h3:first").remove();
  });
</script>
<?php
}
// ACTIVITY FILTER FUNCTIONS
$GLOBALS['my_query_filters'] = array(
	'author'	=> 'hub',
	'location'	=> 'ctry',
	'relact'	=> 'relact'
);
// array of filters (field key => field name)
$GLOBALS['my_meta_query_filters'] = array(
	'field_1'	=> 'method',
	'field_2'	=> 'char',
	'field_3'	=> 'perception'
);
// action
add_action('pre_get_posts', 'my_pre_get_posts', 20);
function my_pre_get_posts( $query ) {

  if( $query->is_main_query() ){
	// bail early if is in admin
	if( is_admin() ) {

		return;

	}

	// loop over filters
	foreach( $GLOBALS['my_query_filters'] as $key => $name ) {

		// continue if not found in url
		if( empty($_GET[ $name ]) && empty($_SESSION[ $name ])) {

			continue;

		}

		if ($key == "author") {
			if (!empty($_GET[ $name ])) {
				// set session
				$_SESSION[$name] = $_GET[ $name ];
				$value = explode(',', $_GET[ $name ]);
				$addQ = 1;
			} else if (!empty($_SESSION[ $name ]) && ($_GET['infinity'] == 'scrolling')) {
				$value = explode(',', $_SESSION[ $name ]);
				$addQ = 1;
			} else {
				unset($_SESSION[ $name ]);
				$addQ = 0;
			}
			if ($addQ > 0) {
				// append to query
				$query->set( 'author__in' , $value );
			}
		}

		if ($key == "location") {
			if (!empty($_GET[ $name ])) {
				// set session
				$_SESSION[$name] = $_GET[ $name ];
				$value = $_GET[ $name ];
				$addQ = 1;
			} else if (!empty($_SESSION[ $name ]) && ($_GET['infinity'] == 'scrolling')) {
				$value = $_SESSION[ $name ];
				$addQ = 1;
			} else {
				unset($_SESSION[ $name ]);
				$addQ = 0;
			}

			if ($addQ > 0) {
				$arg = array(
						'meta_key'		=> 'organization_location',
						'meta_value'	=> sprintf('%s";', $value),
						'meta_compare'	=>'LIKE',
						'fields'	=> 'ID'
				);

				$ctryusers = get_users($arg);
				$query->set( 'author__in' , $ctryusers );
			}
		}
		if ($key == "relact") {
			if (!empty($_GET[ $name ])) {
				// set session
				$_SESSION[$name] = $_GET[ $name ];
				$value = $_GET[ $name ];
				$addQ = 1;
			} else if (!empty($_SESSION[ $name ]) && ($_GET['infinity'] == 'scrolling')) {
				$value = $_SESSION[ $name ];
				$addQ = 1;
			} else {
				unset($_SESSION[ $name ]);
				$addQ = 0;
			}
			if ($addQ > 0) {
				$arg = array(
						'post_type'         => 'bordr',
						'meta_query'        => array(
							array(
								'key'   => 'related_activity',
								'value' => $value
							)
						)
				);
				$query->set('meta_query', $arg);
			}
		}

	}

	// get meta query
	$meta_query = $query->get('meta_query');

	// loop over filters
	foreach( $GLOBALS['my_meta_query_filters'] as $key => $name ) {

		// continue if not found in url
		if( empty($_GET[ $name ]) && empty($_SESSION[ $name ])) {

			continue;

		}

		if ((isset($_GET[ $name ]) && $name == 'char') || (isset($_SESSION[ $name ]) && $name == 'char')) {
			if (!empty($_GET[ 'char' ])) {
				// set session
				$_SESSION['char'] = $_GET[ 'char' ];
				$_SESSION['charval'] = $_GET[ 'charval' ];
				$ckey = $_GET[ 'char' ];
				$cvalue = $_GET[ 'charval' ];
				$addQ = 1;
				$addFQ = 1;
			} else if (!empty($_SESSION[ 'char' ]) && ($_GET['infinity'] == 'scrolling')) {
				$ckey = $_SESSION[ 'char' ];
				$cvalue = $_SESSION[ 'charval' ];
				$addQ = 1;
				$addFQ = 1;
			} else {
				unset($_SESSION[ 'char' ]);
				unset($_SESSION[ 'charval' ]);
				$addQ = 0;
			}

			if ($addQ > 0) {
				if ($cvalue == 100) { $cvalue = 60; $compare = ">"; }
				else { $cvalue = 40; $compare = "<"; }
				// append meta query
				$meta_query[] = array(
					'key'		=> $ckey,
					'value'		=> $cvalue,
					'compare'	=> $compare,
					'type' => 'numeric'
				);
				$meta_query[] = array(
					'key'		=> $ckey."_rel",
					'value'		=> 1,
					'compare'	=> '='
				);
			}

		} else if ((isset($_GET[ $name ]) && $name == 'perception') || (isset($_SESSION[ $name ]) && $name == 'perception')) {
			if (!empty($_GET[ 'perception' ])) {
				// set session
				$_SESSION['perception'] = $_GET[ 'perception' ];
				$_SESSION['perceptionval'] = $_GET[ 'perceptionval' ];
				$ckey = $_GET[ 'perception' ];
				$cvalue = $_GET[ 'perceptionval' ];
				$addQ = 1;
				$addFQ = 1;
			} else if (!empty($_SESSION[ 'perception' ]) && ($_GET['infinity'] == 'scrolling')) {
				$ckey = $_SESSION[ 'perception' ];
				$cvalue = $_SESSION[ 'perceptionval' ];
				$addQ = 1;
				$addFQ = 1;
			} else {
				unset($_SESSION[ 'perception' ]);
				unset($_SESSION[ 'perceptionval' ]);
				$addQ = 0;
			}

			if ($addQ > 0) {
				if ($cvalue == 100) { $cvalue = 60; $compare = ">"; }
				else { $cvalue = 40; $compare = "<"; }
				// append meta query
				$meta_query[] = array(
					'key'		=> $ckey,
					'value'		=> $cvalue,
					'compare'	=> $compare,
					'type' => 'numeric'
				);
			}

		} else if ((isset($_GET[ $name ]) && $name == 'method') || (isset($_SESSION[ $name ]) && $name == 'method')) {
			if (!empty($_GET[ 'method' ])) {
				// set session
				$_SESSION['method'] = $_GET[ 'method' ];
				$ckey = $_GET[ 'method' ];
				$addQ = 1;
				$addFQ = 1;
			} else if (!empty($_SESSION[ 'method' ]) && ($_GET['infinity'] == 'scrolling')) {
				$ckey = $_SESSION[ 'method' ];
				$addQ = 1;
				$addFQ = 1;
			} else {
				unset($_SESSION[ 'method' ]);
				$addQ = 0;
			}

			if ($addQ > 0) {
        if ($ckey == "bordr") {

          $hub_q = new WP_Query(array(
                    'post_type' => 'bordr',
                    'posts_per_page' => -1
                  ));

  				if ( $hub_q->have_posts() ) :
  					while ( $hub_q->have_posts() ) : $hub_q->the_post();
              $bordr_in = get_field('related_activity');
              if( $bordr_in )
              {
              		$bordr_acts[] = $bordr_in->ID;
              }
            endwhile;
  					wp_reset_query();
  				endif;

          $bordr_acts = array_unique($bordr_acts);

          $query->set('post__in', $bordr_acts);

        } else {
  				// append meta query
  				$meta_query[] = array(
  					'key'		=> 'method_icons',
  					'value'		=> '"'.$ckey.'"',
  					'compare'	=> 'LIKE'
  				);
        }
			}

		} else {
			if (!empty($_GET[ $name ])) {
				// set session
				$_SESSION[$name] = $_GET[ $name ];
				$value = explode(',', $_GET[ $name ]);
				$addQ = 1;
				$addFQ = 1;
			} else if (!empty($_SESSION[ $name ]) && ($_GET['infinity'] == 'scrolling')) {
				$value = explode(',', $_SESSION[ $name ]);
				$addQ = 1;
				$addFQ = 1;
			} else {
				unset($_SESSION[ $name ]);
				$addQ = 0;
			}
			if ($addQ > 0) {
				// append meta query
				$meta_query[] = array(
					'key'		=> $key,
					'value'		=> $value,
					'compare'	=> 'IN'
				);
			}
		}

	}

		if ($addFQ > 0) {
			// update meta query
			$query->set('meta_query', $meta_query);
		}
	}
}
// --- BEGIN CUSTOM POST TYPE FILTERS
add_action('acf/save_post', 'pre_save_activity', 10, 1);
function pre_save_activity($post_id) {
    // Handle custom frontend fields: title and draft
    // Bail-out if we are in admin or we are not creating an activity
    if (is_admin() || get_post_type($post_id) != 'activity') {
      return $post_id;
    }
    if ($_POST['post_type'] == 'draft') {
        $post_status = 'draft';
    } else {
        $post_status = 'publish';
    }
    $args = array(
        'ID' => $post_id,
        'post_status' => $post_status,
        'post_title' => $_POST['acf']['field_588f1624311a8']
    );
    wp_update_post($args);
    return $post_id;
}
// Hide frontend activity title field
add_filter('acf/prepare_field/key=field_588f1624311a8', 'hide_field_in_admin', 10, 2);
function hide_field_in_admin($field) {
    if(is_admin()) {
        return false;
    } else {
        return $field;
    }
}
add_filter('acf/load_value/key=field_588f1624311a8', 'load_activity_title_field_value', 10, 3);
function load_activity_title_field_value($value, $post_id, $field) {
    if($post_id) {
        return get_the_title($post_id);
    }
}
// Display activity image galleries as slideshows
add_shortcode('gallery', 'activity_slideshow_gallery');
function activity_slideshow_gallery($attr) {
    if(is_singular('activity')) {
        $attr['type']= 'slideshow';
        $output = gallery_shortcode($attr);
        return $output;
    }
}
// --- BEGIN CUSTOM POST TYPES
add_action( 'init', 'cptui_register_my_cpts' );
function cptui_register_my_cpts() {
	$labels = array(
		"name" => __( 'Bordrs', 'bordr' ),
		"singular_name" => __( 'Bordr', 'bordr' ),
		"menu_name" => __( 'My Bordrs', 'bordr' ),
		"all_items" => __( 'All Bordrs', 'bordr' ),
		"add_new" => __( 'Add New Bordr', 'bordr' ),
		"add_new_item" => __( 'Add New Bordr', 'bordr' ),
		"edit_item" => __( 'Edit Bordr', 'bordr' ),
		"new_item" => __( 'New Bordr', 'bordr' ),
		"view_item" => __( 'View Bordr', 'bordr' ),
		"search_items" => __( 'Search Bordrs', 'bordr' ),
		"not_found" => __( 'No Bordrs Found', 'bordr' ),
		"not_found_in_trash" => __( 'No Bordrs Found in Trash', 'bordr' ),
		"archives" => __( 'Bordr archives', 'bordr' ),
		"insert_into_item" => __( 'Insert into bordr', 'bordr' ),
		"uploaded_to_this_item" => __( 'Upload to this bordr', 'bordr' ),
		);
	$args = array(
		"label" => __( 'Bordrs', 'bordr' ),
		"labels" => $labels,
		"description" => "These are bordr stories, stories about experiences around and over metaphorical and geographic borders.",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => true,
		"show_in_menu" => true,
				"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "bordr", "with_front" => true ),
		"query_var" => true,
		"menu_icon" => "dashicons-leftright",
		"supports" => false,					);
	register_post_type( "bordr", $args );
	$labels = array(
		"name" => __( 'Activities', 'bordr' ),
		"singular_name" => __( 'Activity', 'bordr' ),
		"menu_name" => __( 'My Activities', 'bordr' ),
		"all_items" => __( 'All Activities', 'bordr' ),
		"add_new" => __( 'Add New Activity', 'bordr' ),
		"add_new_item" => __( 'Add New Activity', 'bordr' ),
		"edit_item" => __( 'Edit Activity', 'bordr' ),
		"new_item" => __( 'New Activity', 'bordr' ),
		"view_item" => __( 'View Activity', 'bordr' ),
		"search_items" => __( 'Search Activity', 'bordr' ),
		"not_found" => __( 'No Activities Found', 'bordr' ),
		"not_found_in_trash" => __( 'No Activities Found in Trash', 'bordr' ),
		"archives" => __( 'Activities Archive', 'bordr' ),
		"insert_into_item" => __( 'Insert into activity', 'bordr' ),
		"uploaded_to_this_item" => __( 'Uploaded to this activity', 'bordr' ),
		"filter_items_list" => __( 'Filter Activity List', 'bordr' ),
		);
	$args = array(
		"label" => __( 'Activities', 'bordr' ),
		"labels" => $labels,
		"description" => "Activities are projects, actions, or interventions that explore borders and enable people to meet others",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => true,
		"show_in_menu" => true,
				"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "activity", "with_front" => true ),
		"query_var" => true,
		"menu_icon" => "dashicons-universal-access",
		"supports" => array( "title", "editor", "thumbnail", "comments", "revisions", "author" ),					);
	register_post_type( "activity", $args );
// End of cptui_register_my_cpts()
}
// --- BEGIN CUSTOM FIELD GROUPS
if( function_exists('acf_add_local_field_group') ):
acf_add_local_field_group(array (
	'key' => 'group_5762ca8985b91',
	'title' => 'About',
	'fields' => array (
		array (
			'key' => 'field_5762cab774340',
			'label' => 'In the Press',
			'name' => 'in_the_press',
			'type' => 'repeater',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'collapsed' => '',
			'min' => '',
			'max' => '',
			'layout' => 'block',
			'button_label' => 'Add Row',
			'sub_fields' => array (
				array (
					'key' => 'field_5762caf174341',
					'label' => 'News Source',
					'name' => 'news_source',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5762cb0b74342',
					'label' => 'Article Title',
					'name' => 'article_title',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5762cb4574344',
					'label' => 'Date Published',
					'name' => 'date_published',
					'type' => 'date_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'display_format' => 'd/m/Y',
					'return_format' => 'd/m/Y',
					'first_day' => 1,
				),
				array (
					'key' => 'field_5762cb2374343',
					'label' => 'Key Quote',
					'name' => 'key_quote',
					'type' => 'textarea',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'maxlength' => '',
					'rows' => '',
					'new_lines' => 'wpautop',
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_577285846ea75',
					'label' => 'Image from Article',
					'name' => 'article_image',
					'type' => 'image',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'return_format' => 'array',
					'preview_size' => 'medium',
					'library' => 'all',
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => '',
					'max_height' => '',
					'max_size' => '',
					'mime_types' => '',
				),
				array (
					'key' => 'field_5762cb6f74345',
					'label' => 'Article Link',
					'name' => 'article_link',
					'type' => 'url',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
				),
			),
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'page',
				'operator' => '==',
				'value' => '18',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
));
acf_add_local_field_group(array (
	'key' => 'group_5703f201f38b0',
	'title' => 'Activity',
	'fields' => array (
		array (
			'key' => 'field_57c088f00aefe',
			'label' => 'What is an activity?',
			'name' => '',
			'type' => 'message',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '<p>This is the place to log your audience inclusive projects, actions, or interventions. An activity may be anything from a big project, to a limited event. The most important is that something has been learned.</p>
<p>Only a few questions are mandatory (marked with <span class="acf-required">*</span>), but the more you answer, the more you and others will learn from your efforts.</p>',
			'new_lines' => 'wpautop',
			'esc_html' => 0,
		),
        array (
            'key' => 'field_588f1624311a8',
			'label' => 'Title',
			'name' => 'title',
			'type' => 'text',
			'default_value' => '',
			'maxlength' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
		),
		array (
			'key' => 'field_570c55618d71a',
			'label' => 'Activity Image Gallery',
			'name' => 'departure_images',
			'type' => 'gallery',
			'instructions' => 'Upload images that represent the activity here. The first image will be used as the featured image for the activity.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'min' => '',
			'max' => '',
			'insert' => 'append',
			'library' => 'uploadedTo',
			'min_width' => 300,
			'min_height' => '',
			'min_size' => '',
			'max_width' => '',
			'max_height' => '',
			'max_size' => 2,
			'mime_types' => '',
		),
        array (
			'key' => 'field_56fb182433a5c',
			'label' => '<h2>Explores the space between</h2>',
			'name' => '',
			'type' => 'message',
			'instructions' => 'What border are you exploring?',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '',
			'new_lines' => 'wpautop',
			'esc_html' => 0,
		),
		array (
			'key' => 'field_56fb17b033a5a',
			'label' => 'From',
			'name' => 'from',
			'type' => 'text',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => 50,
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => 120,
			'readonly' => 0,
			'disabled' => 0,
		),
		array (
			'key' => 'field_56fb17fe33a5b',
			'label' => 'To',
			'name' => 'to',
			'type' => 'text',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => 50,
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
			'readonly' => 0,
			'disabled' => 0,
		),
		array (
			'key' => 'field_5703f367df759',
			'label' => 'Other borders explored in this activity',
			'name' => 'other_borders',
			'type' => 'repeater',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'collapsed' => '',
			'min' => '',
			'max' => '',
			'layout' => 'block',
			'button_label' => 'Add Another Border',
			'sub_fields' => array (
				array (
					'key' => 'field_5703f3acdf75a',
					'label' => 'From',
					'name' => 'ofrom',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => 50,
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => 120,
					'readonly' => 0,
					'disabled' => 0,
				),
				array (
					'key' => 'field_5703f3d0df75b',
					'label' => 'To',
					'name' => 'oto',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => 50,
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
					'readonly' => 0,
					'disabled' => 0,
				),
			),
		),
        array (
			'key' => 'field_573b394e55be5',
			'label' => 'Are you partnering with other hubs?',
			'name' => 'partner',
			'type' => 'user',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'role' => array (
				0 => 'hub',
			),
			'allow_null' => 0,
			'multiple' => 1,
		),
		array (
			'key' => 'field_570ce68c4fe89',
			'label' => 'Brief Description',
			'name' => 'brief_description',
			'type' => 'textarea',
			'instructions' => 'Please describe your project (activity) in a sentence or two. (200 characters max)',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => 'limited',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'maxlength' => 120,
			'rows' => 2,
			'new_lines' => '',
		),
		array (
			'key' => 'field_5702d2689ac47',
			'label' => '<h2>Why</h2>',
			'name' => 'why_description',
			'type' => 'wysiwyg',
			'instructions' => 'Why are you doing this? Explain using images, drawings, photographs, film, sound, or text.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'tabs' => 'all',
			'toolbar' => 'basic',
			'media_upload' => 1,
		),
        		array (
			'key' => 'field_56fb0dcf64d76',
			'label' => '<h2>Location</h2>',
			'name' => 'departure_location',
			'type' => 'google_map',
			'instructions' => 'Where is your activity?',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'center_lat' => '48.3995',
			'center_lng' => '9.9832',
			'zoom' => 3,
			'height' => '',
		),
		array (
			'key' => 'field_56fb18a433a5e',
			'label' => '<h2>Characteristics</h2>',
			'name' => '',
			'type' => 'message',
			'instructions' => 'In what type of area was it held?',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '',
			'new_lines' => 'wpautop',
			'esc_html' => 0,
		),
		array (
			'key' => 'field_570d2462d40fc',
			'label' => '',
			'name' => 'urban_rural_rel',
			'type' => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => 'Is it held in an urban or rural area?',
			'default_value' => 0,
		),
		array (
			'key' => 'field_570d2434d40fb',
			'label' => 'Pull the slider to a value that resembles the characteristic of your activity',
			'name' => 'urban_rural',
			'type' => 'number_slider',
			'instructions' => 'Urban area (0)
Rural area (100)',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_570d2462d40fc',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'slider_units' => '%',
			'default_value' => 0,
			'slider_min_value' => 0,
			'slider_max_value' => 100,
			'increment_value' => 1,
		),
		array (
			'key' => 'field_570d28163c50e',
			'label' => '',
			'name' => 'rich_poor_rel',
			'type' => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => 'It is held in a rich or poor area?',
			'default_value' => 0,
		),
		array (
			'key' => 'field_5702af68cf09b',
			'label' => 'Pull the slider to a value that resembles the characteristic of your activity',
			'name' => 'rich_poor',
			'type' => 'number_slider',
			'instructions' => 'Rich area (0)
Poor area (100)',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_570d28163c50e',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'slider_units' => '%',
			'default_value' => 0,
			'slider_min_value' => 0,
			'slider_max_value' => 100,
			'increment_value' => 1,
		),
		array (
			'key' => 'field_570d2a7d3c510',
			'label' => '',
			'name' => 'homo_plural_rel',
			'type' => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => 'It is held in a homogenous or pluralistic area?',
			'default_value' => 0,
		),
		array (
			'key' => 'field_5702afe7cf09c',
			'label' => 'Pull the slider to a value that resembles the characteristic of your activity',
			'name' => 'homo_plural',
			'type' => 'number_slider',
			'instructions' => 'Homogenous area (0)
Pluralistic area (100)',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_570d2a7d3c510',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'slider_units' => '%',
			'default_value' => 50,
			'slider_min_value' => 0,
			'slider_max_value' => 100,
			'increment_value' => 1,
		),
		array (
			'key' => 'field_57be0ee7a98b5',
			'label' => 'Describe the setting',
			'name' => 'setting_desc',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => 8,
			'new_lines' => 'wpautop',
		),
		array (
			'key' => 'field_570d2b113c512',
			'label' => '',
			'name' => 'one_many_rel',
			'type' => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => 'Does it affect a person or people?',
			'default_value' => 0,
		),
		array (
			'key' => 'field_5702b01fcf09d',
			'label' => 'Pull the slider to a value that resembles the characteristic of your activity',
			'name' => 'one_many',
			'type' => 'number_slider',
			'instructions' => 'One person (0)
Many people (100)',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_570d2b113c512',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'slider_units' => '%',
			'default_value' => 0,
			'slider_min_value' => 0,
			'slider_max_value' => 100,
			'increment_value' => 1,
		),
		array (
			'key' => 'field_570d2be23c514',
			'label' => '',
			'name' => 'young_old_rel',
			'type' => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => 'Does it affect the young or the old?',
			'default_value' => 0,
		),
		array (
			'key' => 'field_5702b044cf09e',
			'label' => 'Pull the slider to a value that resembles the characteristic of your activity',
			'name' => 'young_old',
			'type' => 'number_slider',
			'instructions' => 'Young people (0)
Old people (100)',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_570d2be23c514',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'slider_units' => '%',
			'default_value' => 50,
			'slider_min_value' => 0,
			'slider_max_value' => 100,
			'increment_value' => 1,
		),
		array (
			'key' => 'field_570d2ca33c517',
			'label' => '',
			'name' => 'known_unknown_rel',
			'type' => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => 'Does it affect known or unknown people?',
			'default_value' => 0,
		),
		array (
			'key' => 'field_5702b08bcf09f',
			'label' => 'Pull the slider to a value that resembles the characteristic of your activity',
			'name' => 'known_unknown',
			'type' => 'number_slider',
			'instructions' => 'Known people (0)
Unknown people (100)',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_570d2ca33c517',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'slider_units' => '%',
			'default_value' => 50,
			'slider_min_value' => 0,
			'slider_max_value' => 100,
			'increment_value' => 1,
		),
		array (
			'key' => 'field_57be0ff8a98b9',
			'label' => 'Describe the audience/participants (your target group)',
			'name' => 'audience_desc',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'maxlength' => '',
			'rows' => '',
			'new_lines' => 'wpautop',
		),
		array (
			'key' => 'field_57c08ba1444df',
			'label' => '<h2>How the audience/participants were reached or discovered</h2>',
			'name' => 'audience_discovery',
			'type' => 'wysiwyg',
			'instructions' => 'How do/did you find, select, or reach out to your audience/participants (your target group)? Explain using images, drawings, photographs, film, sound, or text.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'tabs' => 'all',
			'toolbar' => 'full',
			'media_upload' => 1,
		),
		array (
			'key' => 'field_570d4d3b389ea',
			'label' => '<h2>How it was done</h2>',
			'name' => 'method_icons',
			'type' => 'checkbox',
			'instructions' => 'Select the methods you\'re using from the list of icons',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
				'archiving' => '<i class="fa fa-archive" aria-hidden="true"></i> archiving',
				'coding' => '<i class="fa fa-code" aria-hidden="true"></i> coding',
				'drawing' => '<i class="fa fa-pencil" aria-hidden="true"></i> drawing',
				'exhibitions' => '<i class="fa fa-picture-o" aria-hidden="true"></i> exhibitions',
				'film' => '<i class="fa fa-video-camera" aria-hidden="true"></i> film',
				'food' => '<i class="fa fa-cutlery" aria-hidden="true"></i> food',
				'graffiti' => '<i class="fa fa-paint-brush" aria-hidden="true"></i> graffiti',
				'interviews' => '<i class="fa fa-comment" aria-hidden="true"></i> interviews',
				'lectures' => '<i class="fa fa-university" aria-hidden="true"></i> lecture',
				'mapping' => '<i class="fa fa-map" aria-hidden="true"></i> mapping',
				'making' => '<i class="fa fa-cogs" aria-hidden="true"></i> making',
				'music' => '<i class="fa fa-music" aria-hidden="true"></i> music',
				'performance' => '<i class="fa fa-users" aria-hidden="true"></i> performance',
				'photography' => '<i class="fa fa-camera-retro" aria-hidden="true"></i> photography',
				'public art' => '<i class="fa fa-street-view" aria-hidden="true"></i> public art',
				'sound' => '<i class="fa fa-volume-up" aria-hidden="true"></i> sound',
				'textile' => '<i class="fa fa-scissors" aria-hidden="true"></i> textile',
				'theatre' => '<i class="fa fa-users" aria-hidden="true"></i> theatre',
				'travel' => '<i class="fa fa-globe" aria-hidden="true"></i> travel',
				'workshops' => '<i class="fa fa-bolt" aria-hidden="true"></i> workshops',
				'writing' => '<i class="fa fa-book" aria-hidden="true"></i> writing',
				'other' => '<i class="fa fa-ellipsis-h" aria-hidden="true"></i> other',
			),
			'default_value' => array (
			),
			'layout' => 'horizontal',
			'toggle' => 0,
			'return_format' => 'value',
		),
		array (
			'key' => 'field_5702d2889ac48',
			'label' => '<h2>How</h2>',
			'name' => 'how_description',
			'type' => 'wysiwyg',
			'instructions' => 'How are you using the above method(s)? Explain using images, drawings, photographs, film, sound, or text.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'tabs' => 'all',
			'toolbar' => 'basic',
			'media_upload' => 1,
		),
		array (
			'key' => 'field_5702d2d19ac49',
			'label' => '<h2>Results</h2>',
			'name' => 'results_description',
			'type' => 'wysiwyg',
			'instructions' => 'What were the results? Explain using images, drawings, photographs, film, sound, or text.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'tabs' => 'all',
			'toolbar' => 'basic',
			'media_upload' => 1,
		),
		array (
			'key' => 'field_5702d38d9ac4b',
			'label' => '<h2>How it went</h2>',
			'name' => 'success_rating',
			'type' => 'number_slider',
			'instructions' => 'Was it a success or failure? Failure — 0 Success — 100',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'units' => '',
			'min_value' => 0,
			'max_value' => 10,
			'increment_value' => '.1',
			'slider_min_value' => 0,
			'slider_max_value' => 100,
			'slider_units' => '%',
			'default_value' => 0,
		),
        array (
			'key' => 'field_570d2d7a799fg',
			'label' => '<h2>What was negative/difficult?</h2>',
			'name' => 'success_negative_desc',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'rows' => 4,
		),
        array (
			'key' => 'field_570d2d7a799fh',
			'label' => '<h2>What was positive/easy?</h2>',
			'name' => 'success_positive_desc',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'rows' => 4,
		),
		array (
			'key' => 'field_570d2d7a799fe',
			'label' => '<h2>Main lessons</h2>',
			'name' => 'success_desc',
			'type' => 'wysiwyg',
			'instructions' => 'Explain using images, drawings, photographs, film, sound, or text.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'tabs' => 'all',
			'toolbar' => 'basic',
			'media_upload' => 1,
		),
        array (
			'key' => 'field_570d2d7a788fe',
			'label' => '<h2>New Activities</h2>',
			'name' => 'activity_influence',
			'type' => 'wysiwyg',
			'instructions' => 'Will this activity contirbute to other work? Please tell',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'tabs' => 'all',
			'toolbar' => 'basic',
			'media_upload' => 1,
		),
		array (
			'key' => 'field_5702d2f29ac4a',
			'label' => '<h2>Inspiration</h2>',
			'name' => 'inspiration_description',
			'type' => 'wysiwyg',
			'instructions' => 'What inspired you? Explain using images, drawings, photographs, film, sound, or text.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'tabs' => 'all',
			'toolbar' => 'basic',
			'media_upload' => 1,
		),
		array (
			'key' => 'field_575eeb8d38ab6',
			'label' => '<h2>Credits</h2>',
			'name' => 'credits_description',
			'type' => 'wysiwyg',
			'instructions' => 'Who helped realize this activity and deserves mention?',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'tabs' => 'all',
			'toolbar' => 'basic',
			'media_upload' => 0,
		),
		array (
			'key' => 'field_5703fc6d566ed',
			'label' => '<h2>Activity timeline</h2>',
			'name' => 'timeline',
			'type' => 'repeater',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'collapsed' => '',
			'min' => 1,
			'max' => '',
			'layout' => 'row',
			'button_label' => 'Add Entry',
			'sub_fields' => array (
				array (
					'key' => 'field_5703fd5d566ef',
					'label' => 'Name of entry',
					'name' => 'event_title',
					'type' => 'text',
					'instructions' => '',
					'required' => 1,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'maxlength' => '',
				),
				array (
					'key' => 'field_5703fd72566f0',
					'label' => 'Date',
					'name' => 'event_date',
					'type' => 'date_picker',
					'instructions' => '',
					'required' => 1,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'display_format' => 'F j, Y',
					'return_format' => 'm/d/Y',
					'first_day' => 1,
				),
				array (
					'key' => 'field_5703fe24566f2',
					'label' => 'End Date',
					'name' => 'end_date',
					'type' => 'date_picker',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'display_format' => 'F j, Y',
					'return_format' => 'm/d/Y',
					'first_day' => 1,
				),
				array (
					'key' => 'field_5703fdd3566f1',
					'label' => 'Description',
					'name' => 'event_description',
					'type' => 'wysiwyg',
					'instructions' => '',
					'required' => 1,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
				),
			),
		),
        array(
            'key' => 'field_5c8889b6b5d7f',
            'label' => 'Did you receive support from the Fanak Fund for this activity?',
            'name' => 'cimetta_grantee',
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => '',
            'default_value' => 0,
            'ui' => 0,
            'ui_on_text' => '',
            'ui_off_text' => '',
        ),
        // Begin Fanak Section
        array(
            'key' => 'field_5c82195d761f0',
            'label' => 'Reporting for Fanak Fund Grantee.',
            'name' => '',
            'type' => 'message',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array (
                array (
                    array (
                        'field' => 'field_5c8889b6b5d7f',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => 'Reporting is required in line with your Grant agreement.

Your reporting is done in two parts:

<b>Global Grand Central Activity Reporting</b>
Includes questions about your work, how do you do it, and what others can learn from you.

<b>Fanak Fund Questions</b>
Includes questions that the Fanak Fund needs to evaluate this grant.

Mandatory questions are marked with *

You will have access to this form during the whole course of your Fanak funded activity, and to your Global Grand Central Activity Report also after your grant is approved.

You are encouraged to read through the whole form, before your Activity begins, and keep filling it out as part of your work.',
            'new_lines' => 'wpautop',
            'esc_html' => 0,
        ),
        array(
            'key' => 'field_5c821c59761f2',
            'label' => 'Fanak Fund Questions',
            'name' => '',
            'type' => 'message',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array (
                array (
                    array (
                        'field' => 'field_5c8889b6b5d7f',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => '- These answers are mandatory
- They will not be made public',
            'new_lines' => 'wpautop',
            'esc_html' => 0,
        ),
        array(
            'key' => 'field_5c821c88761f3',
            'label' => '',
            'name' => 'cimetta_grant_impact',
            'type' => 'textarea',
            'instructions' => '1. How did this grant contribute to the realization of your project, and particularly to artistic exchange, local cultural development and/or the promotion of cultural diversity?',
            'required' => 1,
            'conditional_logic' => array (
                array (
                    array (
                        'field' => 'field_5c8889b6b5d7f',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'maxlength' => '',
            'rows' => '',
            'new_lines' => '',
        ),
        array(
            'key' => 'field_5c821c88761f4',
            'label' => '',
            'name' => 'cimetta_communication',
            'type' => 'textarea',
            'instructions' => '2. Please indicate how you communicated the support received from Fanak and provide any links to communication material.',
            'required' => 1,
            'conditional_logic' => array (
                array (
                    array (
                        'field' => 'field_5c8889b6b5d7f',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'maxlength' => '',
            'rows' => '',
            'new_lines' => '',
        ),
        array(
            'key' => 'field_5c821c88761f5',
            'label' => '',
            'name' => 'cimetta_exchange',
            'type' => 'textarea',
            'instructions' => '3. How does exchange, networking and international contacts contribute to the development of your artistic and cultural project?',
            'required' => 1,
            'conditional_logic' => array (
                array (
                    array (
                        'field' => 'field_5c8889b6b5d7f',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'maxlength' => '',
            'rows' => '',
            'new_lines' => '',
        ),
        array(
            'key' => 'field_5c821c88761f6',
            'label' => '',
            'name' => 'cimetta_needs',
            'type' => 'textarea',
            'instructions' => '4. In your opinion, what are the specific needs of your artistic field in your country and how should they be taken into account?',
            'required' => 1,
            'conditional_logic' => array (
                array (
                    array (
                        'field' => 'field_5c8889b6b5d7f',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'maxlength' => '',
            'rows' => '',
            'new_lines' => '',
        ),
        array(
            'key' => 'field_5c821c88761f7',
            'label' => '',
            'name' => 'cimetta_financial_support',
            'type' => 'textarea',
            'instructions' => '5. Have you received other financial supports for your project apart from the Fanak Fund? If yes, please indicate.',
            'required' => 1,
            'conditional_logic' => array (
                array (
                    array (
                        'field' => 'field_5c8889b6b5d7f',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'maxlength' => '',
            'rows' => '',
            'new_lines' => '',
        ),
        array(
            'key' => 'field_5c821c88761f8',
            'label' => '',
            'name' => 'cimetta_comments',
            'type' => 'textarea',
            'instructions' => '6. Do you have other comments on the grant or its execution that you wish to extend to the Fanak Fund? If so, please elaborate.',
            'required' => 1,
            'conditional_logic' => array (
                array (
                    array (
                        'field' => 'field_5c8889b6b5d7f',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'maxlength' => '',
            'rows' => '',
            'new_lines' => '',
        ),
        array(
            'key' => 'field_5c821c59761g2',
            'label' => 'Global Grand Central Activity Reporting',
            'name' => '',
            'type' => 'message',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => array (
                array (
                    array (
                        'field' => 'field_5c8889b6b5d7f',
                        'operator' => '==',
                        'value' => '1',
                    ),
                ),
            ),
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => '
            Global Grand Central has been co-developed by the Fanak fund and it is grantees to enable sharing of relevant knowledge and experience. Read more on www.globalgrandcentral.net/about/

            - Most answers are voluntary, mandatory questions are marked. *
            - If you accept the Creative Commons License (at the bottom of the form) your answers will be made public - to share with colleagues and epers, and to inspire and get inspired by others.
            - You are free to keep using the service also for other projects, afer the period of the grant.

            Questions refer to your Fanak funded activity.',
            'new_lines' => 'wpautop',
            'esc_html' => 0
        ),
        // End Fanak Section
		array (
			'key' => 'field_573a10c1e94bd',
			'label' => 'Creative Commons License',
			'name' => 'cc_license',
			'type' => 'true_false',
			'instructions' => 'By checking the box below, I accept that my story (including text, photo, drawing, location, and experience evaluations) will now become part of the public domain with rights and obligations for Global Grand Central under a Creative Commons BY 4.0 license, read more <a target="_blank" href="https://creativecommons.org/licenses/by/4.0/">here</a>.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '',
			'default_value' => 0,
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'activity',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'acf_after_title',
	'style' => 'seamless',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => array (
		0 => 'permalink',
		1 => 'the_content',
		2 => 'excerpt',
		3 => 'discussion',
		4 => 'comments',
		5 => 'author',
		6 => 'format',
		7 => 'featured_image',
		8 => 'categories',
		9 => 'tags',
		10 => 'send-trackbacks',
	),
	'active' => 1,
	'description' => '',
));
acf_add_local_field_group(array (
	'key' => 'group_57d8203caf147',
	'title' => 'Bordr',
	'fields' => array (
		array (
			'key' => 'field_57d820ae30eea',
			'label' => 'From',
			'name' => 'brdr_from',
			'type' => 'text',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => 'where/what',
			'prepend' => '',
			'append' => '',
			'maxlength' => 60,
		),
		array (
			'key' => 'field_57d820c230eeb',
			'label' => 'To',
			'name' => 'brdr_to',
			'type' => 'text',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => 'where/what',
			'prepend' => '',
			'append' => '',
			'maxlength' => 60,
		),
		array (
			'key' => 'field_57d82e1934b8f',
			'label' => 'This story relates to',
			'name' => 'related_activity',
			'type' => 'post_object',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'post_type' => array (
				0 => 'activity',
			),
			'taxonomy' => array (
			),
			'allow_null' => 0,
			'multiple' => 0,
			'return_format' => 'object',
			'ui' => 1,
		),
		array (
			'key' => 'field_57d82e9f34b90',
			'label' => 'Tell the story!',
			'name' => 'brdr_story',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => 'Who? Why? How? When?',
			'maxlength' => '',
			'rows' => '',
			'new_lines' => 'wpautop',
		),
		array (
			'key' => 'field_57d82ee034b91',
			'label' => 'What does this border look like?',
			'name' => 'brdr_image',
			'type' => 'image',
			'instructions' => 'Share a photo. Maximum file size is 6MB and a maximum width or height of 2,500px.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'return_format' => 'array',
			'preview_size' => 'medium',
			'library' => 'uploadedTo',
			'min_width' => '400',
			'min_height' => '400',
			'min_size' => '',
			'max_width' => '2500',
			'max_height' => '2500',
			'max_size' => '6',
			'mime_types' => 'jpg, png, jpeg',
		),
		array (
			'key' => 'field_57d82f7834b92',
			'label' => 'How do you experience the border? Tap along the lines to select a value.',
			'name' => '',
			'type' => 'message',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '',
			'new_lines' => 'wpautop',
			'esc_html' => 0,
		),
		array (
			'key' => 'field_57d82ff134b93',
			'label' => 'invisible — visible',
			'name' => 'brdr_invisible_visible',
			'type' => 'number_slider',
			'instructions' => '1 is invisible, 100 is visible',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'slider_units' => '',
			'default_value' => 50,
			'slider_min_value' => 1,
			'slider_max_value' => 100,
			'increment_value' => 1,
		),
		array (
			'key' => 'field_57d8304334b94',
			'label' => 'unimportant — important',
			'name' => 'brdr_unimportant_important',
			'type' => 'number_slider',
			'instructions' => '1 is unimportant, 100 is important',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'slider_units' => '',
			'default_value' => 50,
			'slider_min_value' => 1,
			'slider_max_value' => 100,
			'increment_value' => 1,
		),
		array (
			'key' => 'field_57d8306d34b95',
			'label' => 'negative — positive',
			'name' => 'brdr_negative_positive',
			'type' => 'number_slider',
			'instructions' => '1 is negative, 100 is positive',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'slider_units' => '',
			'default_value' => 50,
			'slider_min_value' => 1,
			'slider_max_value' => 100,
			'increment_value' => 1,
		),
		array (
			'key' => 'field_57d8310a34b97',
			'label' => 'Where is this border?',
			'name' => 'brdr_location',
			'type' => 'google_map',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '100',
				'class' => '',
				'id' => '',
			),
			'center_lat' => '48.4',
			'center_lng' => '9.983333',
			'zoom' => 4,
			'height' => '',
		),
		array (
			'key' => 'field_57d8319e34b98',
			'label' => 'Permission to share?',
			'name' => 'brdr_cc',
			'type' => 'checkbox',
			'instructions' => 'I accept that my story (including text, photo, drawing, location, and experience evaluations) will now become part of the public domain with rights and obligations for Global Grand Central under a Creative Commons BY 4.0 license. <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank">Read more here</a>.',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
				'yes' => 'Yes',
			),
			'default_value' => array (
			),
			'layout' => 'vertical',
			'toggle' => 0,
			'return_format' => 'value',
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'bordr',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'acf_after_title',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => array (
		0 => 'permalink',
		1 => 'the_content',
		2 => 'excerpt',
		3 => 'custom_fields',
		4 => 'discussion',
		5 => 'comments',
		6 => 'revisions',
		7 => 'slug',
		8 => 'author',
		9 => 'format',
		10 => 'page_attributes',
		11 => 'featured_image',
		12 => 'categories',
		13 => 'tags',
		14 => 'send-trackbacks',
	),
	'active' => 1,
	'description' => '',
));
acf_add_local_field_group(array (
	'key' => 'group_5783ddc72821a',
	'title' => 'Home Page',
	'fields' => array (
		array (
			'key' => 'field_5783ddd0f9a7e',
			'label' => 'Headline text',
			'name' => 'headline',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
			'readonly' => 0,
			'disabled' => 0,
		),
		array (
			'key' => 'field_5783de09f9a7f',
			'label' => 'Call to action',
			'name' => 'call_to_action',
			'type' => 'wysiwyg',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'tabs' => 'all',
			'toolbar' => 'full',
			'media_upload' => 1,
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'page_template',
				'operator' => '==',
				'value' => 'welcome.php',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
));
acf_add_local_field_group(array (
	'key' => 'group_57042512f1df9',
	'title' => 'Hub Profile',
	'fields' => array (
		array (
			'key' => 'field_57043c98660f9',
			'label' => 'You are a Hub',
			'name' => '',
			'type' => 'message',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => 'On Global Grand Central, you are registered as a Hub, and your projects, actions, and interventions are called Activities.
Please take a moment and describe your hub.',
			'new_lines' => 'wpautop',
			'esc_html' => 0,
		),
		array (
			'key' => 'field_57cf284c77dce',
			'label' => 'Hub Type',
			'name' => 'hub_type',
			'type' => 'checkbox',
			'instructions' => 'I represent a',
			'required' => 1,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
				'small' => 'mobile individual or organization',
				'medium' => 'location-based organization or individual',
				'large' => 'multiple-state organization, network, or large project',
			),
			'default_value' => array (
			),
			'layout' => 'horizontal',
			'toggle' => 0,
			'return_format' => 'value',
		),
		array (
			'key' => 'field_57042530881b3',
			'label' => 'Hub Name',
			'name' => 'organization_name',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array (
			'key' => 'field_57042a48811f3',
			'label' => 'Hub Logo',
			'name' => 'hub_logo',
			'type' => 'image',
			'instructions' => 'Upload your hub\'s (organization) logo.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'return_format' => 'id',
			'preview_size' => 'thumbnail',
			'library' => 'all',
			'min_width' => 200,
			'min_height' => 200,
			'min_size' => '',
			'max_width' => '',
			'max_height' => '',
			'max_size' => 2,
			'mime_types' => 'jpg,png,jpeg',
		),
		array (
			'key' => 'field_570427ce4f90e',
			'label' => 'Hub Location',
			'name' => 'organization_location',
			'type' => 'google_map',
			'instructions' => 'Where is your hub (organization) based?',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'center_lat' => '48.3995',
			'center_lng' => '9.9832',
			'zoom' => 4,
			'height' => '',
		),
		array (
			'key' => 'field_57043db874399',
			'label' => 'Language',
			'name' => 'organization_language',
			'type' => 'text',
			'instructions' => 'In which languages do you conduct your work? (seperate multiple answers by commas)',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
			'readonly' => 0,
			'disabled' => 0,
		),
		array (
			'key' => 'field_570426ef4f90d',
			'label' => 'Hub Description',
			'name' => 'organization_profile',
			'type' => 'wysiwyg',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'tabs' => 'all',
			'toolbar' => 'full',
			'media_upload' => 1,
		),
		array (
			'key' => 'field_573a144e5437f',
			'label' => 'Photo Gallery',
			'name' => 'hub_images',
			'type' => 'gallery',
			'instructions' => 'Share images of your hub',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'min' => '',
			'max' => '',
			'insert' => 'append',
			'library' => 'all',
			'min_width' => '',
			'min_height' => '',
			'min_size' => '',
			'max_width' => '',
			'max_height' => '',
			'max_size' => '',
			'mime_types' => '',
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'user_role',
				'operator' => '==',
				'value' => 'hub',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'acf_after_title',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
));
endif;

if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array (
    'key' => 'group_58b925f2137e1',
    'title' => 'Featured',
    'fields' => array (
        array (
            'multiple' => 0,
            'allow_null' => 0,
            'choices' => array (
                'no' => 'No',
                'yes' => 'Yes',
            ),
            'default_value' => array (
                0 => 'no',
            ),
            'ui' => 0,
            'ajax' => 0,
            'placeholder' => '',
            'return_format' => 'value',
            'key' => 'field_58b925f973f77',
            'label' => 'Featured',
            'name' => 'featured',
            'type' => 'select',
            'instructions' => 'Is this a featured post?',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array (
                'width' => '',
                'class' => '',
                'id' => '',
            ),
        ),
    ),
    'location' => array (
        array (
            array (
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'bordr',
            ),
            array (
                'param' => 'current_user_role',
                'operator' => '==',
                'value' => 'administrator',
            ),
        ),
        array (
            array (
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'activity',
            ),
            array (
                'param' => 'current_user_role',
                'operator' => '==',
                'value' => 'administrator',
            ),
        ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => 1,
    'description' => '',
));

endif;
// !--- END CUSTOM FIELD GROUPS
add_filter( 'jetpack_enable_opengraph', '__return_false', 99 );
function infinite_scroll_init() {
	add_theme_support( 'infinite-scroll', array(
		'type' => 'scroll',
		'container' => 'masonry',
		'wrapper' => false,
		'footer' => false,
		'render' => 'renderMasonry',
	) );
}
add_action( 'after_setup_theme', 'infinite_scroll_init' );
// add_filter( 'infinite_scroll_query_args', 'my_auto_args' );
function renderMasonry() {
	while ( have_posts() ) : the_post();
		if (get_post_type( get_the_ID() ) == 'activity') {
		 get_template_part( 'activityloop', get_post_format() );
		} else if (get_post_type( get_the_ID() ) == 'bordr') {
		 get_template_part( 'bordrloop', get_post_format() );
		}
	endwhile;
}
function my_auto_args($args) {

	// loop over filters
	foreach( $GLOBALS['my_query_filters'] as $key => $name ) {

		// continue if not found in url
		if( empty($_SESSION[ $name ]) ) {

			continue;

		}

		if ($key == "author") {
			// get the value for this filter
			$value = explode(',', $_SESSION[ $name ]);

			// append to query
			$args['author__in'] = $value;
		}

		if ($key == "location") {
			$value = $_SESSION[ $name ];

			$arg = array(
					'meta_key'		=> 'organization_location',
					'meta_value'	=> sprintf('%s";', $value),
					'meta_compare'	=>'LIKE',
					'fields'	=> 'ID'
			);

			$ctryusers = get_users($arg);
			$args['author__in'] = $ctryusers;
		}
		if ($key == "relact") {
			$value = $_SESSION[ $name ];

			$arg = array(
					'post_type'         => 'bordr',
					'meta_query'        => array(
						array(
							'key'   => 'related_activity',
							'value' => $value
						)
					)
			);
			$args['meta_query'] = $arg;
		}

	}

	return $args;
}
?>
