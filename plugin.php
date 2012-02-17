<?php
namespace Podlove;

register_activation_hook(   PLUGIN_FILE, __NAMESPACE__ . '\activate' );
register_deactivation_hook( PLUGIN_FILE, __NAMESPACE__ . '\deactivate' );
register_uninstall_hook(    PLUGIN_FILE, __NAMESPACE__ . '\uninstall' );

function activate() {
	Model\Feed::build();
	Model\Format::build();
	Model\Show::build();
	
	if ( ! Model\Format::has_entries() ) {
		$default_formats = array(
			// @TODO slug => format_suffix
			array( 'name' => 'MP3 Audio',  'slug' => '-legacy',    'type' => 'audio', 'mime_type' => 'audio/mpeg',  'extension' => 'mp3' ),
			array( 'name' => 'MPG Video',  'slug' => '-legacy',    'type' => 'video', 'mime_type' => 'video/mpeg',  'extension' => 'mpg' ),
			array( 'name' => 'MP4 Audio',  'slug' => '-modern',    'type' => 'audio', 'mime_type' => 'audio/mp4',   'extension' => 'm4a' ),
			array( 'name' => 'MP4 Video',  'slug' => '-modern',    'type' => 'video', 'mime_type' => 'video/mp4',   'extension' => 'm4v' ),
			array( 'name' => 'OGG Audio',  'slug' => '-oldschool', 'type' => 'audio', 'mime_type' => 'audio/ogg',   'extension' => 'oga' ),
			array( 'name' => 'OGG Video',  'slug' => '-oldschool', 'type' => 'video', 'mime_type' => 'video/ogg',   'extension' => 'ogv' ),
			array( 'name' => 'WebM Audio', 'slug' => '-chrome-audio',    'type' => 'audio', 'mime_type' => 'audio/webm',  'extension' => 'webm' ),
			array( 'name' => 'WebM Video', 'slug' => '-chrome-video',    'type' => 'video', 'mime_type' => 'video/webm',  'extension' => 'webm' ),
		);
		
		foreach ( $default_formats as $format ) {
			$f = new Model\Format;
			foreach ( $format as $key => $value ) {
				$f->{$key} = $value;
			}
			$f->save();
		}
	}
	
	if ( ! Model\Show::has_entries() ) {
		$show                        = new Model\Show;
		$show->name                  = \Podlove\t( 'My Podcast' );
		$show->slug                  = \Podlove\t( 'my-podcast' );
		$show->subtitle              = \Podlove\t( 'I can haz listeners?' );
		$show->owner_email           = get_bloginfo( 'admin_email' );
		$show->explicit              = false;
		$show->url_delimiter         = '-';
		$show->episode_number_length = 3;
		$show->save();
		
		$feed                   = new Model\Feed;
		$feed->show_id          = $show->id;
		$feed->format_id        = Model\Format::find_one_by_name( 'MP3 Audio' )->id;
		$feed->name             = \Podlove\t( 'My Awesome Podcast Feed (MP3)' );
		$feed->title            = \Podlove\t( 'My Awesome Podcast Feed' );
		$feed->slug             = \Podlove\t( 'my-awesome-podcast-feed' );
		$feed->block            = false;
		$feed->discoverable     = true;
		$feed->show_description = true;
		$feed->save();
	}
}
// Quick Fix: In Multisite installs we need to create tables and seed data for
// every blog. So, well, simulate a click on every hit. Not nice but works.
if ( is_admin() ) {
	activate();
}

function deactivate() {

}

function uninstall() {
	Model\Feed::destroy();
	Model\Format::destroy();
	Model\Show::destroy();
}

add_action( 'init', function () {
	new Podcast_Post_Type();
});
