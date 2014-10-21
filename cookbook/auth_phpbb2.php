<?php if (!defined('PmWiki')) exit();

##
## SMC: New alternative Wiki Auth
## SMC: Check if we are already authenticated via phpBB2 forum
## Version: 1.0
##

// Set the default phpBB2 cookie name.  _data is appended to this name later in this script.
SDV($phpBB2cookie,'phpBB2_cookie_name');

// Override the default AuthFunction to phpAuth
$AuthFunction = 'PhpBB2Auth';

  $m_phpBB2_auth = false;
  $m_username = 'Anonymous';  // forum defaults to this also

  $m_data_cookie = $phpBB2cookie . '_data';  // append _data to cookie name as this is the element we need
  #print "Session Cookie: " . $m_data_cookie . "<br>";

	if (isset($_COOKIE[$m_data_cookie]))
	{
        $sessiondata = isset($_COOKIE[$m_data_cookie]) ? unserialize(stripslashes($_COOKIE[$m_data_cookie])) : array();
        if (strcasecmp((string)$sessiondata['username'],"Anonymous") <> 0) {
            $m_phpBB2_auth = true;
            $Author = (string)$sessiondata['username'];
		    #print "Session Data: " . (string)$sessiondata['username'] . "<br>";
        }
	}

function PhpBB2Auth($pagename, $level, $authprompt=true, $since=0) {
  global $DefaultPasswords, $GroupAttributesFmt, $AllowPassword,
    $AuthCascade, $FmtV, $AuthPromptFmt, $PageStartFmt, $PageEndFmt,
    $AuthId, $AuthList, $NoHTMLCache, $m_phpBB2_auth;

  ## SMC: If we find an authenticated phpBB2 user continue with regular
  ## PmWiki auth check, if phpBB2 auth failed, stop check now
  static $acache;
  SDV($GroupAttributesFmt,'$Group/GroupAttributes');
  SDV($AllowPassword,'nopass');
  $page = ReadPage($pagename, $since);
  if (!$page) { return false; }
    if (!isset($acache))
    SessionAuth($pagename, (@$_POST['authpw'])
                           ? array('authpw' => array($_POST['authpw'] => 1))
                           : '');
  if (@$AuthId) {
    $AuthList["id:$AuthId"] = 1;
    $AuthList["id:-$AuthId"] = -1;
    $AuthList["id:*"] = 1;
  }
  $gn = FmtPageName($GroupAttributesFmt, $pagename);
  if (!isset($acache[$gn])) {
    $gp = ReadPage($gn, READPAGE_CURRENT);
    foreach($DefaultPasswords as $k => $v) {
      $x = array(2, array(), '');
      $acache['@site'][$k] = IsAuthorized($v, 'site', $x);
      $AuthList["@_site_$k"] = $acache['@site'][$k][0] ? 1 : 0;
      $acache[$gn][$k] = IsAuthorized(@$gp["passwd$k"], 'group',
                                      $acache['@site'][$k]);
    }
  }
  foreach($DefaultPasswords as $k => $v)
    list($page['=auth'][$k], $page['=passwd'][$k], $page['=pwsource'][$k]) =
      IsAuthorized(@$page["passwd$k"], 'page', $acache[$gn][$k]);
  foreach($AuthCascade as $k => $t) {
    if ($page['=auth'][$k]+0 == 2) {
      $page['=auth'][$k] = $page['=auth'][$t];
      if ($page['=passwd'][$k] = $page['=passwd'][$t])         # assign
        $page['=pwsource'][$k] = "cascade:$t";
    }
  }
  if (@$page['=auth']['admin'])
    foreach($page['=auth'] as $lv=>$a) @$page['=auth'][$lv] = 3;
  if (@$page['=passwd']['read']) $NoHTMLCache |= 2;

# SMC: only allow page reads unless phpBB2 authenticated
if ($m_phpBB2_auth == true) {
  if ($level=='ALWAYS' || @$page['=auth'][$level]) return $page;
}
if (strcasecmp ($level, "read") == 0) {
  if ($level=='ALWAYS' || @$page['=auth'][$level]) return $page; # SMC: orig line
}

  if (!$authprompt) return false;
  $GLOBALS['AuthNeeded'] = (@$_POST['authpw'])
    ? $page['=pwsource'][$level] . ' ' . $level : '';
  PCache($pagename, $page);

  $postvars = '';
  foreach($_POST as $k=>$v) {
    if ($k == 'authpw' || $k == 'authid') continue;
    $v = str_replace('$', '&#036;',
             htmlspecialchars(stripmagic($v), ENT_COMPAT));
    $postvars .= "<input type='hidden' name='$k' value=\"$v\" />\n";
  }
  $FmtV['$PostVars'] = $postvars;
  SDV($AuthPromptFmt,array(&$PageStartFmt,
    "<p><b>$[Password required]</b></p>
      <form name='authform' action='{$_SERVER['REQUEST_URI']}' method='post'>
        $[Password]: <input tabindex='1' type='password' name='authpw'
          value='' />
        <input type='submit' value='OK' />\$PostVars</form>
        <script language='javascript' type='text/javascript'><!--
          document.authform.authpw.focus() //--></script>", &$PageEndFmt));
  PrintFmt($pagename,$AuthPromptFmt);
  exit;
}
