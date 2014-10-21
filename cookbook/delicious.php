<?php if (!defined('PmWiki')) exit();
/*  Copyright 2006 Jon Haupt (jhaupt@gmail.com)
    This file is delicious.php; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  

    This script enables you to create interaction between PmWiki and del.icio.us. 

    To use this script, copy it into the cookbook/ directory
    and add the following line to config.php (or a per-page/per-group
    customization file). 
    include_once("$FarmD/cookbook/delicious.php");

    The script will generate a linkroll or tagroll.
    The simplest linkroll can be created by using the markup (:linkroll user=foo:)
    where foo is the desired delicious username, creating a roll of 10 recent links.
    Similarly (:tagroll user=foo:) creates a complete tag cloud of user foo.

    The script can also add a del.icio.us tagometer, by using the markup (:deliciousbadge:).

    The recipe is derived from the del.icio.us help pages, specifically:
    http://del.icio.us/help/tagrolls
    http://del.icio.us/help/linkrolls
    http://del.icio.us/help/tagometer

    The following is a list of arguments for tagrolls and linkrolls:

    For (:linkroll:)
    number: the number of links you want to display
    sort: options are recent (default) or alpha
    tags: Limit the linkroll by tag(s).  Multiple tags use the syntax 'tag+tag'
    user: del.icio.us username (required)
    icon: large, small, rss, or none
    label: text to display as a header
    showtags: true or false - include the tags used with the links

    For (:tagroll:)
    number: number of tags to display
    sizerange: in the syntax '8-25', the lowest and highest text size used for display
    label: text to display as a header
    sort: options are freq (frequency) or alpha
    flow: cloud or list
    color1/color2: color1 is the least frequent tag, color2 is the most frequent (colors 
      are used on a gradient between color1 and color2).  must use html colors, 
      no # (aka like this: '000099')
    counts: true or false - include the frequency of each tag
    user: del.icio.us username (required)

    Version Log:

    March 2006 1.0 Initial Release
    January 2007 1.1 Added tagometer badge

*/

Markup("linkroll", ">block", '/\\(:linkroll\\s(.*?):\\)/ei', "DeliciousLinks('$1')");
Markup("tagroll", ">block", '/\\(:tagroll\\s(.*?):\\)/ei', "DeliciousTags('$1')");

function DeliciousLinks($p) {
  // Defaults
  $defaults = array (
    'number' => '10',
    'sort' => 'recent',
    'tags' => '',
    'user' => '',
    'icon' => 'large',
    'label' => 'del.icio.us links',
    'showtags' => 'false');
  $opt = array_merge($defaults, ParseArgs($p));

  // Return nothing if no username listed
  if ($opt['user'] == '') { return ''; }

  // Begin output
  $output = "<div id='linkroll'>
    <script type='text/javascript' src='http://del.icio.us/feeds/js/".$opt['user'];
  if ($opt['tags'] != '') $output=$output."/".$opt['tags'];
  $output=$output."?";
  if ($opt['showtags'] == 'true') $output=$output."tags;";
  $output=$output."count=".$opt['number'].";title=".$opt['label'].";";
  if ($opt['icon'] != 'none') {
    $output=$output."icon";
    if ($opt['icon'] == 'small') $output=$output."=s";
      else if ($opt['icon'] == 'rss') $output=$output."=rss";
    $output=$output.";"; }
  $output=$output."sort=".$opt['sort']."'></script>
    <noscript><h2><a href='http://del.icio.us/".$opt['user'];
  if ($opt['tags'] <> '') $output=$output."/".$opt['tags'];
  $output=$output."'>".$opt['label']."</a></h2></noscript>
    </div>";
  // Finish output and return it
  return $output;
};

function DeliciousTags($p) {
  // Defaults
  $defaults = array (
    'number' => '',
    'sort' => 'alpha',
    'sizerange' => '12-35',
    'user' => '',
    'flow' => 'cloud',
    'label' => 'del.icio.us tags',
    'color1' => '87ceeb',
    'color2' => '0000ff',
    'counts' => 'false');
  $opt = array_merge($defaults, ParseArgs($p));

  // Return nothing if no username listed
  if ($opt['user'] == '') { return ''; }

  // Begin output
  $output = "<div id='tagroll'>
    <script type='text/javascript' src='http://del.icio.us/feeds/js/tags/".$opt['user']."?sort=".$opt['sort'].";icon;";
    if ($opt['number'] == '') $output=$output;
      else $output=$output."count=".$opt['number'].";";
    if ($opt['counts'] == 'true') $output=$output."totals;";
    if ($opt['flow'] == 'list') $output=$output."flow=list;";
    $output=$output."size=".$opt['sizerange'].";color=".$opt['color1']."-".$opt['color2'].";title=".$opt['label']."'></script>
    <noscript><h2><a href='http://del.icio.us/".$opt['user']."'>".$opt['label']."</a></h2></noscript>
    </div>";
  // Finish output and return it
  return $output;
}

SDV($DeliciousBadgeFmt, "<script src='http://images.del.icio.us/static/js/blogbadge.js'></script>");
Markup("deliciousbadge", ">block", '/\\(:deliciousbadge:\\)/', $DeliciousBadgeFmt);