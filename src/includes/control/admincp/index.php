<?php
// #######################################################################
// ######################## index.php 私有函数      ##########################
// #######################################################################


/* 检查新版本 */
function get_new_info() {

	$nurl = $_SERVER ["HTTP_HOST"];
	if (preg_match ( "/[a-z\-]{1,}\.[a-z]{2,}/i", $nurl )) {
		$nurl = urlencode ( $nurl );
	} else {
		$nurl = "test";
	}
	$gs = '<script type="text/javascript" src="http://api.skyuc.com/pack/version.php?version=' . VERSION . '&formurl=' . $nurl . '&install_date=' . $GLOBALS['skyuc']->options ['install_date'] . '"></script>';
	return $gs;
}
function cachemgr($c = '', $f = '') {

	$pagedata = array ();
	$pagedata ['curBytes'] = $c;
	$pagedata ['totalBytes'] = ($f + $c);
	$pagedata ['cache_status'] = $GLOBALS['skyuc']->secache->status ( $pagedata ['curBytes'], $pagedata ['totalBytes'] );
	$pagedata ['cache'] = $GLOBALS['skyuc']->secache->name;
	$pagedata ['cache_desc'] = '';

	$pagedata ['random'] = TIMENOW;
	$pagedata ['freeBytes'] = $pagedata ['totalBytes'] - $pagedata ['curBytes'];
	$p = round ( 100 * $pagedata ['curBytes'] / $pagedata ['totalBytes'] );
	$pagedata ['status'] = $p . ',' . (100 - $p);

	return $pagedata;
}

?>