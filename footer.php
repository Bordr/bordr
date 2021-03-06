<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 * site-credit
 * @package Nu Themes
 */
?>
			</div>
		<!-- #main --></div>

		<footer id="footer-static" class="site-footer" role="contentinfo">
			<div class="container">
				<div class="row">
					<div class="col-sm-4 col-md-4 site-info">
						<a href="http://ec.europa.eu/programmes/creative-europe/projects/ce-project-details-page/?nodeRef=workspace://SpacesStore/92784398-e6d7-4950-b1e4-3bcde44e03fe" target="_blank"><img src="/wp-content/uploads/2015/12/eu_flag_creative_europe_co_funded_pos_rgb_right.jpg" style="width:200px;"></a>
					<!-- .site-info --></div>
					<div class="col-sm-2 col-md-3 site-info">
						<i class="fa fa-paragraph" aria-hidden="true"></i> <a href="https://github.com/Bordr/bordr/wiki" target="_blank">Help build our forms</a><br/>
						<i class="fa fa-github" aria-hidden="true"></i> <a href="https://github.com/Bordr/bordr" target="_blank">Help code this site</a>
					<!-- .site-credit --></div>
					<div class="col-sm-2 col-md-2 site-info">
						<a href="/terms-of-service/" target="_blank">Terms of Service</a><br/>
						<a href="/privacy-policy/" target="_blank">Privacy Policy</a><br/>
					<!-- .site-info --></div>
					<div class="col-sm-4 col-md-3">
						Global Grand Central, 2017<br/>
						<i class="fa fa-creative-commons" aria-hidden="true"></i> <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank">Attribution-ShareAlike License</a><br/>
					<!-- .site-info --></div>
				</div>
			</div>
		<!-- #footer --></footer>

		<script src="<?php echo get_stylesheet_directory_uri(); ?>/js/bordr.js"></script>
		<script src="<?php echo get_stylesheet_directory_uri(); ?>/js/jquery.flexslider.js"></script>

		<?php wp_footer(); ?>
	
		<?php
		acf_enqueue_uploader();
		?>

	<?php
		if(file_exists(stream_resolve_include_path('analytics.php'))) {
    		include 'analytics.php';
    	}
	?>

	</body>
</html>