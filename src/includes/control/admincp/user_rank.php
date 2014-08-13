<?php
// #######################################################################
// ######################## user_rank.php 私有函数      ##################
// #######################################################################


/**
 * 会员等级列表
 *
 * @param		int		$rank_id	等级ＩＤ
 * @access  public
 * @return  array
 */
function get_rank_list($rank_id = NULL) {
	
	if ($rank_id > 0) {
		$sql = 'SELECT * FROM ' . TABLE_PREFIX . 'user_rank' . " WHERE rank_id='" . $rank_id . "'";
		$res = $GLOBALS ['db']->query_read ( $sql );
		$arr = array ();
		while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
			$row ['allow_cate'] = explode ( ',', $row ['allow_cate'] );
			$arr [] = $row;
		}
	
	} else {
		$res = $GLOBALS ['db']->query_read ( 'SELECT * FROM ' . TABLE_PREFIX . 'user_rank' );
		$arr = array ();
		while ( $row = $GLOBALS ['db']->fetch_array ( $res ) ) {
			$row ['money'] = price_format ( $row ['money'] );
			$row ['count'] = $row ['rank_type'] == 1 ? $row ['count'] . $GLOBALS ['_LANG'] ['day'] : $row ['count'] . $GLOBALS ['_LANG'] ['point'];
			$arr [] = $row;
		
		}
	
	}
	return $arr;

}

?>