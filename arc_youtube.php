<?php

// This is a PLUGIN TEMPLATE.

// Copy this file to a new name like abc_myplugin.php.  Edit the code, then
// run this file at the command line to produce a plugin for distribution:
// $ php abc_myplugin.php > abc_myplugin-0.1.txt

// Plugin name is optional.  If unset, it will be extracted from the current
// file name. Uncomment and edit this line to override:
$plugin['name'] = 'arc_youtube';

$plugin['version'] = '1.2';
$plugin['author'] = 'Andy Carter';
$plugin['author_uri'] = 'http://andy-carter.com/';
$plugin['description'] = 'Embed Youtube videos with customised player';
$plugin['type'] = 0;

@include_once('zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h3. Description

Easily embed Youtube videos in articles and customise the appearance of the player. arc_youtube uses the new iframe player. 


h3. Installation

To install go to the 'plugins' tab under 'admin' and paste the plugin code into the 'Install plugin' box, 'upload' and then 'install'. Please note that you will need to set-up a custom field to use for associating videos with articles, unless you choose to directly embed the new tag in the article text.


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
|start|Start position of the video as an integer|0|start="60"|
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
|theme|Use either the "dark" or "light" Youtube player|dark|theme="light"|
|color|Use either a "red" or "white" video progress bar|red|color="white"|
|fs|'1' to allow full screen, '0' to  disable full screen mode|1| |
|auto|'1' to autoplay the video, '0' to turn off autoplay (default)|0| |
|cc|'1' to display captions/subtitles by default, '0' to use the user's preference|0| |
|related|'1' to show related videos, '0' to turn them off|1| |
|privacy|'1' for enhanced privacy mode, no cookies unless the user clicks play, '0' normal mode|0|privacy='1'|


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

function arc_youtube($atts, $thing)
{
	global $thisarticle;

    extract(lAtts(array(
        'video'     => '',
        'playlist'  => '',
        'custom'    => 'Youtube ID',
        'width'     => '0',
        'height'    => '0',
        'ratio'     => '4:3',
        'color'     => 'red', // or 'white'
        'fs'        => '1',
        'cc'        => 0,
        'related'   => 1,
        'start'     => 0,
        'theme'     => 'dark',
        'privacy'   => '0',
        'auto'      => '0',
        'autohide'  => null,
        'controls'  => null,
        'annotations' => null, // 1 or 3
        'loop'      => 0,
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

    $v = null;
    $p = null;

    // Check for Youtube video ID or Youtube URL to extract ID from
    $match = _arc_youtube($video);
    if (!empty($match['v'])) {
        $v = $match['v'];
    } elseif (!empty($match['p'])) {
        $p = $match['p'];
    } elseif (!empty($video)) {
        $v = $video;
    } elseif (!empty($playlist)) {
        $p = $playlist;
    } else {
        return '';
    }

    $src = '//www.youtube' . ($privacy ? '-nocookie' : '') . '.com/embed/';

    $src .= !empty($v) ? $v : null;

    $qString = array();

    // Setup the playlist.
    if (!empty($p)) {
        $qString[] = 'listType=playlist';
        $qString[] = 'list=' . $p;
    }

    // Set the player UI's theme and colour.
    $qString[] = 'theme=' . ($theme=='dark' ? 'dark' : 'light');
    $qString[] = 'color=' . ($color=='red' ? 'red' : 'white');

    // Disable the fullscreen button in the AS3 player (not supported by the 
    // newer HTML5 player).
    if (!$fs) {
        $qString[] = 'fs=0';
    }

    // Enable autoplay.
    if ($auto) {
        $qString[] = 'autoplay=1';
    }

    // Determine the appearance of the player's controls.
    if ($autohide!==null && in_array($autohide, array(0, 1, 2))) {
        $qString[] = 'autohide=' . $autohide;
    }
    if ($controls!==null && in_array($controls, array(0, 1, 2))) {
        $qString[] = 'controls=' . $controls;
    }

    // Enable/Disable annotations display by default.
    if ($annotations!==null && in_array($annotations, array(1, 2))) {
        $qString[] = 'iv_load_policy=' . $annotations;
    }

    // Enable looping of the video.
    if ($loop) {
        $qString[] = 'loop=1';
    }

    // Set the start position of the video.
    if ($start) {
        $qString[] = 'start=' . $start;
    }

    // Enable captions on by default in the AS3 player (not supported by the
    // newer HTML5 player).
    if ($cc) {
        $qString[] = 'cc_load_policy=1';
    }

    // Disable related videos.
    if (!$related) {
        $qString[] = 'rel=0';
    }

    // Check if we need to append a query string to the video src.
    if (!empty($qString)) {
        $src .= '?' . implode('&amp;', $qString);
    }

    // If the width and/or height has not been set we want to calculate new
    // ones using the aspect ratio.
    if (!$width || !$height) {

        $toolbarHeight = 25;
        
        // Work out the aspect ratio.
        preg_match("/(\d+):(\d+)/", $ratio, $matches);
        if ($matches[0] && $matches[1]!=0 && $matches[2]!=0) {
            $aspect = $matches[1]/$matches[2];
        } else {
            $aspect = 1.333;
        }

        // Calcuate the new width/height.
        if ($width) {
            $height = $width/$aspect + $toolbarHeight;
        } elseif ($height) {
            $width = ($height-$toolbarHeight)*$aspect;
        } else {
            $width = 425;
            $height = 344;
        }

    }

    $out = '<iframe width="'.$width.'" height="'.$height
      .'" src="'.$src.'" frameborder="0"'
      .(($fs)?' allowfullscreen':'').'></iframe>';

    if ($link) {

        $url = 'www.youtube.com/watch?v='.$v;
        $out.= '<p><a href="http://'.$url.'" rel="external">'
            .(($thing)?parse($thing):$url)
            .'</a></p>';

    }

    return doLabel($label, $labeltag).(($wraptag) ? doTag($out, $wraptag, $class) : $out);

}


function arc_if_youtube($atts, $thing)
{
    extract(lAtts(array(
        'video' => ''
    ), $atts));

    return parse(EvalElse($thing, _arc_is_youtube($video)));
}


function _arc_youtube($video)
{
    if (preg_match("/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.youtube\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i", $video)) {
        
        $urlc = parse_url($video);
        $qstr = $urlc['query'];
        parse_str($qstr, $qarr);

        if (isset($qarr['v'])) {

            return array('v' => $qarr['v']);

        } elseif (isset($qarr['p'])) {

            return array('p' => $qarr['p']);

        } else {

            return false;

        }

    }  elseif (preg_match("/^[a-zA-Z]+[:\/\/]+youtu\.be\/([A-Za-z0-9]+)/i", $video, $matches)) {

        return array('v' => $matches[1]);
        
    }

    return false;
}

# --- END PLUGIN CODE ---

?>
