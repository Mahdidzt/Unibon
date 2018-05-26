<?php get_header(); ?>
<div class="container-fluid">
    <div class="row">
        <div class="slider">

            <?php

                echo do_shortcode('[smartslider3 slider=3]');

            ?>
        </div>
		
		
		
		<?php
        echo do_shortcode('[products]');
        ?>
    </div>
</div>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="<?php echo get_template_directory_uri(); ?>/js/jquery-1.11.3.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="<?php echo get_template_directory_uri(); ?>/js/bootstrap.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/wow.min.js" type="text/javascript"></script>
<script>
    new WOW().init();
</script>
<?php wp_footer(); ?>
</body>
</html>
