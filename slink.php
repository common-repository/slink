<?php
/*
Plugin Name: slink
Plugin URI: http://www.bywrights.com/software/slink
Description: Defines named links for use in post/page content.
Author: Chris Wright
Version: 0.1
Author URI: http://www.bywrights.com

slink
Copyright 2009 Chris Wright

This plugin is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this plugin; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

class slink_tag {
	
	private $name;
	private $replacement_pattern;
	private $is_function;
	private $properties;
	
	public function slink_tag( $name, $replacement_pattern, $is_function ) {
		
		$this->name = $name;
		$this->replacement_pattern = $replacement_pattern;
		$this->is_function = $is_function;
		$this->properties = array();
	}
	
	public function add_property( $key, $value ) {
		$this->properties[$key] = $value;
	}
	
	public function replaced_pattern( $text, $uri ) {
		
		$result = $this->replace_pattern( $text, $uri );
		if ($this->is_function) {
			$fn = create_function( '', $result );
			$result = $fn();
		}
		return $result;
	}
	
	private function replace_pattern( $text, $uri ) {
	
		$this->properties['text'] = $text;
		
		if ($uri[0] == '/')
			$uri = substr( $uri, 1 );
		$this->properties['uri'] = $uri;
		
		return preg_replace_callback( 
			'/\$(\w+)/i', 
			array('slink_tag', 'replace_pattern_match'), $this->replacement_pattern );
	}
	
	private function replace_pattern_match($matches) {
					
		$key = $matches[1];
		if (array_key_exists($key, $this->properties))
			return $this->properties[$key];
		else
			throw new Exception( "Property '$key' not set for tag: " . $this->name );
	}
}

class slinkPlugin{

	private $tags;

	public function slinkPlugin(){
		
		add_filter('the_content', array(&$this,'filter'), 1);
		add_filter('the_content_rss', array(&$this,'filter'), 1);
		add_filter('the_excerpt', array(&$this,'filter'), 1);
		add_filter('the_excerpt_rss', array(&$this,'filter'), 1);
		
		$this->tags = array();
	}
	
	public function add_tag( $tag, $replacement_pattern ) {
		
		$tag = strtolower( $tag );
		$this->tags[$tag] = new slink_tag( $tag, $replacement_pattern, false );
	}
	
	public function add_func( $tag, $code ) {
		
		$tag = strtolower( $tag );
		$this->tags[$tag] = new slink_tag( $tag, $code, true );
	}
	
	public function add_tag_property( $tag, $key, $value ) {
		
		$tag = strtolower( $tag );
		if (array_key_exists( $tag, $this->tags ))
			$this->tags[$tag]->add_property( $key, $value );
		else
			throw "Tag is not defined: $tag";
	}
	
	public function filter( $content ) {
		
		// [name: text goes here] ( my/uri/here.jpg )
		return preg_replace_callback(
			"/\[(\w*):\s+(.*?)\]\s*\((.*?)\)/", 
			array('slinkPlugin', 'filter_link'), $content );
	}
	
	function filter_link($match) { 

		$tag = strtolower( $match[1] );
		$text = $match[2];
		$uri = trim( $match[3] );
		
		if (array_key_exists( $tag, $this->tags )) {
			return $this->tags[$tag]->replaced_pattern( $text, $uri );
		}
		else {
			return "Error, tag $tag not defined for " . $match[0];
		}
	}
}
$wp_slink = new slinkPlugin();
$GLOBALS['wp_slink'] = &$wp_slink; 

function slink_tag( $tag, $replacement_pattern ) { global $wp_slink; $wp_slink->add_tag( $tag, $replacement_pattern ); }
function slink_func( $tag, $code ) { global $wp_slink; $wp_slink->add_func( $tag, $code ); }
function slink_filter( $content ) { global $wp_slink; return $wp_slink->filter( $content ); }

// include the config file
{
	$folder = realpath(dirname(__FILE__));
	if (file_exists( $folder . '/slink_config.php' )) {
		require_once( $folder . '/slink_config.php' );
	} else {
		require_once( $folder . '/slink_config_default.php' );	
	}
}

?>