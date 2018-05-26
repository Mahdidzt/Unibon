
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php bloginfo("name"); wp_title(); ?></title>


    <link href="<?php echo get_template_directory_uri(); ?>/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo get_stylesheet_uri(); ?>" rel="stylesheet" type="text/css">
    <link href="<?php echo get_template_directory_uri(); ?>/css/animate.css" rel="stylesheet" type="text/css">
    <link href="<?php echo get_template_directory_uri(); ?>/css/font-awesome.min.css" rel="stylesheet" type="text/css" >

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <?php wp_head(); ?>
</head>
<body>

    <div class="container-fluid">
        <header>
            <div class="row">
                <div class="col-md-6 padding-right0 padding-left0">
                    <div class="social"> </div>
                </div>
                <div class="col-md-6 padding-right0 padding-left0">
                    <div class="Login-bskt">
                        <div class="sign-login"> <a href="">عضویت</a>
                            <div class="separator"></div>
                            <a href="">ورود</a> </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="header">
                    <div class="col-md-2">
                        <a href="<?php bloginfo("url"); ?>" title="Unibon" class="Brand">
                            <div class="UnibonLogo">
                                <div class="wow animated fadeInLeft animate2">Un</div>
                                <div class="pencilBoncAnim animated bounceInDown"><div class="pencil"></div></div>
                                <div class="wow animated fadeInRight animate2">bon</div>
                            </div>
                        </a>

                    </div>
                    <div class="col-md-8">
                        <div id="imaginary_container">
                            <div class="input-group stylish-input-group">
                                <span class="input-group-addon">
                                    <button class="transparent" type="submit">
                                        <span class="glyphicon glyphicon-search"></span>
                                    </button>
                                 </span>
                                <input type="text" class="form-control"  placeholder="جستجو" >
                                <div class="dropdown">
                                    <button class="dropdown-toggle" type="button" data-toggle="dropdown">انتخاب شهر <span class="caret"></span></button>
                                    <ul class="dropdown-menu">
                                        <li><a href="#">زنجان</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="cart-box">
                            <button class="btn btn-success">سبد خرید <i class="fa fa-shopping-cart"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    </div>