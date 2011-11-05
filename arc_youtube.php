<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'arc_youtube';

$plugin['version'] = '0.4';
$plugin['author'] = 'Andy Carter';
$plugin['author_uri'] = 'http://www.redhotchilliproject.com/';
$plugin['description'] = 'Embed Youtube videos with customised player';
$plugin['type'] = 1; // 0 for regular plugin; 1 if it includes admin-side code

if (!defined('PLUGIN_LIFECYCLE_NOTIFY')) define('PLUGIN_LIFECYCLE_NOTIFY', 0x0002);
$plugin['flags'] = PLUGIN_LIFECYCLE_NOTIFY;

@include_once('../zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h3. Description

Easily embed valid XHTML 1.0 markup Youtube videos in articles and customise the appearance of the player.


h3. Installation

To install go to the 'plugins' tab under 'admin' and paste the plugin code into the 'Install plugin' box, 'upload' and then 'install'. Finally activate the plugin. Please note that you will need to set-up a custom field to use for associating videos with articles, unless you choose to directly embed the new tag in the article text.


h3. Syntax

bc.. <txp:arc_youtube />

<txp:arc_youtube video="tgbNymZ7vqY" width="500" ratio="16:9" />

<txp:arc_youtube video="http://uk.youtube.com/watch?v=tgbNymZ7vqY" width="500" ratio="16:9" />

p. or,

bc. <txp:arc_youtube link="1">Link</txp:arc_youtube>


h3. Usage

h4. Video and playlists

|_. Attribute|_. Description|_. Default|_. Example|
|video|Youtube url[1] or video ID for the video you want to embed| _unset_|video="ta7VHKGuoJY"|
|playlist|Youtube playlist ID for the playlist you want to embed| _unset_|playlist="2DBFB60D581AB901"|
|custom|Name of the custom field containing video IDs/urls associated with article|Youtube ID|custom="video"|

fn1. The url can be either for an individual video or for a playlist.

h4. Basics

|_. Attribute|_. Description|_. Default|_. Example|
|link|Set to '1' to show a link to the Youtube video page below the player; the link text will be the URL or the text between opening and closing tags|0|link="1"|
|label|Label for the video| _no label_|label="Youtube video"|
|labeltag|Independent wraptag for label| _empty_|labeltag="h3"|
|wraptag|HTML tag to be used as the wraptag, without brackets| _unset_|wraptag="div"|
|class|CSS class attribute for wraptag|arc_youtube|class="youtube"|


h4. Customising the Youtube player

You can customise the appearance of the Youtube flash player using this plugin to define colours, size, and language settings.

|_. Attribute|_. Description|_. Default|_. Example|
|width|Width of video|0|width="200"|
|height|Height of video|0|height="150"|
|ratio|Aspect ratio|4:3|ration="16:9"|
|color1|Six-digit hexadecimal colour code (if color1 and color2 are unset then the default Youtube colour scheme is used)| _unset_|color1="2b405b"|
|color2|Six-digit hexadecimal colour code| _unset_|color2="6b8ab6"|
|border|'1' to show border, '0' to hide border|0|border="1"|
|fs|'1' to allow full screen, '0' to  disable full screen mode|1| |
|hd|'1' to play video in HD, '0' to for normal play|0| |
|auto|'1' to autoplay the video, '0' to turn off autoplay (default)|0| |
|privacy|'1' for enhanced privacy mode, no cookies unless the user clicks play, '0' normal mode|0|privacy='1'|
|ssl|'1' to use HTTPS protocol|0| |
|lang|Language code for player, by default this is set to English "en"|en|lang="fr"|


h3. Examples

h4. Example 1: Use custom field to associate video with an article

<txp:arc_youtube custom="Youtube" />

h4. Example 2: Customise the appearance of the player and associate video with custom field

bc. <txp:arc_youtube color1="006699" color2="54abd6" border="1" custom="Youtube" />

This will place a border around the player and change its colour scheme to a blue one. As example 1, the video is selected using an article's custom field called 'Youtube'.

h4. Example 3: Small video player with fixed video

bc. <txp:arc_youtube width="200" video="tgbNymZ7vqY" />

Here the video is defined within the tag using the video attribute which has been given the value of Youtube's video ID, alternatively this value could have been the video's URL.

h4. Example 4: Embed a playlist

bc. <txp:arc_youtube video="http://www.youtube.com/view_play_list?p=2DBFB60D581AB901" />

This embeds a Youtube playlist (a single player that cycles through the videos in the playlist). Alternatively the playlist ID can be used using the 'playlist' attribute:-

bc. <txp:arc_youtube playlist="2DBFB60D581AB901" />


# --- END PLUGIN HELP ---
<?php
}

# --- BEGIN PLUGIN CODE ---

if (@txpinterface == 'admin')
{
	register_callback('_arc_youtube_auto_enable', 'plugin_lifecycle.arc_youtube', 'installed');
}

function arc_youtube($atts,$thing)
{
	global $thisarticle;

    extract(lAtts(array(
        'video'     => '',
        'playlist'  => '',
        'custom'    => 'Youtube ID',
        'width'     => '0',
        'height'    => '0',
        'ratio'     => '4:3',
        'color1'    => '',
        'color2'    => '',
        'border'    => '0',
        'fs'        => '1',
        'hd'        => '0',
        'privacy'   => '0',
        'ssl'       => '0',
        'auto'      => '0',
        'lang'      => 'en',
        'link'      => '0',
        'label'     => '',
        'labeltag'  => '',
        'wraptag'   => '',
        'class'     => __FUNCTION__
    ),$atts));

    $custom = strtolower($custom);
    if (!$video && isset($thisarticle[$custom])) {
        $video = $thisarticle[$custom];
    }

    $v = ""; $p = "";

    // Check for Youtube video ID or Youtube URL to extract ID from

    if (preg_match("/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.youtube\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i",$video)) {

        $urlc = parse_url($video);
        $qstr = $urlc['query'];
        parse_str($qstr, $qarr);

        if (isset($qarr['v'])) {

            $v = $qarr['v'];

        } elseif (isset($qarr['p'])) {

            $p = $qarr['p'];

        } else {

            return '';

        }
        
    } elseif (preg_match("/^[a-zA-Z]+[:\/\/]+youtu\.be\/([A-Za-z0-9]+)/i",$video,$matches)) {

        $v = $matches[1];
        
    } elseif ($video) {

        $v = $video;

    } elseif ($playlist) {

        $p = $playlist;

    } else {

        return '';

    }

    if ($p) {

        $vlink = 'http'.(($ssl)?'s':'').'://www.youtube'
          .(($privacy)?'-nocookie':'').'.com/p/'.$p;

    } elseif ($v) {

        $vlink = 'http'.(($ssl)?'s':'').'://www.youtube'
          .(($privacy)?'-nocookie':'').'.com/embed/'.$v;

    }

    if ($v||$p) {
    
        $toolbar_h = 25;
    
        if (!$width || !$height) {
          // calculate the aspect ratio
          preg_match("/([0-9]+):([0-9]+)/",$ratio,$matches);
          if ($matches[0] && $matches[1]!=0 && $matches[2]!=0) {
            $aspect = $matches[1]/$matches[2];
          } else {
            $aspect = 1.333;
          }
          // calcuate the new width/height
          if ($width) {
            $height = $width/$aspect + $toolbar_h;
          } elseif ($height) {
            $width = ($height-$toolbar_h)*$aspect;
          } else {
            $width = 425;
            $height = 344;
          }
        }

        $src = $vlink.'&amp;hl='.$lang
            .(($fs)?'&amp;fs=1':'')
            .(($auto)?'&amp;autoplay=1':'')
            .(($color1)?'&amp;color1=0x'.$color1:'')
            .(($color2)?'&amp;color2=0x'.$color2:'')
            .(($border)?'&amp;border=1':'')
            .(($hd)?'?hd=1':'');

        $out = '<object type="application/x-shockwave-flash" '
            .'style="width:'.$width.'px; height:'.$height.'px;" '
            .'data="'.$src.'">'
            .'<param name="movie" value="'.$src.'"></param>'
            .'<param name="allowFullScreen" value="'.(($fs)?'true':'false').'"></param>'
            .'<param name="allowscriptaccess" value="always"></param>'
            .'</object>';

        if ($link) {

            $url = 'www.youtube.com/watch?v='.$v;
            $out.= '<p><a href="http://'.$url.'">'
                .(($thing)?parse($thing):$url)
                .'</a></p>';

        }

        return doLabel($label, $labeltag).(($wraptag) ? doTag($out, $wraptag, $class) : $out);

    }

}

// Auto enable plugin on install (original idea by Michael Manfre)
function _arc_youtube_auto_enable($event, $step)
{ 
  $plugin = substr($event, strlen('plugin_lifecycle.'));
  $prefix = 'arc_youtube';
  if (strncmp($plugin, $prefix, strlen($prefix)) == 0)
  {
    safe_update('txp_plugin', "status = 1", "name = '" . doSlash($plugin) . "'");
  }
}

# --- END PLUGIN CODE ---

?>
