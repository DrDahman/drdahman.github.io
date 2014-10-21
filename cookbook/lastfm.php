<?php if (!defined('PmWiki')) exit();
/*******************************************************************
  file lastfm.php for PmWiki 2.

  Copyright 2007 Jon Haupt.
  This file is distributed under the terms of the GNU General Public
  License as published by the Free Software Foundation; either
  version 2 of the License, or (at your option) any later version.

  This recipe allows you to incorporate Last.fm widgets into your
  PmWiki installation.
  
  To install, place lastfm.php in your cookbook directory, then 
  add the following to config.php or another local configuration
  file:
  include_once("$FarmD/cookbook/lastfm.php");
  
  You can embed radio players, playlist players, quilts, or charts.

  For more information, see: http://last.fm/widgets

  The recipe enables the (:lastfm:) markup.  You add arguments 
  to customize the widget.

  Here are the arguments:

  type (chart, radio, playlist, quilt)
  user 
  color/colour (red, blue, black, grey)
  chart (recent, topartists, toptracks, weeklyartists, weeklytracks)
  quilt (album, artist)
  orient (horizontal, vertical)
  size (small, medium, large for quilts; regular, mini for radios)
  autostart ( 1 for an autostarting radio )

  If you just put in a username and nothing else, you will get, by
  default, a red medium-sized chart of recent tracks played.

********************************************************************/
# Version date
$RecipeInfo['LastFM']['Version'] = '2007-05-12';

#Define Online Presence image markup
Markup('lastfm', 'inline',  '/\\(:lastfm (.*?):\\)/e', 'LastFM("$1")');

function LastFM($opts) {

  $defaults = array (
        'type' => 'chart',
        'user' => '',
        'color' => 'red',
        'chart' => 'recent',
        'quilt' => 'album',
        'orient' => 'vertical',
        'size' => 'medium',
        'autostart' => '',
        );

  $args = array_merge($defaults, ParseArgs($opts));
  if ($args['colour'] != '') $args['color'] = $args['colour'];

  if ($args['color'] == 'red') $bgcolor = 'd01f3c';
  if ($args['color'] == 'blue') $bgcolor = '6598cd';
  if ($args['color'] == 'black') $bgcolor = '000000';
  if ($args['color'] == 'grey') $bgcolor = '999999';

  # css is always the same.
  $output = '
    <style type="text/css">
      .lastfmWidget {float:left;width:100%;}
      .lastfmWidget object {float:left;}
      .lastfmWidget div {height:20px;}
      .lastfmWidget a {overflow:hidden;height:20px;margin:0;padding:0;text-decoration:none;}
      .lastfmHead a {float:left;background-repeat:no-repeat;background-position:0 -20px;}
      .lastfmHead a:hover {background-position: 0 0;}
      .lastfmFoot {clear:left;float:left;background-repeat:repeat-x;background-position:0 100%;}
      .lastfmFoot a {float:right;background-repeat:no-repeat;background-position:0 -20px;}
      .lastfmFoot a.config {width:85px;background-position: 0 -20px;}
      .lastfmFoot a.config:hover {background-position: 0 0;}
      .lastfmFoot a.view {width:74px;background-position:-85px -20px;}
      .lastfmFoot a.view:hover {background-position:-85px 0;}
      .lastfmFoot a.popup {width:25px;background-position:-159px -20px;}
      .lastfmFoot a.popup:hover {background-position:-159px 0;}
    </style>';
      
  switch ($args['type']) {

    case "chart":
      $width = 184;
      $a1_href = 'http://www.last.fm/user/'.$args['user'].'/charts/?charttype=';
      switch ($args['chart']) {
        case "recent":
          $a1_title = $args['user'].': Recently Listened Tracks';
          $a1_secondary = 'recenttracks';
          $charttype = 'recenttracks';
          $imgurl = 'http://panther1.last.fm/widgets/images/header/chart/recenttracks_regular_'.$args['color'].'.gif';
          $height = 179;
          break;
        case "toptracks":
          $a1_title = $args['user'].': Overall Top Tracks';
          $a1_secondary = 'overall&subtype=track';
          $charttype = 'overall';
          $subtype = 'track';
          $imgurl = 'http://panther1.last.fm/widgets/images/header/chart/toptracks_regular_'.$args['color'].'.gif';
          $height = 160;
          break;
        case "topartists":
          $a1_title = $args['user'].': Overall Top Artists';
          $charttype = 'overall';
          $subtype = 'artist';
          $imgurl = 'http://panther1.last.fm/widgets/images/header/chart/topartists_regular_'.$args['color'].'.gif';
          $height = 140;
          break;
        case "weeklytracks":
          $a1_title = $args['user'].': Weekly Top Tracks';
          $charttype = 'weekly';
          $subtype = 'track';
          $imgurl = 'http://panther1.last.fm/widgets/images/header/chart/weeklytracks_regular_'.$args['color'].'.gif';
          $height = 160;
          break;
        case "weeklyartists":
          $a1_title = $args['user'].': Weekly Top Artists';
          $charttype = 'weekly';
          $subtype = 'artist';
          $imgurl = 'http://panther1.last.fm/widgets/images/header/chart/weeklyartists_regular_'.$args['color'].'.gif';
          $height = 140;
          break;
      }
      if (!$a1_secondary) $a1_secondary = charttype.'&'.subtype;
      $swfurl = 'http://panther1.last.fm/widgets/chart/2.swf';
      $flashvars = 'type='.$charttype.'&amp;user='.$args['user'].'&amp;theme='.$args['color'];
      $popupurl = 'http://www.last.fm/tools/widgets/popup/?widget='.$args['type'].'&amp;colour='.$args['color'].'&amp;chartType='.$charttype.'&amp;user='.$args['user'].'&amp;from=widget&amp;resize=1';
      $getyourownurl = 'http://www.last.fm/tools/widgets/?widget=chart&amp;colour='.$args['color'].'&amp;chartType='.$charttype.'&amp;user='.$args['user'].'&amp;from=widget';
      break;
    case "quilt":

      switch ($args['orient']) {
        case "horizontal":
          $width=460;
          if ($args['size'] = 'small') $height = 135;
          if ($args['size'] = 'medium') $height = 180;
          if ($args['size'] = 'large') $height = 225;
          break;
        case "vertical":
          $width=184;
          if ($args['size'] = 'small') $height = 270;
          if ($args['size'] = 'medium') $height = 405;
          if ($args['size'] = 'large') $height = 540;
      }
      
      $a1_href = 'http://www.last.fm/user/'.$args['user'].'/charts/';
      if ($args ['quilt'] == 'artist') {
        $a1_title = 'Top artists';
        $flashfile = 'topartists';
        }
      else {
        $a1_title = 'Top albums';
        $flashfile = 'topalbums';
        }
      $imgurl = 'http://panther1.last.fm/widgets/images/header/quilt/'.$args['quilt'].'_'.$args['orient'].'_'.$args['color'].'.gif';
      $swfurl = 'http://panther1.last.fm/widgets/quilt/6.swf';
      $flashvars = 'type=user&amp;variable='.$args['user'].'&amp;file='.$flashfile.'&amp;bgColor='.$args['color'].'&amp;theme='.$args['color'];
      $popupurl = 'http://www.last.fm/tools/widgets/popup/?widget=quilt&amp;url=user%2F'.$args['user'].'%2Fpersonal&amp;quiltType='.$args['quilt'].'&amp;colour='.$args['color'].'&amp;orient='.$args['orient'].'&amp;height='.$args['size'].'&amp;from=widget&amp;resize=1';
      $getyourownurl = 'http://www.last.fm/tools/widgets/?widget=quilt&amp;url=user%2F'.$args['user'].'%2Fpersonal&amp;quiltType='.$args['quilt'].'&amp;colour='.$args['color'].'&amp;orient='.$args['orient'].'&amp;height=height='.$args['size'].'&amp;from=widget';
      break;
    case "radio";
      if ($args['size'] == "mini") $width = 110; 
      else {
        $width = 184;
        $args['size'] = "regular";
      }
      $a1_title = $args['user'].'&squo;s Radio Station';
      $a1_href = 'http://www.last.fm/listen/user/'.$args['user'].'/personal';
      $imgurl = 'http://panther1.last.fm/widgets/images/header/radio/my_'.$args['size'].'_'.$args['color'].'.gif';
      $height = 140;
      $swfurl = 'http://panther1.last.fm/widgets/radio/2.swf';
      $flashvars = 'lfmMode=radio&amp;radioURL=user%2F'.$args['user'].'%2Fpersonal&amp;title='.$args['user'].'%E2%80%99s+Radio+Station&amp;theme='.$args['color'].'&amp;autostart='.$args['autostart'];
      $popupurl = 'http://www.last.fm/tools/widgets/popup/?widget=radio&amp;url=user%2F'.$args['user'].'%2Fpersonal&amp;colour='.$args['color'].'&amp;width='.$args['size'].'&amp;autostart='.$args['autostart'].'&amp;from=widget&amp;resize=1';
      $getyourownurl = 'http://www.last.fm/tools/widgets/?widget=radio&amp;url=user%2F'.$args['user'].'%2Fpersonal&amp;colour='.$args['color'].'&amp;width='.$args['size'].'&amp;autostart='.$args['autostart'].'&amp;from=widget';
      break;
    case "playlist";
      if ($args['size'] == "mini") $width = 110; 
      else {
        $width = 184;
        $args['size'] = "regular";
      }
      $a1_title = $args['user'].'&squo;s Playlist';
      $a1_href = 'http://www.last.fm/listen/user/'.$args['user'].'/playlist';
      $imgurl = 'http://panther1.last.fm/widgets/images/header/playlist/my_'.$args['size'].'_'.$args['color'].'.gif';
      $height = 284;
      $swfurl = 'http://panther1.last.fm/widgets/playlist/2.swf';
      $flashvars = 'lfmMode=playlist&amp;resourceType=37&amp;resourceID=126537&amp;radioURL=user%2F'.$args['user'].'%2Fplaylist&amp;username='.$args['user'].'&amp;title='.$args['user'].'%E2%80%99s+Playlist&amp;theme='.$args['color'].'&amp;autostart='.$args['autostart'];
      $popupurl = 'http://www.last.fm/tools/widgets/popup/?widget=playlist&amp;user='.$args['user'].'&amp;colour='.$args['color'].'&amp;width='.$args['size'].'&amp;autostart='.$args['autostart'].'&amp;from=widget&amp;resize=1';
      $getyourownurl = '';
      break;
  }
  # Figure popup height
  $popupheight = $height + 80;

  # Finish output
  $output .='
    <div class="lastfmWidget">
      <div class="lastfmHead" style="width:'.$width.'px;">
        <a title="'.$a1_title.'" href="'.$a1_href.$a1_secondary.'" target="_blank" style="background-image:url('.$imgurl.');width:'.$width.'px;"></a>
      </div>
      <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="'.$width.'" height="'.$height.'" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab%23version=7,0,0,0">
        <param name="bgcolor" value="'.$bgcolor.'" />
        <param name="movie" value="'.$swfurl.'" />
        <param name="quality" value="high" />
        <param name="allowScriptAccess" value="sameDomain" />
        <param name="FlashVars" value="'.$flashvars.'" />
        <embed src="'.$swfurl.'" type="application/x-shockwave-flash" name="widgetPlayer" bgcolor="'.$bgcolor.'" width="'.$width.'" height="'.$height.'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer"  FlashVars="'.$flashvars.'" allowScriptAccess="sameDomain"></embed>
      </object>
      <div class="lastfmFoot" style="background-image:url(http://panther1.last.fm/widgets/images/footer/background/'.$args['color'].'.gif);width:'.$width.'px;">
        <a class="popup" title="Load this '.$args['type'].' in a pop up" href="'.$popupurl.'" target="_blank" onclick="window.open(this.href + &squo;&amp;shrink=0&squo;, &squo;lastfm_popup&squo;, &squo;height='.$popupheight.', width='.$width.', location=no, toolbar=no, menubar=no, directories=no, personalbar=no, status=no, resizable=yes, scrollbars=no&squo;); return false;" style="background-image:url(http://panther1.last.fm/widgets/images/footer/'.$args['color'].'.gif);"></a>';
  if ($args['size'] != "mini") $output .='
        <a class="view" title="View '.$args['user'].'&squo;s profile" href="http://www.last.fm/user/'.$args['user'].'/" target="_blank" style="background-image:url(http://panther1.last.fm/widgets/images/footer/'.$args['color'].'.gif);"></a>';
  $output .='        
        <a class="config" title="Get your own" href="'.$getyourownurl.'" target="_blank" style="background-image:url(http://panther1.last.fm/widgets/images/footer/'.$args['color'].'.gif);"></a>
      </div>
    </div>';

  return Keep($output);
}