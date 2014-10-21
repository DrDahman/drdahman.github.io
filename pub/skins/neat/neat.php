<?php if (!defined('PmWiki')) exit();

global $WikiLibDirs,$WikiSubTitle,$Conditions,$action,$FmtPV,$HTMLHeaderFmt,$HTMLStylesFmt,$GlobalRssFeed;

#Include neat.d folder as one of the $WikiLibDirs
SDV($IncludeNeatD, '1');

#Global rss feed - rss feed from AllRecentChanges, inside html <head> tag
SDV($GlobalRssFeed, '1');

#High light to actions been performed
SDV ($HighLightActions, '0');

#SubTitle 
SDV( $WikiSubTitle, "The wiki way");

#---------------------------

#Gloablrss feed
if ($GlobalRssFeed == '1')
    $HTMLHeaderFmt[globalrssfeed] = "<link rel='alternate' title='$WikiTitle RSS' href='\$ScriptUrl/\$SiteGroup/AllRecentChanges&action=rss' type='text/xml' />";

#Condition to check if commentBox is installed and running
$Conditions['commentbox'] = defined('COMMENTBOXSTYLED_VERSION');

#LastMod
$FmtPV['$NeatLastMod'] = 'strftime("%b %d, %Y", $page["time"])';

#High Light Actions
if ($HighLightActions == '1'){

  if ($action == "browse")
     $HTMLStylesFmt[SkinActionCSS] = "#wiki-action li.browse a.wikilink {background-color:#fff;border:1px solid #ccc;padding:2px 5px 2px 5px}";
  if ($action == "edit"  )
     $HTMLStylesFmt[SkinActionCSS] = "#wiki-action li.edit a.wikilink {background-color:#fff;border:1px solid #ccc;padding:2px 5px 2px 5px}";
  if ($action == "diff"  ) 
     $HTMLStylesFmt[SkinActionCSS] = "#wiki-action li.diff a.wikilink {background-color:#fff;border:1px solid #ccc;padding:2px 5px 2px 5px}";
  if ($action == "upload")
     $HTMLStylesFmt[SkinActionCSS] = "#wiki-action li.upload a.wikilink {background-color:#fff;border:1px solid #ccc;padding:2px 5px 2px 5px}";

}

#Include neat.d as a wikilib folder
if ($IncludeNeatD == '1'){
	$PageStorePath = dirname(__FILE__)."/neat.d/\$FullName";
	$where = count($WikiLibDirs);
	if ($where>1) $where--;
	array_splice($WikiLibDirs, $where, 0, array(new PageStore($PageStorePath)));
}
