<?php if (!defined('PmWiki')) exit();
/*******************************************************************
  file onlinestatus.php for PmWiki 2.

  Copyright 2006 Jon Haupt.
  This file is distributed under the terms of the GNU General Public
  License as published by the Free Software Foundation; either
  version 2 of the License, or (at your option) any later version.

  This recipe allows you to display whether or not you are available
  on various instant messaging networks.

  There are two arguments, svc and id.  For each image, you need to
  specify which IM service you want to use and what your ID is,
  like this:

  (:onlinestatus svc=yim id=dairyman88:)

  This would show dairyman88's status in Yahoo Instant Messenger.
  The services available are:

  yim - Yahoo Instant Messenger
  aol -  AOL Instant Messenger
  skype - Skype
  gtalk - Google Talk (not yet implemented)
  meebo - Meebo Me! widgets.  In order to use them, specify id and 
          size (s,m,l) or set w and h
  wablet (not yet implemented)

********************************************************************/
# Version date
$RecipeInfo['OnlineStatus']['Version'] = '2007-02-07';

#Define Online Presence image markup
Markup('onlinestatus', 'inline',  '/\\(:onlinestatus (.*?):\\)/e', 'OnlineStatus("$1")');

function OnlineStatus($opts) {

  $args = ParseArgs($opts);

  switch ($args['svc']) {

    case "yim":
      # Yahoo Instant Messenger
      $output = "
      <a href='http://edit.yahoo.com/config/send_webmesg?.target=".$args['id']."&amp;.src=pg'>
      <img border=0 src='http://opi.yahoo.com/online?u=".$args['id']."&amp;m=g&amp;t=2&amp;l=us' /></a>";
      break;
    case "aol":
      $output = "
      <img src='http://big.oscar.aol.com/".$args['id']."?on_url=http://www.aol.com:80/aim/gr/online.gif&amp;off_url=http://www.aol.com:80/aim/gr/offline.gif' />";
      break;
    case "skype":
      $output = "
      <a href='skype:".$args['id']."?call'><img src='http://mystatus.skype.com/bigclassic/".$args['id']."' /></a>";
      break;
          case "myskype":
      $output = "
      <a href='skype:".$args['id']."?call'><img src='http://download.skype.com/share/skypebuttons/buttons/call_blue_transparent_34x34.png' /></a>";
      break;
    case "gtalk":
    #in development
      $output = "
      <img src='http://www.jonburrows.co.uk/gtalkstatus/".$args['img']."/encstring.jpg' />";
      break;
    case "meebo":
      switch ($args['size']) {
        case "s":
          $width = 160;
          $height = 250;
          break;
        case "m":
          $width = 190;
          $height = 275;
          break;
        case "l":
          $width = 250;
          $height = 300;
          break;
      }
      if ($args['w'] != null) $width = $args['w'];
      if ($args['h'] != null) $height = $args['h'];
      $output = "
       <object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab' width='".$width."' height='".$height."'>
         <param name='movie' value='http://widget.meebo.com/mm.swf?".$args['id']."' />
         <!--[if !IE]> <-->
           <object data='http://widget.meebo.com/mm.swf?".$args['id']."' type='application/x-shockwave-flash' width='".$width."' height='".$height."'>
             <param name='pluginurl' value='http://www.macromedia.com/go/getflashplayer' />
             <img src='noflash.gif' alt='Flash required ' />
           </object>
         <!--> <![endif]-->
       </object>";
      break;
    case "wablet":
      break;
  }

  return Keep($output);
}