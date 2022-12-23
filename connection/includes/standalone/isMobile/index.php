<?php
if (isset($_SESSION['screen_width']) and isset($_SESSION['screen_height'])) {
	// echo 'User resolution: ' . $_SESSION['screen_width'] . 'x' . $_SESSION['screen_height'];
} else if (isset($_REQUEST['width']) and isset($_REQUEST['height'])) {
	$_SESSION['screen_width'] = $_REQUEST['width'];
	$_SESSION['screen_height'] = $_REQUEST['height'];
	header('Location: ' . $_SERVER['PHP_SELF']);
} else {
	echo '<script type="text/javascript">window.location = "' . $_SERVER['PHP_SELF'] . '?width="+screen.width+"&height="+screen.height;</script>';
}

if (isset($_SESSION['window_width']) and isset($_SESSION['window_height'])) {
	// echo 'User resolution: ' . $_SESSION['window_width'] . 'x' . $_SESSION['window_height'];
} else if (isset($_REQUEST['width']) and isset($_REQUEST['height'])) {
	$_SESSION['window_width'] = $_REQUEST['width'];
	$_SESSION['window_height'] = $_REQUEST['height'];
	header('Location: ' . $_SERVER['PHP_SELF']);
} else {
	echo '<script type="text/javascript">window.location = "' . $_SERVER['PHP_SELF'] . '?width="+window.innerWidth+"&height="+window.innerHeight;</script>';
}

function isMobileDevice()
{
	$aMobileUA = array(
		'/iphone/i' => 'iPhone',
		'/ipod/i' => 'iPod',
		'/ipad/i' => 'iPad',
		'/android/i' => 'Android',
		'/blackberry/i' => 'BlackBerry',
		'/webos/i' => 'Mobile'
	);

	//Return true if Mobile User Agent is detected
	foreach ($aMobileUA as $sMobileKey => $sMobileOS) {
		if (preg_match($sMobileKey, $_SERVER['HTTP_USER_AGENT'])) {
			return true;
		}
	}
	//Otherwise return false..  
	return false;
}

function isDesktopDevice()
{
	$aDesktopeUA = array(
		'/windows nt 6.2/i'     =>  'Windows 8',
		'/windows nt 6.1/i'     =>  'Windows 7',
		'/windows nt 6.0/i'     =>  'Windows Vista',
		'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
		'/windows nt 5.1/i'     =>  'Windows XP',
		'/windows xp/i'         =>  'Windows XP',
		'/windows nt 5.0/i'     =>  'Windows 2000',
		'/windows me/i'         =>  'Windows ME',
		'/win98/i'              =>  'Windows 98',
		'/win95/i'              =>  'Windows 95',
		'/win16/i'              =>  'Windows 3.11',
		'/windows|win32/i'      =>  'Windows',
		'/macintosh|mac os x/i' =>  'Mac OS X',
		'/mac_powerpc/i'        =>  'Mac OS 9',
		'/linux/i'              =>  'Linux',
		'/ubuntu/i'             =>  'Ubuntu'
	);

	//Return true if Desktope User Agent is detected
	foreach ($aDesktopeUA as $sDesktopeKey => $sDesktopeOS) {
		if (preg_match($sDesktopeKey, $_SERVER['HTTP_USER_AGENT']) && intval($_SESSION['screen_width']) > 450 && intval($_SESSION['window_width']) > 450) {
			return true;
		}
	}
	//Otherwise return false..  
	return false;
}
