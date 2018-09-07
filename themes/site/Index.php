<?php get_header(); ?>
<div class="container-fluid">
    <div class="row">
        <div class="slider">

            <?php
            echo do_shortcode('[smartslider3 slider=2]');
            ?>
        </div>


    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="headerProduct">
                <h2>تخفیف های امروز</h2>
                <span class="home-category-info-norm-header-line"></span></div>
            <div class="posts">
                <div class="row">
                    <?php
                    $_product = wc_get_product($product_id);
                    $args = array(
                        'post_type' => 'product',
                        'posts_per_page' => 12
                    );
                    $loop = new WP_Query($args);
                    if ($loop->have_posts()) {
                        while ($loop->have_posts()) : $loop->the_post();
                            $product->get_image()
//                            wc_get_template_part('content', 'product');
//                            ?>
                            <div class="col-md-4 padding-right0">
                                <div class="bodypost">
                                    <div class="imgSection">
                                        <?php $image = wp_get_attachment_image_src(get_post_thumbnail_id($loop->post->ID), 'single-post-thumbnail'); ?>
                                        <img src="<?php echo $image[0]; ?>" data-id="<?php echo $loop->post->ID; ?>"
                                             class="img-responsive" alt="">
                                        <ul class="overlay">
                                            <li class="shopbag"><i class="fa fa-shopping-bag"></i></li>
                                            <li class="addwish"><i class="fa fa-heart"></i></li>
                                            <li class="percent"><i class="fa fa-percent"><span>45</span></i></li>
                                        </ul>
                                    </div>
                                    <div class="detail">
                                        <div class="NamePos"><span><i class="fa fa-map-marker"></i>زنجان</span>
                                            <h2><?php echo $product->get_title(); ?></h2>
                                        </div>
                                        <div class="footerpost">
                                            <div class="price">
                                                <span><?php echo $product->get_sale_price(); ?> تومان</span>
                                                <strike><?php echo $product->get_regular_price(); ?></strike>
                                            </div>


                                            <button class="btn btn-success pulse">مشاهده و خرید</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php
                        endwhile;
                    } else {
                        echo __('No products found');
                    }
                    wp_reset_postdata();
                    ?>

                </div>
            </div>
        </div>
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
