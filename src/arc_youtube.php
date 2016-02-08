<?php

$plugin['name'] = 'arc_youtube';

$plugin['version'] = '2.0.3';
$plugin['author'] = 'Andy Carter';
$plugin['author_uri'] = 'http://andy-carter.com/';
$plugin['description'] = 'Embed Youtube videos with customised player';
$plugin['type'] = 0;

@include_once('zem_tpl.php');

if (0) {
?>
# --- BEGIN PLUGIN HELP ---

h1. arc_youtube

Easily embed Youtube videos in articles and customise the appearance of the player. arc_youtube uses the new iframe player.

h2. Table of contents

# "Plugin requirements":#help-section01
# "Installation":#help-section02
# "Tags":#help-section03
# "Examples":#help-section04
# "Author":#help-section05
# "License":#help-section06

h2(#help-section01). Plugin requirements

arc_youtube's minimum requirements:

* Textpattern 4.5+

h2(#help-section02). Installation

To install go to the 'plugins' tab under 'admin' and paste the plugin code into the 'Install plugin' box, 'upload' and then 'install'. Please note that you will need to set-up a custom field to use for associating videos with articles, unless you choose to directly embed the new tag in the article text.

h2(#help-section03). Tags

h3. arc_youtube

Embeds a Youtube video in the page using an iframe.

bc. <txp:arc_youtube />

h4. Video and playlist attributes

* _video_ - Youtube url[1] or video ID for the video you want to embed
* _playlist_ - Youtube playlist ID for the playlist you want to embed
* _custom_ - Name of the custom field containing video IDs/urls associated with article
* _start_ - Start position of the video as an integer
* _auto_ - '1' to autoplay the video, '0' to turn off autoplay (default)
* _loop_ - '1' to play the video in a loop, '0' for a single play
* _playsinline_ - '1' causes inline playback on iOS, '0' fullscreen playback

fn1. The url can be either for an individual video or for a playlist.

h4. Basic attributes

* _link_ - Set to '1' to show a link to the Youtube video page below the player; the link text will be the URL or the text between opening and closing tags
* _label_ - Label for the video
* _labeltag_ - Independent wraptag for label
* _wraptag_ - HTML tag to be used as the wraptag, without brackets
* _class_ - CSS class attribute for wraptag

h4. Customising the Youtube player

Some of the attributes with this plugin are subject to change if Youtube change their supported parameters for the player. Some will not work with the HTML5 player which the player defaults to if the browser supports it.

* _width_ - Width of video
* _height_ - Height of video
* _ratio_ - Aspect ratio (defaults to 4:3)
* _theme_ - Use either the "dark" or "light" Youtube player
* _color_ - Use either a "red" or "white" video progress bar
* _modestbranding_ - '1' to prevent the YouTube logo from displaying in the control bar
* _fs_ - '1' to allow full screen, '0' to  disable full screen mode[2]
* _cc_ - '1' to display captions/subtitles by default, '0' to use the user's preference[2]
* _related_ - '1' to show related videos, '0' to turn them off
* _privacy_ - '1' for enhanced privacy mode, no cookies unless the user clicks play, '0' normal mode
* _autohide_ - '2' for the video progress bar to fade out while the player controls (play button, volume control, etc.) remain visible; '1' for the video progress bar and the player controls will slide out of view a couple of seconds after the video starts playing; or '0' to always show the progress bar and controls
* _controls_ - '2' to display the controls in the player, '0' to hide them
* _annotations_ - '1' to show annotations, '3' to hide them
* _title_ - '0' to hide the video's title and other information, '1' by default to show the information

fn2. Not supported by the HTML5 player.

h3. arc_if_youtube

The arc_is_youtube tag is a _conditional tag_ and always used as an opening and closing pair. It will render the content between the tags if the video attribute is a valid Youtube URL.

bc. <txp:arc_if_youtube video="[URL]"></txp:arc_if_youtube>

h4. Parameters

Use one or the other of the following:-

* _custom_ - Name of the custom field containing video IDs/urls associated with article
* _video_ - A URL to check if it is a valid Youtube URL

h2(#help-section04). Examples

h3. Example 1: Use custom field to associate video with an article

bc. <txp:arc_youtube custom="Youtube" />

h3. Example 2: Customise the appearance of the player and associate video with custom field

bc. <txp:arc_youtube theme="light" modestbranding="1" custom="Youtube" />

This will use the _light_ player theme with modest branding (__i.e.__ removes the Youtube logo from the controls). As example 1, the video is selected using an article's custom field called 'Youtube'.

h3. Example 3: Small video player with fixed video

bc. <txp:arc_youtube width="200" video="tgbNymZ7vqY" />

Here the video is defined within the tag using the video attribute which has been given the value of Youtube's video ID, alternatively this value could have been the video's URL.

h2(#help-section05). Author

"Andy Carter":http://andy-carter.com. For other Textpattern plugins by me visit my "Plugins page":http://andy-carter.com/txp.

h2(#help-section06). License

The MIT License (MIT)

Copyright (c) 2016 Andy Carter

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

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
        'modestbranding' => null,
        'playsinline' => null, // 0 or 1
        'title'     => null,
        'link'      => '0',
        'label'     => '',
        'labeltag'  => '',
        'wraptag'   => '',
        'class'     => __FUNCTION__
    ), $atts));

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
    $qString[] = 'theme=' . ($theme == 'dark' ? 'dark' : 'light');
    $qString[] = 'color=' . ($color == 'red' ? 'red' : 'white');

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
    if ($autohide !== null && in_array($autohide, array(0, 1, 2))) {
        $qString[] = 'autohide=' . $autohide;
    }
    if ($controls !== null && in_array($controls, array(0, 1, 2))) {
        $qString[] = 'controls=' . $controls;
    }

    // Enable/Disable annotations display by default.
    if ($annotations !== null && in_array($annotations, array(1, 3))) {
        $qString[] = 'iv_load_policy=' . $annotations;
    }

    // Enable looping of the video.
    if ($loop) {
        $qString[] = 'loop=1';
    }

    // Enable modest browsing (removes the Youtube logo from the controls).
    if ($modestbranding) {
        $qString[] = 'modestbranding=1';
    }

    if ($playsinline !== null && in_array($playsinline, array(0, 1))) {
        $qString[] = 'playsinline=' . $playsinline;
    }

    // Set the start position of the video.
    if ($start) {
        $qString[] = 'start=' . $start;
    }

    // Show or hide the video title and other information.
    if ($title!==null) {
        $qString[] = 'showinfo=' . ($title ? '1' : '0');
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
        if ($matches[0] && $matches[1] != 0 && $matches[2] != 0) {
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

    $out = '<iframe width="' . $width . '" height="' . $height
      . '" src="' . $src . '" frameborder="0"'
      . (($fs)?' allowfullscreen':'') . '></iframe>';

    if ($link) {
        $url = 'www.youtube.com/watch?v=' . $v;
        $out .= '<p><a href="http://' . $url . '" rel="external">'
            . (($thing)?parse($thing):$url)
            . '</a></p>';

    }

    return doLabel($label, $labeltag).(($wraptag) ? doTag($out, $wraptag, $class) : $out);
}

function arc_if_youtube($atts, $thing)
{
    global $thisarticle;

    extract(lAtts(array(
        'custom' => null,
        'video' => null
    ), $atts));

    $result = $video ? _arc_youtube($video) : _arc_youtube($thisarticle[strtolower($custom)]);

    return parse(EvalElse($thing, $result));
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

    } elseif (preg_match("/^[a-zA-Z]+[:\/\/]+youtu\.be\/([A-Za-z0-9]+)/i", $video, $matches)) {
        return array('v' => $matches[1]);

    }

    return false;
}

# --- END PLUGIN CODE ---

?>
