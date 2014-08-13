<?php
/**
 * SKYUC! 管理中心采集管理语言文件
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
*/

/*------------------------------------------------------ */
//-- 节点列表
/*------------------------------------------------------ */
$_LANG['gathername'] = '节点名称';
$_LANG['cat_id'] = '入库栏目';
$_LANG['lasttime'] = '最后采集';
$_LANG['savetime'] = '加入时间';
$_LANG['language'] = '编码';
$_LANG['notes'] = '网址数';
$_LANG['col_notes'] ='种子网址数';


$_LANG['addconote'] = '添加新节点';
$_LANG['importrule'] = '导入规则';
$_LANG['exportrule'] = '导出规则';
$_LANG['collection'] = '采集';
$_LANG['exportdown'] = '导出数据';
$_LANG['editnote'] = '更改';
$_LANG['testrule'] = '测试';
$_LANG['viewdown'] = '查看已下载';
$_LANG['copynote'] = '复制';
$_LANG['clearnote'] = '清空';
$_LANG['delnote'] = '删除';

$_LANG['select_all'] = '全选';
$_LANG['delete_url'] = '仅删除网址';
$_LANG['clear_content'] = '仅清空内容';
$_LANG['delete_url_history'] = '删除网址及历史记录';
$_LANG['delete_trash'] = '删除垃圾图片';

$_LANG['addconote_ok'] = '添加新节点成功！';
$_LANG['continue_add'] = '继续添加新节点！';
$_LANG['editconote_ok'] = '修改节点成功！';
$_LANG['back_conote_list'] = '返回节点管理列表';
$_LANG['no_collection'] = '从未采集';

//采集
$_LANG['clearall'] = '清空临时内容';
$_LANG['collection_note'] = '采集指定节点';
$_LANG['all_node']= '所有节点';
$_LANG['collection_title'] = '监控式采集';
$_LANG['use_monitor'] = '没指定采集节点，将使用检测新内容采集模式！';
$_LANG['no_seed'] = '没有记录或从来没有采集过这个节点！';
$_LANG['seeds'] = "共有 %d 个历史种子网址！<a href='javascript:SubmitNew();'>[<u>更新种子网址，并采集</u>]</a>";
$_LANG['pagesize'] = '每页采集';
$_LANG['threadnum'] = '条，线程数';
$_LANG['sptime'] = '间隔时间';
$_LANG['second'] = '秒（防刷新的站点需设置）';
$_LANG['islisten'] = '附加选项';

$_LANG['islisten_no'] = '监控采集模式(检测当前或所有节点是否有新内容)';
$_LANG['islisten_on'] = '下载种子网址的未下载内容';
$_LANG['islisten_re'] = '重新下载全部内容';
$_LANG['dostart'] ='开始采集网页';
$_LANG['viewnotes'] ='查看种子网址';
$_LANG['viewdown'] = '查看已下载';
$_LANG['noteurl'] = '节点的种子网址';
$_LANG['divmin'] = '缩小';
$_LANG['divmax'] = '增大';

//采集内容列表
$_LANG['download_no'] = '未下载';
$_LANG['download_yes'] = '已下载';
$_LANG['exdata_no'] = '未导出';
$_LANG['exdata_yes'] = '已导出';
$_LANG['url_title'] = '内容标题';
$_LANG['dtime'] = '获取时间';
$_LANG['isdown'] = '下载';
$_LANG['isexport'] = '导出';
$_LANG['sourcepage'] = '来源网页';
$_LANG['sourceurl'] = '[源网址]';

//测试规则
$_LANG['collection_demo'] = '采集内容预览';
$_LANG['collection_test'] = '测试节点';
$_LANG['listurl_error'] = "配置中指定列表的网址错误！\r\n";
$_LANG['firsturl_error'] = "读取其中的一个网址： %d 时失败！\r\n";
$_LANG['found_url'] = "按指定规则在 %s 发现的网址：\r\n";
$_LANG['parsehtml_error'] = "分析网页的HTML时失败！\r\n";
$_LANG['no_test_url'] = '没有递交测试的网址！';
$_LANG['test_list'] = '列表测试信息';
$_LANG['test_rule'] = '网页规则测试';
$_LANG['test_url'] = '测试网址';
$_LANG['url_demo'] = '　(本网址信息未下载到本地，以下是预览信息)';

/* 规则信息 */
$_LANG['general-tab'] = '基本信息';
$_LANG['url-tab'] ='内容规则';
$_LANG['url-tab'] ='内容规则';
$_LANG['charset'] = '页面编码';
$_LANG['player'] = '播放器';
$_LANG['server'] = '服务器';
$_LANG['split_type'] = '区域匹配模式';
$_LANG['cosort'] = '内容导入顺序';
$_LANG['split_regex'] = '正则表达式';
$_LANG['split_string'] = '字符串';
$_LANG['cosort_asc']= '与目标站一致';
$_LANG['cosort_desc']= '与目标站相反';
$_LANG['runphp_false'] = '使用规则获取地址';
$_LANG['runphp_true'] = '使用PHP处理接口获取地址';
$_LANG['generate_list'] = '按规则生成列表网址';
$_LANG['generate_note'] = '(符合特定序列的列表网址)';
$_LANG['varstart'] = '<span style="color:#F00"> &nbsp[page]&nbsp;</span>从';
$_LANG['varend'] = '到';
$_LANG['addv'] = '每页递增';
$_LANG['varpage'] = ' (填写页码或规律递增数字)';
$_LANG['generate_example'] = '（如：　http://www.skyuc.com/list.php?page=[page]，如果不能匹配所有网址，可以在手工指定网址的地方输入要追加的网址）';
$_LANG['manual_list'] = '手工指定列表网址';
$_LANG['manual_note'] = '(如果列表网址不规范,你可以手工指定,一行为一个列表页)';
$_LANG['pagerepad'] = '页面过滤';
$_LANG['pagerepad_note'] = '(目标网页有妨碍你采集的信息可以指定规则去除它。)<br>格式：  <INPUT TYPE="text" NAME="pad" VALUE="{suc:trim}广告开始(.*)广告结束{/suc}" class="text">';
$_LANG['detail_url'] = '列表区域内网址筛选';
$_LANG['detail_need'] = '网址必需包含';
$_LANG['detail_cannot'] = '网址不能包含';
$_LANG['detail_list'] = '限定列表HTML范围';
$_LANG['detail_note'] = '( 格式：列表开始 <span style="color:#F00"> &nbsp;[!--me--] &nbsp;</span> 列表结束 。如果用限定列表HTML范围无法正确获得需要的网址，可以下面进行再次筛选。)';
$_LANG['note_rule'] = '↓内容匹配规则（格式：内容开始 <span style="color:#F00"> &nbsp;[!--me--]&nbsp; </span> 内容结束）';
$_LANG['note_rule_filter'] = '↓内容过滤规则（格式：{suc:trim}...{/suc}）';
$_LANG['title'] = '影片名称';
$_LANG['actor'] = '领衔主演';
$_LANG['director'] = '导　　演';
$_LANG['image'] = '影片海报';
$_LANG['pubdate'] = '上映日期';
$_LANG['status'] = '影片状态';
$_LANG['area'] = '出品地区';
$_LANG['lang'] = '配音语言';
$_LANG['detail'] = '简介内容';
$_LANG['show_url'] = '点播地址';
$_LANG['savepic'] = '保存远程图片到本地';
$_LANG['selectrule'] = '常用规则';
$_LANG['runphp'] = 'PHP处理接口';
$_LANG['runphp_desc'] = '用于获取影片地址的PHP代码。<BR><BR>@body 表示原始网页HTML代码<BR>@me 表示当前标记值和最终结果 ';


/* 采集内容导出数据*/
$_LANG['exportdata'] = '采集内容导出';
$_LANG['totalcol'] = '节点信息';
$_LANG['totalnote'] = '本节点共有 %d 条数据';
$_LANG['progress_status'] = '进行状态';
$_LANG['export_pagesize'] = '每批导入';
$_LANG['onlytitle'] = '跳过名称重复的影片';
$_LANG['updateurl'] = '仅更新重名影片的地址';
$_LANG['updateall'] = '更新重名影片所有内容';
$_LANG['show_txt_pre'] = '第';
$_LANG['show_txt_ext'] = '集';

/* 导出或导入规则 */
$_LANG['importrule_desc'] = '请在下面输入你要导入的文本配置：(建议用base64编码[支持不编码的规则，但不兼容旧版规则])';
$_LANG['exportrule_desc'] = '以下为规则 [ %s ]  的文本配置，你可以共享给你的朋友！';
$_LANG['export_text'] = '导出为普通格式';
$_LANG['export_base64'] = '导出为 base64 格式';
$_LANG['import_error_rule']= "你导入的规则不是 SKYUC 的采集规则，或者规则不完整！";
$_LANG['import_error_base64rule'] = '该规则不合法，Base64格式的采集规则为：BASE64:base64编码后的配置:END !';
$_LANG['import_error_str'] = '配置字符串有错误！';



//提示消息
$_LANG['notice'] ='例子：比如采集的影片名称中含有[01]这样的序号，我们可以在过滤规则中填写 {suc:trim}\[(.*)\]{/suc} 用于去除这样信息，从而得到一个完整的影片名称。又或者影片信息中没有配音语言这一项，我们可以直接在“内容匹配规则”中输入“中文字幕”等信息。';
$_LANG['selectnote'] = '请选择一个节点！';
$_LANG['information'] = 'SKYUC 提示信息！';
$_LANG['pleaselink'] = '如果你的浏览器没反应，请点击这里...';
$_LANG['note_islisten_on'] = "你指定的模式为：<font color='red'>[下载种子网址中未下载内容]</font>，<br />使用这个模式节点必须已经有种子网址，否则请使用其它模式！";
$_LANG['note_downloaded'] = '检测节点正常，现转向网页采集...';
$_LANG['go_getsource_url'] = '已获得所有种子网址，转向网页采集...';
$_LANG['notfound_new'] = '在这节点中没发现有新内容....';
$_LANG['finish_check'] = '完成所有节点检测....';
$_LANG['checked_col'] = '已检测节点( {%d} )，继续下一个节点...';
$_LANG['limitlist'] = '采集列表剩余：%d 个页面，继续采集...';
$_LANG['notfound_url'] = '按指定规则没找到任何链接！';
$_LANG['no_title'] = '无标题，可能是图片链接';
$_LANG['get_list_faild'] = '获取列表网址失败，无法完成采集！';
$_LANG['notfound_all'] = '获取到的网址为零：可能是规则不对或没发现新内容！';

$_LANG['confirm'] = '你确定要删除这个节点吗?';
$_LANG['delete_succeed'] ='成功删除一个节点！';
$_LANG['copy_succeed'] = '成功复制一个节点！';
$_LANG['delete_url_succeed'] = '成功清空一个节点采集的内容！';
$_LANG['delete_url_history_succeed'] = '成功删除指定的网址内容！';
$_LANG['delete_all_succeed'] = '成功清除所有内容!';


$_LANG['all_succeed'] = '完成当前下载任务！';
$_LANG['export_succeed'] = '完成所有数据导入！';
$_LANG['progress'] = "完成线程 %d 的：%d %%，继续执行任务...";
$_LANG['export_progress'] = "完成 {%d} %% 导入，继续执行操作...";
$_LANG['trash_progress'] = "完成 {%d} %% 垃圾删除，继续执行操作...";

$_LANG['truncate_succed'] = '清空所有已下载内容成功！';
$_LANG['delete_trash_succed'] = '删除所有垃圾图片成功！';
$_LANG['notfound_down'] = '没发现可下载的内容！';
$_LANG['confirm_down']= "本操作会检测并下载‘<a href='col_url.php?act=list'><u>临时内容</u></a>’中所有未下载的内容，是否继续？";
$_LANG['confirm_yes']= "是的，我要继续！";
$_LANG['confirm_no']= "否，我要查看临时内容。";
?>