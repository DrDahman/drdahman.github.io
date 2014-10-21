<?php if (!defined('PmWiki')) exit();
/*
 * SourceBlock - Yet another source code syntax highlighter for PmWiki 2.0
 * Copyright 2005-2007 by D.Faure (dfaure@cpan.org)
 * Geshi module written by and (C) Nigel McNie (oracle.shinoda@gmail.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * See http://www.pmwiki.org/wiki/Cookbook/SourceBlock for info.
 */
$RecipeInfo['SourceBlock']['Version'] = '20070507';

SDV($SourceBlockAction, 'sourceblock');
if ($action == $SourceBlockAction)
  $HandleActions[$SourceBlockAction] = 'HandleSourceBlock';

Markup('sourceblock', '>markupend',
  "/\\(:source(\\s+.*?)?\\s*:\\)[^\\S\n]*\\[([=@])(.*?)\\2\\]/sei",
  "SourceBlock(\$pagename, PSS('$1'), PSS('$3'))");
Markup('sourceblockend', '>sourceblock',
  "/\\(:source(\\s+.*?)?\\s*:\\)[^\\S\n]*\n(.*?)\\(:sourcee?nd:\\)/sei",
  "SourceBlock(\$pagename, PSS('$1'), PSS('$2'))");

Markup('codeblock', '>markupend',
  "/\\(:code(\\s+.*?)?\\s*:\\)[^\\S\n]*\\[([=@])(.*?)\\2\\]/sei",
  "CodeBlock(\$pagename, PSS('$1'), PSS('$3'))");
Markup('codeblockend', '>codeblock',
  "/\\(:code(\\s+.*?)?\\s*:\\)[^\\S\n]*\n(.*?)\\(:codee?nd:\\)/sei",
  "CodeBlock(\$pagename, PSS('$1'), PSS('$2'))");

function HandleSourceBlock($pagename) {
  global $HandleBrowseFmt;
  $HandleBrowseFmt = '';
  HandleBrowse($pagename);
  return;
}

function ManageSourceBlockHandling($pagename, &$opt, &$block) {
  global $PCache, $action, $SourceBlockAction, $SourceBlockLinkUrl,
         $SourceBlockLinkText, $EnableIEForcedAttachment;
  SDV($PCache[$pagename]['sourceblock'], 0);
  $ref = ++$PCache[$pagename]['sourceblock'];

  if ($action == $SourceBlockAction && $ref == $_REQUEST['ref']) {
    # undo PmWiki's htmlspecialchars conversion
    $block = str_replace(array('<:vspace>', '&lt;', '&gt;', '&amp;'),
                         array('', '<', '>', '&'), $block);
    $filename = isset($opt['filename']) ?
                $opt['filename'] : "sourceblock_{$ref}.txt";
    $type = 'text/plain';
    $disp = @in_array('attach', $opt['-']) ? 'attachment' : 'inline';
    if(IsEnabled($EnableIEForcedAttachment, 1) &&
       strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
      $type = 'application/octet-stream';
      $disp = 'attachment';
    }
    header("Cache-Control: cache, must-revalidate");
    header("Expires: Tue, 01 Jan 2002 00:00:00 GMT");
    header("Content-type: $type");
    header("Content-disposition: $disp; filename=$filename");
    header('Content-Length: ' . strlen($block));
    echo $block;
    exit(0);
  }
  SDV($SourceBlockLinkUrl, "<a href='\$LinkUrl' type='text/plain'>\$LinkText</a>");
  SDV($SourceBlockLinkText, "[$[Get Code]]");
  return MakeLink($pagename,
                  "{$pagename}?action={$SourceBlockAction}&ref={$ref}",
                  $SourceBlockLinkText, NULL, $SourceBlockLinkUrl);
}

function CodeBlock($pagename, $args, $block) {
  global $CodeBlockFmt, $HTMLStylesFmt;
  SDV($CodeBlockFmt, "
<div class='codeblock'>
  <div class='codeblocktext'><pre>\$txt</pre></div>
  <div class='codeblocklink'>\$url</div>
</div>
");
  SDV($HTMLStylesFmt['codeblock'], "
.codeblocklink {
  text-align: right;
  font-size: smaller;
}
.codeblocktext {
  text-align: left;
  padding: 0.5em;
  border: 1px solid #808080;
  background-color: #f1f0ed;
}
.codeblocktext pre {
  font-family: monospace;
  font-size: small;
  line-height: 1;
}
");
  $opt = ParseArgs($args);
  # undo PmWiki's htmlspecialchars conversion
  $block = str_replace(array('<:vspace>', '&lt;', '&gt;', '&amp;'),
                       array('', '<', '>', '&'), $block);  
  return Keep(str_replace(
    array('$url', '$txt'),
    array(ManageSourceBlockHandling($pagename, $opt, $block),
          htmlspecialchars(isset($opt['wrap']) ?
                           wordwrap($block, $opt['wrap']) : $block)),
    $CodeBlockFmt));
}

function SourceBlock($pagename, $args, $block) {
  global $SourceBlockParams, $GESHI_PATH, $HTMLStylesFmt, $GeshiStyles,
         $GeshiConfig, $SourceBlockFmt;
  SDVA($SourceBlockParams, array('header' => '', 'footer' => ''));
  $opt = array_merge((array)$SourceBlockParams, ParseArgs($args));
  if(!@in_array('trim', $opt['-'])) $block = trim($block);
  if($opt['lang']) {
    SDV($GESHI_PATH, dirname(__FILE__) . "/geshi");
    include_once($GESHI_PATH . "/geshi.php");
    # undo PmWiki's htmlspecialchars conversion
    $block = str_replace(array('<:vspace>', '&lt;', '&gt;', '&amp;'),
                          array('', '<', '>', '&'), $block);
    $geshi =& new GeSHi($block, $opt['lang'], $GESHI_PATH . "/geshi");
    $geshi->enable_classes();
    SDVA($GeshiStyles, array(
    'code'  => 'font-family: monospace; font-weight: normal;',
    'line1' => 'font-family: monospace; color: black; font-weight: normal;',
    'line2' => 'font-weight: bold;',
    ));
    $geshi->set_code_style($GeshiStyles['code']);
    $geshi->set_line_style($GeshiStyles['line1'], $GeshiStyles['line2']);
    $geshi->set_header_type(GESHI_HEADER_DIV);
    if($opt['tabwidth']) $geshi->set_tab_width($opt['tabwidth']);
    if (@in_array('linenum', $opt['']))
      $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
    if($opt['linenum']) {
      $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
      $geshi->start_line_numbers_at($opt['linenum']);
    }
    if($opt['highlight']) {
      $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);
      $geshi->highlight_lines_extra(preg_split("/[\s,]+/", $opt['highlight']));
    }
    if(!@in_array('function_url', $opt[''])) $block = trim($block);
    $geshi->set_header_content($opt['header']);
    $geshi->set_footer_content($opt['footer']);
    if($opt['id']) $geshi->set_overall_id($opt['id']);
    if(@in_array('link', $opt['-'])) $geshi->enable_keyword_links(false);
    if($GeshiConfig) $GeshiConfig($pagename, $geshi, $args);
    $HTMLStylesFmt['geshi_'.$opt['lang']] = $geshi->get_stylesheet();
    $txt = $geshi->parse_code();
    if($geshi->error()) $txt = $geshi->error();
  } else {
    $lines = explode("\n", $block);
    $count = count($lines);
    if(@in_array('linenum', $opt[''])) $linenum = "<ol>";
    elseif($opt['linenum']) $linenum = "<ol start=" . $opt['linenum'] . ">";
    for($i = 0; $i < $count; $i++)
      $txt .= ($linenum ? "<li>" : "") . $lines[$i] . ($linenum ? "</li>" : "<br/>");
    $txt = "<div>{$linenum}{$txt}" . ($linenum ? "</ol>" : "") . "</div>";
  }
  SDV($SourceBlockFmt, "
<div class='sourceblock'>
  <div class='sourceblocktext'>\$txt</div>
  <div class='sourceblocklink'>\$url</div>
</div>
");
  SDV($HTMLStylesFmt['sourceblock'], "
.sourceblocklink {
  text-align: right;
  font-size: smaller;
}
.sourceblocktext {
  padding: 0.5em;
  border: 1px solid #808080;
  background-color: #f1f0ed;
}
.sourceblocktext div {
  font-family: monospace;
  font-size: small;
  line-height: 1;
  height: 1%;
}
.sourceblocktext div.head,
.sourceblocktext div.foot {
  font: italic medium serif;
  padding: 0.5em;
}
");
  return Keep(str_replace(
    array('$url', '$txt'),
    array(ManageSourceBlockHandling($pagename, $opt, $block), $txt),
    $SourceBlockFmt));
}

if(!$RecipeInfo['LinkedResourceExtras']['Version']) return;

Markup('sourceblockfile', '<sourceblock',
  "/\\(:source\\s+([\\(\\)\\w]+:\\S+)(.*?):\\)/ie",
  "SourceCodeBlockFile('SourceBlock', \$pagename, '$1', PSS('$2'))");

Markup('codeblockfile', '<codeblock',
  "/\\(:code\\s+([\\(\\)\\w]+:\\S+)(.*?):\\)/ie",
  "SourceCodeBlockFile('CodeBlock', \$pagename, '$1', PSS('$2'))");

function SourceCodeBlockFile($func, $pagename, $tgt, $args) {
  if(!ResolveLinkResource($pagename, $tgt, $url, $txt, $upname, $filepath, $size, $mime))
    return isset($filepath) ? $url : '';
  $fp = @fopen($filepath, "r");
  if($fp) {
    while($l = fgets($fp, 4096))
      $block[] = $l;
    fclose($fp);
  }
  $block = implode('', $block);
  return $func($pagename, $args, $block);
}

