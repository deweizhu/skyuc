<?php
/**
 * SKYUC! 采集程序类
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
 */
class Collection
{
    // 采集节点的来源列表处理信息
    public $lists = array();
    // 采集节点的基本配置信息
    private $noteInfos = array('notename', 'language', 'cat_id',
                               'server_id', 'player', 'macthtype', 'cosort', 'varurl', 'varstart', 'varend',
                               'addv', 'pagerepad', 'listurl', 'linkarea', 'need', 'cannot', 'savepic',
                               'title', 'actor', 'director', 'image', 'pubdate', 'status', 'area', 'lang',
                               'detail', 'url', 'title_trim', 'actor_trim', 'director_trim', 'image_trim',
                               'pubdate_trim', 'status_trim', 'area_trim', 'lang_trim', 'detail_trim',
                               'url_trim', 'runphp', 'runphp_code');
    // 节点信息数组
    public $codeArray = array();
    /**
     * 我们需要的任何选项的注册表对象
     *
     * @private    Registry
     */
    private $registry = null;
    private $tmpHtml = '';

    /**
     * 构造函数
     *
     * @param    Registry    SKYUC 注册表对象
     */
    function __construct(&$registry)
    {
        if (is_object($registry)) {
            $this->registry = & $registry;
        } else {
            trigger_error('Registry object is not an object', E_USER_ERROR);
        }
    }

    /**
     * 获取节点信息
     *
     * @param    int    $nid    节点ID
     * @param int    $type 返回类型,1等于数组
     * @return  array
     */
    public function get_col_info($nid, $type = 0)
    {
        if ($nid == 0) {
            return false;
        }
        $sql = 'SELECT * FROM ' . TABLE_PREFIX . 'co_note' . ' WHERE nid=' . $nid;
        $colinfo = $this->registry->db->query_first($sql);
        if ($type == 1) {
            return $colinfo;
        }
        $col = array();
        $col = $this->parse_col_code($colinfo['noteinfo']);
        $listurl = preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", "\r\n",
                                trim($col['listurl'])) . "\r\n";
        if (!empty($col['varurl']) && $col['varend'] > $col['varstart']) {
            $addv = $col['addv'];
            if ($addv <= 0) {
                $addv = 1;
            }
            //采集顺序：与目标站相反
            if ($col['cosort'] == 'desc') {
                for ($i = $col['varend']; $i >= $col['varstart']; $i -= $addv) {
                    $url = str_replace('[page]', $i, $col['varurl']);
                    $listurl .= $url . "\r\n";
                }
            } else {
                //采集顺序：与目标站一致
                for ($i = $col['varstart']; $i <= $col['varend']; $i += $addv) {
                    $url = str_replace('[page]', $i, $col['varurl']);
                    $listurl .= $url . "\r\n";
                }
            }
        }
        $col['listarray'] = explode("\r\n", trim($listurl)); //把ＵＲＬ数组加入节点信息中
        $col['nid'] = $nid; //把当前节点ＩＤ加入节点信息中
        return $col;
    }

    /**
     * 解析采集规则信息代码
     *
     * @param        string 专家代码
     * @access  public
     * @return  array
     */
    function parse_col_code($procode)
    {
        $sucodes = '';
        preg_match_all("/<suc:([^>]+)>([\s\S]*?)<\/suc>/", $procode, $matchs);
        for ($i = 0; $i < count($matchs[1]); $i++) {
            if (in_array($matchs[1][$i], $this->noteInfos)) {
                $sucodes[$matchs[1][$i]] = trim($matchs[2][$i]);
            } else {
                $sucodes[$matchs[1][$i]] = NUll; //未可识别的字段值设为NULL
            }
        }
        unset($matchs);
        foreach ($sucodes as $key => $value) {
            if ($value == NULL) {
                unset($sucodes[$key]); //删除值为空的元素
            } else {
            }
        }
        return $sucodes;
    }

    /**
     * 生成采集规则信息代码
     *
     * @param        array $codeArray 传递来的数组
     * @access  public
     * @return  stirng    返回规则代码，并处理引用传递的数组
     */
    function generate_col_code(&$codeArray)
    {
        if (!is_array($codeArray) || empty($codeArray)) {
            return false;
        }
        $strArray = array('notename', 'language', 'player', 'macthtype',
                          'cosort', 'varurl', 'pagerepad', 'listurl', 'linkarea', 'need', 'cannot',
                          'title', 'actor', 'director', 'image', 'pubdate', 'status', 'area',
                          'lang', 'detail', 'url', 'title_trim', 'actor_trim', 'director_trim',
                          'image_trim', 'pubdate_trim', 'status_trim', 'area_trim', 'lang_trim',
                          'detail_trim', 'url_trim', 'runphp', 'runphp_code');
        $itemconfig = '';
        foreach ($codeArray as $key => $value) {
            if (in_array($key,
                         array('cat_id', 'server_id', 'varstart', 'varend', 'addv',
                              'savepic'))
            ) {
                $codeArray[$key] = intval($value);
                $itemconfig .= '<suc:' . $key . '>' . intval($value) . '</suc>';
            } elseif (in_array($key, $strArray)) {
                $codeArray[$key] = trim($value);
                $itemconfig .= '<suc:' . $key . '>' . trim($value) . '</suc>';
            } else {
                unset($codeArray[$key]);
            }
        }
        return $itemconfig;
    }

    /**
     * 采集种子网址
     *
     * @param        int        $islisten 采集监控
     * @param        int        $glstart 列表起始页
     * @param        int        $pagesize 每页采集
     * @return  array
     */
    function get_source_url($islisten = 0, $glstart = 0, $pagesize = 10)
    {
        $col = & $this->codeArray;
        //在第一页中进行预处理
        //“下载种子网址的未下载内容”的模式不需要经过采集种子网址的步骤
        if ($glstart == 0) {
            if ($islisten == -1) {
                //重新采集所有内容模式
                $this->registry->db->query_write(
                    'DELETE FROM ' . TABLE_PREFIX . 'co_listen' . ' where nid=' .
                    $col['nid']);
                $this->registry->db->query_write(
                    'DELETE FROM ' . TABLE_PREFIX . 'co_html' . ' where nid=' .
                    $col['nid']);
            } else {
                //监听模式(保留未导出的内容、保留节点的历史网址记录)
                $this->registry->db->query_write(
                    'DELETE FROM ' . TABLE_PREFIX . 'co_html' . ' where nid=' .
                    $col['nid'] . ' And isexport=1');
            }
        }
        require (DIR . '/includes/class_html.php');
        $htmlparse = new htmlparse();
        $tmplink = array();
        $arrStart = 0;
        $moviePostion = 0;
        $endpos = $glstart + $pagesize;
        $totallen = count($col['listarray']);
        foreach ($col['listarray'] as $k => $cururl) {
            $moviePostion++;
            if ($moviePostion > $endpos) {
                break;
            }
            if ($moviePostion > $glstart) {
                $html = fetch_body_request($cururl, $col['language']);
                $html = $this->filterhtml($col['pagerepad'], $html); //过滤页面广告
                $sptag = '[!--me--]';
                if (trim($col['linkarea']) != '' &&
                    trim($col['linkarea']) != $sptag
                ) {
                    $areabody = $col['linkarea']; //此处必须这样，否则get_html_split会在下一次刷新页面前重复修改规则。
                    $html = get_html_split($sptag,
                                           $areabody, $html, $col['macthtype']);
                }
                $htmlparse->SetSource($html, $cururl, 'link');
                $lk = 0;
                foreach ($htmlparse->Links as $k => $v) {
                    //网址必须包含，优先级高于网址不能包含
                    if (($col['need'] != '' &&
                         !preg_match("#" . $col['need'] . "#i", $v['link'])) ||
                        ($col['cannot'] != '' &&
                         preg_match("#" . $col['cannot'] . "#i", $v['link']))
                    ) {
                        continue;
                    }
                    $tmplink[$arrStart][0] = $v;
                    $tmplink[$arrStart][1] = $k;
                    $arrStart++;
                    $lk++;
                }
                $htmlparse->Clear();
            } //在位置内
        } //end foreach
        if ($col['cosort'] !== 'asc') {
            krsort($tmplink); //倒序数组，与目标站顺序相反
        }
        $unum = count($tmplink);
        if ($unum > 0) {
            //echo "完成本次种子网址抓取，共找到：{$unum} 个记录!<br/>\r\n";
            foreach ($tmplink as $vs) {
                $v = $vs[0];
                //$k = addslashes($vs[1]); //此处值和$vs[0]['link']相同
                if ($islisten == 1) {
                    $lrow = $this->registry->db->query_first(
                        'SELECT COUNT(nid) AS total FROM ' . TABLE_PREFIX .
                        'co_listen' . ' WHERE nid=' . $col['nid'] . " AND hash='" .
                        md5($v['link']) . "' ");
                    if ($lrow['total'] > 0) {
                        continue;
                    }
                }
                $sql = 'INSERT INTO ' . TABLE_PREFIX . 'co_html' .
                       " (nid,	title,	url,	litpic,	dtime,	isdown,	result) VALUES ('" .
                       $col['nid'] . "','" .
                       $this->registry->db->escape_string($v['title']) . "' , '" .
                       $this->registry->db->escape_string($v['link']) . "' , '" .
                       $this->registry->db->escape_string($v['image']) . "' , '" .
                       TIMENOW . "','0','')";
                $this->registry->db->query_write($sql);
                $this->registry->db->query_write(
                    'INSERT INTO ' . TABLE_PREFIX . 'co_listen' .
                    " (hash,	nid) VALUES ('" . md5($v['link']) . "','" . $col['nid'] .
                    "')");
            }
            if ($endpos >= $totallen) {
                return 0;
            } else {
                return ($totallen - $endpos);
            }
        } else {
            // echo $this->registry->lang['notfound_url'];
            //仅在第一批采集时出错才返回
            if ($glstart == 0) {
                return -1;
            }
            //在其它页出错照常采集后面内容
            if ($endpos >= $totallen) {
                return 0;
            } else {
                return ($totallen - $endpos);
            }
        }
    }

    //---------------------
    //测试列表规则
    //---------------------
    function TestList()
    {
        $col = & $this->codeArray;
        if (isset($col['listarray'][0])) {
            $dourl = $col['listarray'][0];
        } else {
            echo $this->registry->lang['listurl_error'];
            return;
        }
        require (DIR . '/includes/class_html.php');
        $dhtml = new htmlparse();
        $html = fetch_body_request($dourl, $col['language']);
        $html = $this->filterhtml($col['pagerepad'], $html); //过滤页面广告
        if ($html == '') {
            return $this->registry->lang['firsturl_error'];
        }
        if (trim($col['linkarea']) != '' && trim($col['linkarea']) != '[!--me--]') {
            $html = get_html_split('[!--me--]', $col['linkarea'], $html,
                                   $col['macthtype']);
        }
        $dhtml->GetLinkType = 'link';
        $dhtml->SetSource($html, $dourl, false);
        $dhtml->Links = array_reverse($dhtml->Links); //倒序数组
        $rehtml = ''; //返回信息
        $TestPage = ''; //测试信息页的网址
        if (is_array($dhtml->Links)) {
            $rehtml = sprintf($this->registry->lang['found_url'], $dourl);
            foreach ($dhtml->Links as $k => $v) {
                //网址必须包含，优先级高于网址不能包含
                if (($col['need'] != '' &&
                     !preg_match("#" . $col['need'] . "#i", $v['link'])) || ($col['cannot'] !=
                                                                             '' && preg_match("#" . $col['cannot'] . "#i", $v['link']))
                ) {
                    continue;
                }
                //$links[] = $v; //返回数组
                $rehtml .= $v['link'] . ' - ' . $v['title'] .
                           "\r\n";
                $TestPage = $k;
            } //end foreach
        } else {
            return $this->registry->lang['parsehtml_error'];
        }
        $arr = array($rehtml, $TestPage);
        //返回数组（列表测试信息和要测试的信息页网址）
        return $arr;
    }

    /**
     * 取得内容页字段信息
     *
     * @param        string    $str    内容匹配正则
     * @param        string    $rule    内容过滤正则
     * @access  public
     * @return  string
     */
    function get_page_fieds($str, $rule = NULL)
    {
        if ($this->tmpHtml == '') {
            return '';
        }
        $tmpHtml = '';
        $pad = '[!--me--]';
        if (!strpos($str, $pad)) {
            return $str;
        } else {
            $macthtype = $this->codeArray['macthtype'] == 'string' ? 'string'
                    : 'regex';
            $tmpHtml = get_html_split($pad, $str, $this->tmpHtml, $macthtype);
            if (!empty($rule) && !empty($tmpHtml)) {
                $tmpHtml = $this->filterhtml($rule, $tmpHtml);
            }
        }
        return $tmpHtml;
    }

    /**
     * 过滤页面信息
     *
     * @param        string    $rule    内容过滤正则
     * @param        string    $html    HTML代码
     * @access  public
     * @return  string
     */
    function filterhtml($rule, $html)
    {
        $replace = '';
        if (!empty($rule)) {
            preg_match_all("/{suc:trim([^}]*)}([\s\S]*?){\/suc}/", $rule,
                           $matchs);
            if (is_array($matchs[2]) && !empty($matchs[2])) {
                foreach ($matchs[2] as $key => $value) {
                    //替换为字符，如果规则中没有设置，则使用replace=''
                    $replace_str = !empty($matchs[1][$key]) ? trim(
                        $matchs[1][$key]) : 'replace=\'\'';
                    $replace_str = '$' . $replace_str . ';'; //组成替换为字符串变量
                    eval($replace_str);
                    $rl = str_replace("/", "\\/", $value);
                    //此处$replace来自于上面的replace=
                    $html = preg_replace("/$rl/isU", $replace, $html);
                }
            }
        }
        return $html;
    }

    /**
     * 采集网页内容
     * @param    int            $aid      co_html表中的aid
     * @param    int            $nid      co_html表中的nid
     * @param    string    $dourl    下载的网址
     * @return    void
     */
    function gather_url($aid, $nid, $dourl)
    {
        $col = & $this->codeArray;
        if (empty($aid) || empty($dourl) || empty($col)) {
            return false;
        }
        $this->tmpHtml = fetch_body_request($dourl, $col['language']);
        $film = array();
        $film['title'] = $this->get_page_fieds($col['title'],
                                               $col['title_trim']);
        $film['actor'] = $this->get_page_fieds($col['actor'],
                                               $col['actor_trim']);
        $film['director'] = $this->get_page_fieds($col['director'],
                                                  $col['director_trim']);
        $film['pubdate'] = $this->get_page_fieds($col['pubdate'],
                                                 $col['pubdate_trim']);
        $film['status'] = $this->get_page_fieds($col['status'],
                                                $col['status_trim']);
        //图片处理:start
        $film['image'] = $this->get_page_fieds($col['image'],
                                               $col['image_trim']);
        $film['image'] = $this->FillUrl($film['image'], $dourl); //返回绝对路径
        //是否下载远程图片
        if (isset($col['savepic']) && $col['savepic'] == 1) {
            $doimgurl = $film['image']; //远程图片文件
            //检测是否已经下载此文件
            $tofile = '';
            $row = $this->registry->db->query_first(
                'SELECT hash,tofile FROM ' . TABLE_PREFIX . 'co_media' .
                ' WHERE nid=' . $nid . " AND hash='" . md5($doimgurl) . "' ");
            if (isset($row['tofile'])) {
                $tofile = $row['tofile'];
            }
            //如果不存在，下载文件
            if ($tofile == '' || !is_file(DIR . '/' . $tofile)) {
                $imagebinary = fetch_body_request($doimgurl, '', 'img');
                $imgPath = $this->registry->config['Misc']['imagedir'] .
                           '/posters/' . skyuc_date('YmdH');
                //创建文件夹
                if (!is_dir($imgPath . '/source/')) {
                    make_dir(DIR . '/' . $imgPath . '/source/');
                }
                if (!is_dir($imgPath . '/image/')) {
                    make_dir(DIR . '/' . $imgPath . '/image/');
                }
                if (!is_dir($imgPath . '/thumb/')) {
                    make_dir(DIR . '/' . $imgPath . '/thumb/');
                }
                //图片文件名
                $fileName = TIMENOW . '_' . random(3, 0) . '.' .
                            file_extension($film['image']);
                //图片写入本地
                if (file_put_contents(
                    DIR . '/' . $imgPath . '/source/' . $fileName, $imagebinary)
                ) {
                    $film['source'] = $imgPath . '/source/' . $fileName;
                }
                if (file_put_contents(
                    DIR . '/' . $imgPath . '/image/' . $fileName, $imagebinary)
                ) {
                    $film['image'] = $imgPath . '/image/' . $fileName;
                }
                if (file_put_contents(
                    DIR . '/' . $imgPath . '/thumb/' . $fileName, $imagebinary)
                ) {
                    $film['thumb'] = $imgPath . '/thumb/' . $fileName;
                }
                //下载文件成功，保存记录
                if (is_file(DIR . '/' . $film['source'])) {
                    if ($tofile == '') {
                        $sql = 'INSERT INTO ' . TABLE_PREFIX . 'co_media' .
                               ' (nid,hash,tofile) ' . " VALUES ('" . $nid . "', '" .
                               md5($doimgurl) . "', '" .
                               $this->registry->db->escape_string($film['source']) .
                               "')";
                    } else {
                        $sql = 'UPDATE ' . TABLE_PREFIX . 'co_media' .
                               " SET tofile='" .
                               $this->registry->db->escape_string($film['source']) .
                               "' WHERE hash='" . md5($doimgurl) . "'";
                    }
                    $this->registry->db->query_write($sql);
                }
            } else {
                // 存在已下载的原始图片
                $film['source'] = $tofile;
                $film['image'] = str_replace('/source/', '/image/', $tofile);
                $film['thumb'] = str_replace('/source/', '/thumb/', $tofile);
            }
        } else {
            $film['source'] = $film['thumb'] = $film['image'];
        }
        //图片处理:end
        //地址处理:start
        if ($col['runphp'] == 1 and !empty($col['runphp_code'])) {
            $film['url'] = $this->RunPHP($col['runphp_code'], $col['language']);
        } else {
            $film['url'] = $this->get_page_fieds($col['url'], $col['url_trim']);
        }
        //地址处理:end
        $film['area'] = $this->get_page_fieds($col['area'],
                                              $col['area_trim']);
        $film['lang'] = $this->get_page_fieds($col['lang'], $col['lang_trim']);
        $film['detail'] = $this->get_page_fieds($col['detail'],
                                                $col['detail_trim']);
        $itemconfig = '';
        $itemconfig .= '<suc:title>' . $film['title'] . "</suc>\r\n";
        $itemconfig .= '<suc:actor>' . $film['actor'] . "</suc>\r\n";
        $itemconfig .= '<suc:director>' . $film['director'] . "</suc>\r\n";
        $itemconfig .= '<suc:image>' . $film['image'] . "</suc>\r\n";
        $itemconfig .= '<suc:thumb>' . $film['thumb'] . "</suc>\r\n";
        $itemconfig .= '<suc:source>' . $film['source'] . "</suc>\r\n";
        $itemconfig .= '<suc:pubdate>' . $film['pubdate'] . "</suc>\r\n";
        $itemconfig .= '<suc:status>' . $film['status'] . "</suc>\r\n";
        $itemconfig .= '<suc:area>' . $film['area'] . "</suc>\r\n";
        $itemconfig .= '<suc:lang>' . $film['lang'] . "</suc>\r\n";
        $itemconfig .= '<suc:detail>' . $film['detail'] . "</suc>\r\n";
        $itemconfig .= '<suc:url>' . $film['url'] . "</suc>\r\n";
        $sql = 'UPDATE ' . TABLE_PREFIX . 'co_html' . ' SET dtime=' . TIMENOW .
               ", result='" . $this->registry->db->escape_string($itemconfig) .
               "', isdown=1 WHERE aid=" . $aid;
        $this->registry->db->query_write($sql);
        return true;
    }

    /**
     * 临时查看远程页面信息
     *
     * @param    string    $dourl    下载的网址
     * @param    int            $type    类型，1为测试规则，0为临时查看远程信息
     * @return    stirng
     */
    function get_test_url($dourl, $type = 0)
    {
        $col = & $this->codeArray;
        $this->tmpHtml = fetch_body_request($dourl, $col['language']);
        $film = array();
        $film['title'] = $this->get_page_fieds($col['title'],
                                               $col['title_trim']);
        $film['actor'] = $this->get_page_fieds($col['actor'],
                                               $col['actor_trim']);
        $film['director'] = $this->get_page_fieds($col['director'],
                                                  $col['director_trim']);
        $film['pubdate'] = $this->get_page_fieds($col['pubdate'],
                                                 $col['pubdate_trim']);
        $film['status'] = $this->get_page_fieds($col['status'],
                                                $col['status_trim']);
        //图片处理:start
        $film['image'] = $this->get_page_fieds($col['image'],
                                               $col['image_trim']);
        $film['image'] = $this->FillUrl($film['image'], $dourl); //返回绝对路径
        //图片处理:end
        $film['area'] = $this->get_page_fieds($col['area'],
                                              $col['area_trim']);
        $film['lang'] = $this->get_page_fieds($col['lang'], $col['lang_trim']);
        $film['detail'] = $this->get_page_fieds($col['detail'],
                                                $col['detail_trim']);
        //地址处理:start
        if ($col['runphp'] == 1 and !empty($col['runphp_code'])) {
            $film['url'] = $this->RunPHP($col['runphp_code'], $col['language']);
        } else {
            $film['url'] = $this->get_page_fieds($col['url'], $col['url_trim']);
        }
        //地址处理:end
        $testurl = $dourl . "\r\n";
        $itemconfig = '<suc:title>' . $film['title'] . "</suc>\r\n";
        $itemconfig .= '<suc:actor>' . $film['actor'] . "</suc>\r\n";
        $itemconfig .= '<suc:director>' . $film['director'] . "</suc>\r\n";
        $itemconfig .= '<suc:image>' . $film['image'] . "</suc>\r\n";
        $itemconfig .= '<suc:pubdate>' . $film['pubdate'] . "</suc>\r\n";
        $itemconfig .= '<suc:status>' . $film['status'] . "</suc>\r\n";
        $itemconfig .= '<suc:area>' . $film['area'] . "</suc>\r\n";
        $itemconfig .= '<suc:lang>' . $film['lang'] . "</suc>\r\n";
        $itemconfig .= '<suc:detail>' . $film['detail'] . "</suc>\r\n";
        $itemconfig .= '<suc:url>' . $film['url'] . "</suc>\r\n";
        if ($type == 1) {
            return $testurl . $itemconfig;
        } else {
            return $itemconfig;
        }
    }

    /**
     * URL补全函数，不同于cls_html.php中的
     *
     * @param    string    $url    相对路径 例：/img/2008.jpg
     * @param    array        $httpurl    当前网址 例：http://www.skyuc.com/list-28.html
     * @return    stirng 返回绝对路径 例：http://www.skyuc.com/img/2008.jpg
     */
    function FillUrl($url, $httpurl)
    {
        $surl = $url;
        if (strlen($surl) > 0) {
            $rurl = str_replace(chr(34), '', $surl); //过滤双引号
            $rurl = str_replace(chr(39), '', $rurl); //过滤单引号
        } else {
            return $url;
        }
        $url_parsed = parse_url($httpurl);
        $scheme = $url_parsed['scheme'];
        if ($scheme != '') {
            $scheme = $scheme . '://';
        }
        $host = $url_parsed['host'];
        $HomeUrl = $scheme . $host; //主页地址
        if (strlen($HomeUrl) == 0) {
            return $url;
        }
        $path = dirname($url_parsed['path']);
        if ($path[0] == "\\") {
            $path = '';
        }
        $pos = strpos($rurl, '#');
        if ($pos > 0) {
            $rurl = substr($rurl, 0, $pos);
        }
        //判断类型
        if (preg_match(
            "/^(http|https|ftp):(\/\/|\\\\)(([\w\/\\\+\-~`@:%])+\.)+([\w\/\\\.\=\?\+\-~`@\':!%#]|(&amp;)|&)+/i",
            $rurl)
        ) {
            //http开头的url类型要跳过
            return $url;
        } elseif ($rurl[0] == '/') {
            //绝对路径
            $rurl = $HomeUrl . $rurl;
        } elseif (substr($rurl, 0, 3) == '../') { //相对路径
            while (substr($rurl, 0, 3) == '../') {
                $rurl = substr($rurl, strlen($rurl) - (strlen($rurl) - 3),
                               strlen($rurl) - 3);
                if (strlen($path) > 0) {
                    $path = dirname($path);
                }
            }
            $rurl = $HomeUrl . $path . '/' . $rurl;
        } elseif (substr($rurl, 0, 2) == './') {
            $rurl = $HomeUrl . $path . substr($rurl,
                                              strlen($rurl) - (strlen($rurl) - 1), strlen($rurl) - 1);
        } elseif (strtolower(substr($rurl, 0, 7)) == 'mailto:' ||
                  strtolower(substr($rurl, 0, 11)) == 'javascript:'
        ) {
            return $url;
        } else {
            $rurl = $HomeUrl . $path . '/' . $rurl;
        }
        $newurl = str_replace($surl, $rurl, $url);
        $newurl = str_replace("\\", '', $newurl); //加此行去除当路径为根目录时会多出一个反斜线
        return $newurl;
    }

    /**
     * 用扩展函数处理采集到的原始数据
     *
     * @param    string    $phpcode    自定义PHP接口代码
     * @param    string    $charset    原始网页字符编码
     * @return    stirng $me
     */
    function RunPHP($phpcode, $charset = 'utf-8')
    {
        $MeValue = '';
        $phpcode = preg_replace("/'@me'|\"@me\"|@me/isU", '$MeValue', $phpcode);
        if (preg_match("#@body#i", $phpcode)) {
            $BodyValue = $this->tmpHtml;
            $phpcode = preg_replace("/'@body'|\"@body\"|@body/isU",
                                    '$BodyValue', $phpcode);
        }
        if (preg_match("#file_get_url#i", $phpcode)) {
            $phpcode = preg_replace('/file_get_url\(([^)]*)\)/isU',
                                    'fetch_body_request(\\1, \'' . $charset . '\')', $phpcode);
        }
        eval($phpcode . ';');
        return $MeValue;
    }
}

// #######################################################################
// ######################## 采集程序 私有函数库    ############################
// #######################################################################
/**
 * 获取特定区域的HTML
 *
 * @access public
 * @param    string    $sptag    分隔符号
 * @param    string    $areaRule    匹配正则
 * @param    string    $html    原始ＨＴＭＬ
 * @param    string    $matchtype    匹配方式，默认正则
 * @return    string
 */
function get_html_split($sptag, &$areaRule, &$html, $matchtype = 'regex')
{
    //用正则表达式的模式匹配
    if ($matchtype == 'regex') {
        //$areaRule = str_replace('/', '\/', $areaRule);
        $areaRules = explode($sptag, $areaRule);
        $arr = array();
        if ($html == '' || $areaRules[0] == '') {
            return '';
        }
        //echo $areaRules[0] . $areaRules[1] .'<br>';
        preg_match(
            '#' . $areaRules[0] . '(.*)' . $areaRules[1] . '#isU', $html, $arr);
        return empty($arr[1]) ? '' : trim($arr[1]);
    } else { //用字符串模式匹配
        $areaRules = explode($sptag, $areaRule);
        if ($html == '' || $areaRules[0] == '') {
            return '';
        }
        $posstart = @strpos($html, $areaRules[0]);
        if ($posstart === false) {
            return '';
        }
        $posend = @strpos($html, $areaRules[1], $posstart);
        if ($posend > $posstart && $posend !== false) {
            return substr($html, $posstart + strlen($areaRules[0]),
                          $posend - $posstart - strlen($areaRules[0]));
        } else {
            return '';
        }
    }
}

/**
 * 取得所有链接
 *
 * @access  public
 * @return  array
 */
function get_all_link($code)
{
    preg_match_all(
        '/<a[^>]*href=["|\']?([^>"\']+)["|\']?\s*[^>]*>([^>]+)<\/a>/is', $code, $arr);
    /*地址中含空格不能截取整个地址
				preg_match_all('/<a[^>]*href=["|\']?([^>"\' ]+)["|\']?\s*[^>]*>([^>]+)<\/a>/is',$code,$arr); */
    $match_address = array('name' => $arr[2], 'url' => $arr[1]);
    if (version_compare(PHP_VERSION, '5.2.9', '>=')) {
        $match_address['name'] = array_unique($match_address['name'],
                                              SORT_REGULAR);
        $match_address['url'] = array_unique($match_address['url'],
                                             SORT_REGULAR);
    } else {
        $match_address['name'] = array_unique($match_address['name']);
        $match_address['url'] = array_unique($match_address['url']);
    }
    return $match_address;
}

/**
 * 解析专家模式影片信息代码
 *
 * @param        string 专家代码
 * @access  public
 * @return  array
 */
function parse_film_code($procode)
{
    $sucodes = '';
    preg_match_all("/<suc:([^>]+)>([\s\S]*?)<\/suc>/", $procode, $matchs);
    for ($i = 0; $i < count($matchs[1]); $i++) {
        if (in_array($matchs[1][$i],
                     array('server_id', 'cat_id', 'click_count', 'cat_id', 'points',
                          'runtime', 'attribute'))
        ) {
            $sucodes[$matchs[1][$i]] = intval($matchs[2][$i]);
        } elseif (in_array($matchs[1][$i],
                           array('director', 'actor', 'title', 'image', 'thumb', 'source',
                                'detail', 'pubdate', 'status', 'area', 'lang', 'player', 'add_time'))
        ) {
            $sucodes[$matchs[1][$i]] = trim($matchs[2][$i]);
        } elseif (in_array($matchs[1][$i], array('url'))) {
            $sucodes[$matchs[1][$i]] = trim($matchs[2][$i]);
        }
    }
    unset($matchs);
    $sucodes['is_show'] = 1;
    return $sucodes;
}

/**
 * 影片信息入库操作
 *
 * @param  object    $filmInfo     影片数组
 * @param     intval $onlytitle        　是否跳过重名影片
 */
function insert_film_info($filmInfo, $onlytitle = 1)
{
    if (empty($filmInfo) || !$filmInfo['title']) {
        return false;
    }
    $title = $filmInfo['title'];
    $filmInfo['actor'] = trim(
        preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", ' ', $filmInfo['actor']));
    $filmInfo['director'] = trim(
        preg_replace("/(\s*(\r\n|\n\r|\n|\r)\s*)/", ' ', $filmInfo['director']));
    //修复地址
    $filmInfo['data'] = repair_url_data(array($filmInfo['url']));
    $description = html2text(sub_str($filmInfo['detail'], 100));
    $keywords = preg_replace("/\[(.*)\]/u", '', $title) . ' ' .
                $filmInfo['actor'];
    $type = 'insert'; //新片插入
    $sql = 'SELECT show_id, image, thumb, source, data, player, server_id FROM ' .
           TABLE_PREFIX . 'show' . " WHERE title = '" .
           $GLOBALS['db']->escape_string($title) . "' AND cat_id = " .
           $filmInfo['cat_id'];
    $show = $GLOBALS['db']->query_first($sql);
    if ($show['show_id'] > 0) {
        if (!empty($onlytitle)) {
            $type = 'update'; //存在同名影片，更新地址
        } else {
            return false; //跳过重名影片
        }
    }
    if ($type == 'insert') {
        $sql = 'INSERT INTO ' . TABLE_PREFIX . 'show' .
               ' (title, actor, director, cat_id,	server_id, image, thumb,source, keywords, description, detail, pubdate, status, area, lang, click_count, data , is_show, add_time, points, runtime, player) ' .
               " VALUES ('" . $GLOBALS['db']->escape_string($title) . "',  '" .
               $GLOBALS['db']->escape_string($filmInfo['actor']) . "', '" .
               $GLOBALS['db']->escape_string($filmInfo['director']) . "', '" .
               $filmInfo['cat_id'] . "', '" . $filmInfo['server_id'] . "',	'" .
               $GLOBALS['db']->escape_string($filmInfo['image']) . "', '" .
               $GLOBALS['db']->escape_string($filmInfo['thumb']) . "', '" .
               $GLOBALS['db']->escape_string($filmInfo['source']) . "', '" .
               $GLOBALS['db']->escape_string($keywords) . "','" .
               $GLOBALS['db']->escape_string($description) . "','" .
               $GLOBALS['db']->escape_string($filmInfo['detail']) . "', '" .
               $filmInfo['pubdate'] . "', '" . $filmInfo['status'] . "', '" .
               $GLOBALS['db']->escape_string($filmInfo['area']) . "', '" .
               $GLOBALS['db']->escape_string($filmInfo['lang']) . "', '" .
               $filmInfo['click_count'] . "', '" .
               $GLOBALS['db']->escape_string($filmInfo['data']) . "', '1' ,'" . TIMENOW .
               "', '0', '45' ,'" . $GLOBALS['db']->escape_string($filmInfo['player']) .
               "')";
        $GLOBALS['db']->query_write($sql);
        include_once (DIR . '/includes/functions_search.php');
        $param = array();
        $param['show_id'] = $GLOBALS['db']->insert_id();
        $param['cat_id'] = $filmInfo['cat_id'];
        $param['title'] = $title;
        $param['title_alias'] = $filmInfo['title_alias'];
        $param['title_english'] = $filmInfo['title_english'];
        $param['actor'] = $filmInfo['actor'];
        $param['director'] = $filmInfo['director'];
        $param['detail'] = '';
        add_search_index($param);
        return true;
    }
    elseif ($show['show_id'] > 0 && $type == 'update') {
        //判断播放器是否相同
        if ($show['player'] != $filmInfo['player']) {
            $player = explode(',', $show['player']);
            // 播放器不存在，/添加新的播放器、服务器和新地址
            if (!in_array($filmInfo['player'], $player)) {
                $filmInfo['player'] = $show['player'] . ',' . $filmInfo['player'];
                $filmInfo['server_id'] = $show['server_id'] . ',' .
                                         $filmInfo['server_id'];
                //已存在的地址结尾有###分割符，故不再添加。
                if (substr($show['data'], -3) == '###')
                    $filmInfo['data'] = $show['data'] . $filmInfo['data'];
                else
                    $filmInfo['data'] = $show['data'] . '###' . $filmInfo['data'];
            } else {
                foreach ($player as $key => $value) {
                    //播放器已存在，替换对应的地址数组$key值为新地址
                    if ($value == $filmInfo['player']) {
                        $data = explode('###', $show['data']);
                        $data["$key"] = $filmInfo['data'];
                        $filmInfo['data'] = implode('###', $data);
                    }
                }
            }
        }
        //更新重名影片所有内容
        if ($onlytitle == 2) {
            $sql = 'UPDATE ' . TABLE_PREFIX . 'show' . " SET title = '" .
                   $GLOBALS['db']->escape_string($title) . "', " . " actor = '" .
                   $GLOBALS['db']->escape_string($filmInfo['actor']) . "', " .
                   " director = '" .
                   $GLOBALS['db']->escape_string($filmInfo['director']) . "', " .
                   " cat_id = '" . $filmInfo['cat_id'] . "', " . " server_id = '" .
                   $filmInfo['server_id'] . "', " . " data ='" .
                   $GLOBALS['db']->escape_string($filmInfo['data']) . "', ";
            // 如果有新下载图片，需要更新数据库
            if (!empty($filmInfo['image'])) {
                if ($show['image'] != $filmInfo['image']) {
                    if ($show['image'] != '' && pic_parse_url($show['image'])) {
                        @unlink(DIR . '/' . $show['image']);
                    }
                    if ($show['source'] != '' && pic_parse_url($show['source'])) {
                        @unlink(DIR . '/' . $show['source']);
                    }
                    $sql .= "image = '" .
                            $GLOBALS['db']->escape_string($filmInfo['image']) .
                            "', source = '" .
                            $GLOBALS['db']->escape_string($filmInfo['source']) . "', ";
                }
                if ($show['thumb'] != $filmInfo['thumb']) {
                    if ($show['thumb'] != '' && pic_parse_url($show['thumb'])) {
                        @unlink(DIR . '/' . $show['thumb']);
                    }
                    $sql .= "thumb = '" .
                            $GLOBALS['db']->escape_string($filmInfo['thumb']) . "', ";
                }
            }
            $sql .= " keywords = '" . $GLOBALS['db']->escape_string($keywords) .
                    "', " . " description = '" .
                    $GLOBALS['db']->escape_string($description) . "', " . " detail = '" .
                    $GLOBALS['db']->escape_string($filmInfo['detail']) . "', ";
            if (!empty($filmInfo['pubdate'])) {
                $sql .= " pubdate = '" . $filmInfo['pubdate'] . "', ";
            }
            if (!empty($filmInfo['status'])) {
                $sql .= " status = '" . $filmInfo['status'] . "', ";
            }
            if (!empty($filmInfo['area'])) {
                $sql .= " area = '" .
                        $GLOBALS['db']->escape_string($filmInfo['area']) . "', ";
            }
            if (!empty($filmInfo['lang'])) {
                $sql .= " lang = '" .
                        $GLOBALS['db']->escape_string($filmInfo['lang']) . "',";
            }
            $sql .= " add_time = '" . TIMENOW . "', " . " player = '" .
                    $filmInfo['player'] . "' " . ' WHERE show_id = ' . $show['show_id'];
        } else {
            $sql = 'UPDATE ' . TABLE_PREFIX . 'show' . " SET data ='" .
                   $GLOBALS['db']->escape_string($filmInfo['data']) . "', " .
                   " server_id = '" . $filmInfo['server_id'] . "', " . " player = '" .
                   $filmInfo['player'] . "', " . " add_time = '" . TIMENOW .
                   "' ";
            if (!empty($filmInfo['status'])) {
                $sql .= ", status = '" . $filmInfo['status'] . "' ";
            }
            $sql .= ' WHERE show_id = ' . $show['show_id'];
        }
        $GLOBALS['db']->query_write($sql);
        return true;
    }
}

/**
 * 采集节点列表
 *
 * @access  public
 * @return  array
 */
function get_col_list()
{
    global $skyuc;
    $skyuc->input->clean_array_gpc('r',
                                   array('sort_by' => TYPE_STR, 'sort_order' => TYPE_STR));
    // 过滤条件
    $filter['sort_by'] = iif(empty($skyuc->GPC['sort_by']), 'nid',
                                                            $skyuc->GPC['sort_by']);
    $filter['sort_order'] = iif(empty($skyuc->GPC['sort_order']), 'DESC',
                                                                  $skyuc->GPC['sort_order']);
    // 记录总数
    $sql = 'SELECT count(nid) AS total FROM ' . TABLE_PREFIX . 'co_note';
    $total = $skyuc->db->query_first($sql);
    $filter['record_count'] = $total['total'];
    // 分页大小
    $filter = page_and_size($filter);
    $sql = 'SELECT c.nid,c.gathername,c.language,c.player,c.cat_id,c.server_id,';
    $sql .= 'c.savetime,c.lasttime,t.cat_name as typename, COUNT(n.aid)	as notes	FROM ' .
            TABLE_PREFIX . 'co_note' . '  AS c ';
    $sql .= 'LEFT JOIN ' . TABLE_PREFIX . 'category' .
            ' AS t  ON t.cat_id=c.cat_id ';
    $sql .= ' LEFT JOIN ' . TABLE_PREFIX . 'co_html' . ' AS n  ON n.nid=c.nid ';
    $sql .= ' GROUP by ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
    $sql = $skyuc->db->query_limit($sql, $filter['page_size'], $filter['start']);
    $res = $skyuc->db->query_read($sql);
    $arr = array();
    while ($row = $skyuc->db->fetch_array($res)) {
        if (empty($row['lasttime'])) {
            $row['lasttime'] = $skyuc->lang['no_collection'];
        } else {
            $row['lasttime'] = skyuc_date($skyuc->options['date_format'],
                                          $row['lasttime']);
        }
        $row['savetime'] = skyuc_date($skyuc->options['date_format'],
                                      $row['savetime']);
        $arr[] = $row;
    }
    $arr = array('col' => $arr, 'filter' => $filter,
                 'page_count' => $filter['page_count'],
                 'record_count' => $filter['record_count']);
    return $arr;
}

/**
 * 已采集资源列表
 *
 * @access  public
 * @return  array
 */
function col_url_list($nid = NUll)
{
    global $skyuc;
    $skyuc->input->clean_array_gpc('r',
                                   array('sort_by' => TYPE_STR, 'sort_order' => TYPE_STR,
                                        'keyword' => TYPE_STR));
    // 过滤条件
    $filter['sort_by'] = iif(empty($skyuc->GPC['sort_by']), 'aid',
                                                            $skyuc->GPC['sort_by']);
    $filter['sort_order'] = iif(empty($skyuc->GPC['sort_order']), 'DESC',
                                                                  $skyuc->GPC['sort_order']);
    $filter['keyword'] = $skyuc->GPC['keyword'];
    // 关键字
    if (!empty($filter['keyword'])) {
        $where = " WHERE  c.title LIKE '%" .
                 $skyuc->db->escape_string_like($filter['keyword']) . "%' ";
    }
    if (!empty($nid)) {
        $where = ' WHERE  c.nid= ' . $nid;
    }
    // 记录总数
    $sql = 'SELECT COUNT(c.aid) AS total FROM ' . TABLE_PREFIX . 'co_html' .
           ' AS c ' . $where;
    $total = $skyuc->db->query_first($sql);
    $filter['record_count'] = $total['total'];
    // 分页大小
    $filter = page_and_size($filter);
    $sql = 'SELECT c.aid,c.nid,c.isexport,c.title,c.url,';
    $sql .= 'c.dtime,c.isdown,u.gathername FROM ' . TABLE_PREFIX . 'co_html' .
            ' AS c ';
    $sql .= ' LEFT JOIN ' . TABLE_PREFIX . 'co_note' . ' AS u ON u.nid=c.nid  ' .
            $where;
    $sql .= ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'];
    $sql = $skyuc->db->query_limit($sql, $filter['page_size'], $filter['start']);
    $res = $skyuc->db->query_read($sql);
    $arr = array();
    while ($row = $skyuc->db->fetch_array($res)) {
        $row['dtime'] = skyuc_date(
            $skyuc->options['date_format'] . ' ' . $skyuc->options['time_format'],
            $row['dtime']);
        $row['isdown'] = iif($row['isdown'], $skyuc->lang['download_yes'],
                             $skyuc->lang['download_no']);
        $row['isexport'] = iif($row['isexport'], $skyuc->lang['exdata_yes'],
                               $skyuc->lang['exdata_no']);
        $arr[] = $row;
    }
    $arr = array('col' => $arr, 'filter' => $filter,
                 'page_count' => $filter['page_count'],
                 'record_count' => $filter['record_count']);
    return $arr;
}

?>
