<?php
/**
   Plugin Name: RefGenerator
   Plugin URI: http://www.nyamsprod.com/blog/refgenerator/
   Description: RefGenerator is a free Wordpress plugin that automatically list and add all your external references included in your post at the end of its content. This plugin is very simple to <a href="options-general.php?page=refgenerator/options.php">configure.</a>. Happy blogging!!
   Author: Nyamagana Butera Ignace Philippe
   Version: 2.3
   Author URI: http://www.nyamsprod.com/
 */
if (function_exists('load_plugin_textdomain')) {
	load_plugin_textdomain('refgen', 'wp-content/plugins/' .  dirname(plugin_basename(__FILE__)) . '/langs');
}
//1 - ACTIVATION FUNCTIONS 

//! add default options when the plugin is activated */
register_activation_hook( __FILE__ , 'refgenerator_activation');
function refgenerator_activation(){
	add_option('refgen_post_parent_id','post');
	add_option('refgen_post_content_tag','div');
	add_option('refgen_post_content_class','post-content');
	add_option('refgen_post_links_class','post-links');
	add_option('refgen_method','php');
	add_option('refgen_display_settings','');
	add_option('refgen_display_title','Post external references');
}

//2 - ADMIN FUNCTIONS 

//! function to call the plugin admin page : options-refgenerator.php
add_action('admin_menu','refgenerator_admin');
function refgenerator_admin() {
	add_options_page('RefGenerator', 'RefGenerator', 'manage_options', 'refgenerator/options-refgenerator.php');
}

//! javascript used by the plugin admin page : options-refgenerator.php
add_action('admin_head','refgenerator_admin_js');
function refgenerator_admin_js() {
	wp_enqueue_script('refgenerator-admin', plugins_url('js/refgenerator-admin.js', __FILE__), array('jquery'), false, true);
}

//3 - TEMPLATE FUNCTIONS

$refgen['method']             = get_option('refgen_method');
$refgen['post_links_class']   = get_option('refgen_post_links_class');
$refgen['display_settings']   = get_option('refgen_display_settings');
$refgen['display_title']      = get_option('refgen_display_title');
$refgen['post_parent_id']     = get_option('refgen_post_parent_id');
$refgen['post_content_tag']   = get_option('refgen_post_content_tag');
$refgen['post_content_class'] = get_option('refgen_post_content_class');

//!function for the PHP method
function php_simple_refgenerator($content) {
	global $refgen;
	$ref_title  = __('reference #', 'refgen');
	$links      = array();
	$userLinks  = array();
	$index      = 1;
	$references = NULL;
	$url = get_bloginfo('url');
	//The script starts here....
	//we extract all links from the content
	if (preg_match_all('|<a(.*)>|U', $content, $L, PREG_SET_ORDER)) {
		foreach ($L as $o) {
			//if this is a link and it's url does not start with the blog url then it is an external link to the blog
			if (preg_match(', href="(.*?)",i', $o[1], $M) && strpos($M[1], $url) === false) {
				if (!in_array($M[1], $userLinks) && !preg_match(',^(javascript:|mailto:|#),i', $M[1])) { 
					$userLinks[]            = $M[1];
					$links[$index]['href']  = $M[1];
					$links[$index]['title'] = ( preg_match(', title="(.*?)",i', $o[1] , $M ) ) ? $M[1]:NULL;
					$links[$index]['lang']  = ( preg_match(', lang="(.*?)",i', $o[1] , $M ) ) ? $M[1]:NULL;
					$index++;
				}
			}
		}
		//references formatting
		if (!empty($links)) {
			$res   = array();
			$res[] = '<div class="'.$refgen['post_links_class'].'">';
			$res[] = '<h3>' . $refgen['display_title'] . '</h3>';
			$res[] = '<ol>';
			foreach ($links as $k => $v) {
				$lang = (!is_null($v['lang'])) ? ' lang="' . $v['lang'] . '"' : '';
				if (is_null($v['title'])) { 
					$v['title'] = $ref_title.$k;
				}
				$res[] = '<li><a href="'.$v['href'].'"'.$lang.' rel="nofollow" target="_blank">'.$v['title'].'</a><br/><small>'.$v['href']."</small></li>";
			}
			$res[] = '</ol>';
			$res[] = '</div>';
			$references = implode(PHP_EOL, $res);
		}
	}
	return $content.$references;
}

//! function to call depending on your settings ( The Method and In Which Page to display RefGenerator
add_action('get_header', 'refgenerator');
function refgenerator() {
	global $refgen;
	$refgen_display = false; //if nothing is set...nothing will be shown
	if (!is_array($refgen['display_settings'])) {
		$refgen_display = true;
	} else {
		foreach ($refgen['display_settings'] as $page ){
			if (function_exists($page) && $page()) { 
				$refgen_display = true; 
				break;
			}
		}
	}
	if ($refgen_display) {
		wp_enqueue_style('refgenerator-screen', plugins_url('css/screen.css', __FILE__), array(), false, 'screen');
		wp_enqueue_style('refgenerator-print', plugins_url('css/print.css', __FILE__), array(), false, 'print');
		if ($refgen['method'] === 'php') {
			add_filter('the_content', 'php_simple_refgenerator');
		} else {
			// Add some parameters for the JS.
			$data = $refgen;
			unset($data['method'], $data['display_settings']);
			$data['a_title'] = __('reference #', 'refgen');
			$data['blog_url'] = get_bloginfo('url');
			wp_enqueue_script('refgenerator', plugins_url('js/refgenerator.js', __FILE__), array('jquery'), false, true);
			wp_localize_script('refgenerator', 'refgen', $data);
		}
	}
}
