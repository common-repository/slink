<?php  
/*

	Please refer to Configuration File in slink.php for instructions on editing this file.

*/

// this tag allows us to show our example without a lot of grief
slink_tag( 'name', 
	'[name: $text] ($uri)' );

// show a screenshot from a folder on another server, using the same styling as WP does
slink_tag( 'screenshot', 
	'<div class="wp-caption alignright"><img src="http://media.bywrights.com/screenshots/$uri" alt="$text" /><p class="wp-caption-text">$text</p></div>' );

// show a download box
slink_tag( 'download', 
	'<a href="http://www.bywrights.com/downloads/$uri" title="$text" /><div class="downloadbox"><h2>Download</h2><h2>$text</h2></div></a>' );

// link to another article in a particular area of the site
slink_tag( 'help_link', 
	'<a href="/help/$uri" title="$text" />$text</a>' );

// call some php to include the contents of another page in this page	
slink_func( 'include_page',
	// since we're including page content that still needs to be filtered, and we've already started the filtering for slink, 
	//  we need to re-invoke slink on the new content
 	'return slink_filter( iinclude_page( "$uri", "displayTitle=true&titleBefore=<h3>&titleAfter=</h3>&filter=false", true ) );' );
	
	// you will need to install the iinclude_page plugin in order to make this work
	// you will also have to code around a limitation in the current iinclude release, I'm working on getting 
	//  that into the iinclude base.  The "filter" parameter is unimplemented right now, it tells iinclude_page
	//  not to apply filters to the retrieved content (doing so breaks slink).
?>