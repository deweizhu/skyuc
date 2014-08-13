<?php
/**
 * SKYUC! Google & Baidu sitemap 类
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */

if (! defined ( 'SKYUC_AREA' )) {
	echo 'SKYUC_AREA must be defined to continue';
	exit ();
}

class google_sitemap {
	public $header = "<\x3Fxml version=\"1.0\" encoding=\"UTF-8\"\x3F>\n\t<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
	public $charset = "UTF-8";
	public $footer = "\t</urlset>\n";
	public $items = array ();

	/**
	 * 增加一个新的子项
	 *@access   public
	 *@param    google_sitemap  item    $new_item
	 */
	public function add_item($new_item) {
		$this->items [] = $new_item;
	}

	/**
	 * 生成XML文档
	 *@access    public
	 *@param     string  $file_name  如果提供了文件名则生成文件，否则返回字符串.
	 *@return [void|string]
	 */
	public function build($file_name = null) {
		$map = $this->header . "\n";

		foreach ( $this->items as $item ) {
			$item->loc = htmlentities ( $item->loc, ENT_QUOTES );
			$map .= "\t\t<url>\n\t\t\t<loc>$item->loc</loc>\n";

			// lastmod
			if (! empty ( $item->lastmod ))
				$map .= "\t\t\t<lastmod>$item->lastmod</lastmod>\n";

		// changefreq
			if (! empty ( $item->changefreq ))
				$map .= "\t\t\t<changefreq>$item->changefreq</changefreq>\n";

		// priority
			if (! empty ( $item->priority ))
				$map .= "\t\t\t<priority>$item->priority</priority>\n";

			$map .= "\t\t</url>\n\n";
		}

		$map .= $this->footer . "\n";

		if (! is_null ( $file_name )) {
			return file_put_contents ( $file_name, $map );
		} else {
			return $map;
		}
	}

}

class google_sitemap_item {
	/**
	 *@access   public
	 *@param    string  $loc        位置
	 *@param    string  $lastmod    日期格式 YYYY-MM-DD
	 *@param    string  $changefreq 更新频率的单位 (always, hourly, daily, weekly, monthly, yearly, never)
	 *@param    string  $priority   更新频率 0-1
	 */
	public function __construct($loc, $lastmod = '', $changefreq = '', $priority = '') {
		$this->loc = $loc;
		$this->lastmod = $lastmod;
		$this->changefreq = $changefreq;
		$this->priority = $priority;
	}
}

class baidu_sitemap {
	public $header = "<\x3Fxml version=\"1.0\" encoding=\"GB18030\"\x3F>\n\t<document>\n";
	public $charset = "GB18030";
	public $footer = "\t</document>\n";
	public $items = array ();

	/**
	 * 增加站点网址，管理员Email，更新周期
	 *@access   public
	 */
	public function add_header($updatePeri) {
		global $skyuc;
		$site_url = $skyuc->options ['site_url'];
		$service_email = ! empty ( $skyuc->options ['service_email'] ) ? $skyuc->options ['service_email'] : $skyuc->options ['msn'];

		$map = "\t<webSite>$site_url</webSite>\n";
		$map .= "\t<webMaster>$service_email</webMaster>\n";
		$map .= "\t<updatePeri>$updatePeri</updatePeri>";
		$this->header .= $map;
	}

	/**
	 * 增加一个新的子项
	 *@access   public
	 *@param    baidu_sitemap  item    $new_item
	 */
	public function add_item($new_item) {
		$this->items [] = $new_item;
	}

	/**
	 * 生成XML文档
	 *@access    public
	 *@param     string  $file_name  如果提供了文件名则生成文件，否则返回字符串.
	 *@return [void|string]
	 */
	public function build($file_name = null) {
		$map = $this->header . "\n";

		foreach ( $this->items as $item ) {
			$item->playLink = htmlentities ( $item->playLink, ENT_QUOTES );
			$map .= "\t\t<item>\n\t\t\t<op>add</op>\n";
			// playLink
			$map .= "\t\t\t<playLink>$item->playLink</playLink>\n";

			// pubDate
			if (! empty ( $item->pubDate ))
				$map .= "\t\t\t<pubDate>$item->pubDate</pubDate>\n";

		// duration
			if (! empty ( $item->duration ))
				$map .= "\t\t\t<duration>$item->duration</duration>\n";

		// title
			if (! empty ( $item->title ))
				$map .= "\t\t\t<title><![CDATA[	$item->title   ]]></title>\n";

		// imageLink
			if (! empty ( $item->imageLink ))
				$map .= "\t\t\t<imageLink>$item->imageLink</imageLink>\n";

		// tag
			if (! empty ( $item->tag ))
				$map .= "\t\t\t<tag>$item->tag</tag>\n";

		// comment
			if (! empty ( $item->comment ))
				$map .= "\t\t\t<comment><![CDATA[	$item->comment   ]]></comment>\n";

			$map .= "\t\t</item>\n\n";
		}

		$map .= $this->footer . "\n";

		if (! is_null ( $file_name )) {
			return file_put_contents ( $file_name, $map );
		} else {
			return $map;
		}
	}

}

class baidu_sitemap_item {
	/**
	 *@access   public
	 *@param    string  $playLink        位置
	 *@param    string  $pubDate    日期格式 YYYY-MM-DD
	 *@param    string  $title 影片名称
	 *@param    string  $imageLink   图片地址
	 *@param		string	$comment		简介
	 *@param		string	$duration		片长，以秒为单位
	 */
	public function __construct($playLink, $pubDate = '', $title = '', $imageLink = '', $comment = '', $duration = '') {
		$this->playLink = $playLink;
		$this->pubDate = $pubDate;
		$this->title = $title;
		$this->imageLink = $imageLink;
		$this->comment = $comment;
		$this->duration = $duration;
	}
}
?>