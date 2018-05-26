<?php

define('N2WORDPRESS', 1);
define('N2JOOMLA', 0);
define('N2MAGENTO', 0);
define('N2NATIVE', 0);

class N2Wordpress {

    private static $nextend_js = '';
	private static $nextend_css = '';

    public static function init() {

	    if (is_admin()) {
		    add_action( 'admin_init', 'N2Wordpress::outputStart', 3000 );
		    add_action('admin_footer', 'N2Wordpress::finalizeCssJs');
	    }

	    add_action('template_redirect', 'N2Wordpress::outputStart', 10000);


	    add_action('shutdown', 'N2Wordpress::closeOutputBuffers', -10000);
	    add_action('pp_end_html', 'N2Wordpress::closeOutputBuffers', -10000); // ProPhoto 6 theme
	    add_action('headway_html_close', 'N2Wordpress::closeOutputBuffers', -10000);


	    add_action('headway_html_close', 'N2Wordpress::finalizeCssJs', -10000);
	    add_action('wp_footer', 'N2Wordpress::finalizeCssJs', 11);
    }

    public static function closeOutputBuffers(){
	    $handlers = ob_list_handlers();
	    if(in_array('N2Wordpress::platformRenderEnd', $handlers)){
		    for($i = count($handlers)-1; $i >= 0; $i--){
			    if($handlers[$i] === 'N2Wordpress::platformRenderEnd'){
			    	self::finalizeCssJs();
			    }
			    ob_end_flush();
			    if($handlers[$i] === 'N2Wordpress::platformRenderEnd'){
				    break;
			    }
		    }
	    }
    }

    public static function outputStart() {
	    static $started = false;
	    if(!$started) {
		    $started = true;

		    ob_start( "N2Wordpress::platformRenderEnd" );
	    }
    }

    public static function finalizeCssJs() {
        static $finalized = false;
        if(!$finalized) {
	        $finalized = true;

	        if ( defined( 'N2LIBRARY' ) ) {
		        ob_start();
		        do_action( 'nextend_css' );
		        if ( class_exists( 'N2AssetsManager' ) ) {
			        echo N2AssetsManager::getCSS();
		        }
		        self::$nextend_css = ob_get_clean();

		        ob_start();
		        do_action( 'nextend_js' );
		        if ( class_exists( 'N2AssetsManager' ) ) {
			        echo N2AssetsManager::getJs();
		        }
		        self::$nextend_js = ob_get_clean();

	        }
        }

        return true;
    }

    public static function platformRenderEnd($buffer) {
        if (self::$nextend_css != '' && strpos($buffer, '<!--n2css-->') !== false) {
            $buffer            = str_replace('<!--n2css-->', self::$nextend_css, $buffer);
            self::$nextend_css = '';
        }

        if (self::$nextend_css != '' || self::$nextend_js != '') {
            $parts = preg_split('/<\/head[\s]*>/', $buffer, 2);

            return implode(self::$nextend_css . self::$nextend_js . '</head>', $parts);
        }

        return $buffer;
    }
}

function nextend_comment_for_css() {
    static $once;
    if (!$once) {
        echo "<!--n2css-->";
        $once = true;
    }
}

add_action('wp_print_scripts', 'nextend_comment_for_css');