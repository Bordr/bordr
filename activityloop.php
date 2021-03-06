<?php
/**
 * @package Nu Themes
 */

$location = get_field('departure_location');
$gallery = get_field('departure_images');
$methods = get_field('method_icons');

$location_ctry = trim(end(explode(",", $location['address'])));

$has_bordr_posts = (!empty(get_posts(array(
  'post_type'		=> 'bordr',
  'meta_query'		=> array(
  	array(
  	  'key' => 'related_activity',
  	  'value' =>  get_the_ID(),
  	  'compare' => '='
  	)
  )
))));

$method_options = array('photography'=>'<i class="fa fa-camera-retro" aria-hidden="true"></i> photos',
 						'music' => '<i class="fa fa-music" aria-hidden="true"></i> music',
 						'food' => '<i class="fa fa-cutlery" aria-hidden="true"></i> food',
						'writing' => '<i class="fa fa-book" aria-hidden="true"></i> writing',
						'film' => '<i class="fa fa-video-camera" aria-hidden="true"></i> film',
						'lectures' => '<i class="fa fa-university" aria-hidden="true"></i> lectures',
						'theatre' => '<i class="fa fa-users" aria-hidden="true"></i> theatre',
						'coding' => '<i class="fa fa-code" aria-hidden="true"></i> coding',
						'public art' => '<i class="fa fa-street-view" aria-hidden="true"></i> public art',
						'travel' => '<i class="fa fa-globe" aria-hidden="true"></i> travel',
						'workshops' => '<i class="fa fa-bolt" aria-hidden="true"></i> workshops',
						'archiving' => '<i class="fa fa-archive" aria-hidden="true"></i> archiving',
						'drawing' => '<i class="fa fa-pencil" aria-hidden="true"></i> drawing',
						'graffiti' => '<i class="fa fa-brush" aria-hidden="true"></i> graffiti',
						'interviews' => '<i class="fa fa-comment" aria-hidden="true"></i> interviews',
						'mapping' => '<i class="fa fa-map" aria-hidden="true"></i> mapping',
						'performance' => '<i class="fa fa-users" aria-hidden="true"></i> performance',
						'sound' => '<i class="fa fa-volume-up" aria-hidden="true"></i> sound',
						'exhibitions' => '<i class="fa fa-picture-o" aria-hidden="true"></i> exhibitions',
						'textile' => '<i class="fa fa-scissors" aria-hidden="true"></i> textile',
						'other' => '<i class="fa fa-ellipsis-h" aria-hidden="true"></i> other',
						'making' => '<i class="fa fa-cogs" aria-hidden="true"></i> making');

?>
<div class="col-xs-12 col-sm-6 col-lg-4 masonry-item">
<article id="post-<?php the_ID(); ?>" <?php post_class( 'box' ); ?>>
	<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark" class="maximized"></a>
	<div class="row">
			<?php
			  if($gallery) {
					?><img src="<?php echo $gallery[0]['sizes']['large']; ?>" alt="<?php echo $gallery[0]['alt']; ?>" class="img-responsive"/><?php
			  } else {
				 ?><img src="/wp-content/uploads/2016/12/egc_bg-cremesoda_400x300.png" class="img-responsive"><?php
			  }
			?>
	</div>

	<header class="entry-header">
		<h2 class="entry-title"><?php echo wp_trim_words( get_the_title(), 8 ); ?></h2>

		<?php if ( 'activity' == get_post_type() ) : ?>
		<div class="entry-meta">
			by<br> <?php activity_posted_by(); ?> in <?php echo $location_ctry; ?>
		<!-- .entry-meta --></div>
		<?php endif; ?>
	<!-- .entry-header --></header>

	<div class="clearfix entry-summary">
		<div class="row">
			<div class="col-sm-12">
 			<p><?php the_field('brief_description'); ?></p>
			</div>
			<div class="col-sm-12">
				<div class="row">
				<?php
				if( $methods ): ?>
					<?php foreach( $methods as $method ): ?>
                      <?php /* Skip bordr method, this is handled separately */
                        if($method == 'bordr'): continue; endif; ?>
						<div class="col-xs-6">
							<?php echo $method_options[$method]; ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
                <?php
                if( $has_bordr_posts ): ?>
                	<div class="col-xs-6">
					  <img src="/wp-content/themes/bordr/img/ggc-arrows-96x104.png" width="18"> Bordr stories
					</div>
				<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="chart_success_<?php the_ID(); ?> barchart"></div>
		<script>
		$(function() {
			var data = {
			  'totalMin': 0,
			  'totalMax': 100,
			  'totalAverage': 50,
			  'postTotal': <?php the_field('success_rating'); ?>,
			  'leftField': 'Failure',
			  'rightField': 'Success'
			}
            var chart = ".chart_success_<?php the_ID(); ?>";
			drawdotchart(data, chart);
		});
		</script>

	<!-- .entry-summary --></div>

<!-- #post-<?php the_ID(); ?> --></article>
</div>
