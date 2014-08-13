<?php
/**
 * SKYUC! 管理中心採集管理語言文件
 * ============================================================================
 * 版權所有 (C) 2012 天空網絡，並保留所有權利。
 * 網站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
*/

/*------------------------------------------------------ */
//-- 節點列表
/*------------------------------------------------------ */
$_LANG['gathername'] = '節點名稱';
$_LANG['cat_id'] = '入庫欄目';
$_LANG['lasttime'] = '最後採集';
$_LANG['savetime'] = '加入時間';
$_LANG['language'] = '編碼';
$_LANG['notes'] = '網址數';
$_LANG['col_notes'] ='種子網址數';


$_LANG['addconote'] = '添加新節點';
$_LANG['importrule'] = '導入規則';
$_LANG['exportrule'] = '導出規則';
$_LANG['collection'] = '採集';
$_LANG['exportdown'] = '導出數據';
$_LANG['editnote'] = '更改';
$_LANG['testrule'] = '測試';
$_LANG['viewdown'] = '查看已下載';
$_LANG['copynote'] = '複製';
$_LANG['clearnote'] = '清空';
$_LANG['delnote'] = '刪除';

$_LANG['select_all'] = '全選';
$_LANG['delete_url'] = '僅刪除網址';
$_LANG['clear_content'] = '僅清空內容';
$_LANG['delete_url_history'] = '刪除網址及歷史記錄';
$_LANG['delete_trash'] = '刪除垃圾圖片';

$_LANG['addconote_ok'] = '添加新節點成功！';
$_LANG['continue_add'] = '繼續添加新節點！';
$_LANG['editconote_ok'] = '修改節點成功！';
$_LANG['back_conote_list'] = '返回節點管理列表';
$_LANG['no_collection'] = '從未採集';

//採集
$_LANG['clearall'] = '清空臨時內容';
$_LANG['collection_note'] = '採集指定節點';
$_LANG['all_node']= '所有節點';
$_LANG['collection_title'] = '監控式採集';
$_LANG['use_monitor'] = '沒指定採集節點，將使用檢測新內容採集模式！';
$_LANG['no_seed'] = '沒有記錄或從來沒有採集過這個節點！';
$_LANG['seeds'] = "共有 %d 個歷史種子網址！<a href='javascript:SubmitNew();'>[<u>更新種子網址，並採集</u>]</a>";
$_LANG['pagesize'] = '每頁採集';
$_LANG['threadnum'] = '條，線程數';
$_LANG['sptime'] = '間隔時間';
$_LANG['second'] = '秒（防刷新的站點需設置）';
$_LANG['islisten'] = '附加選項';

$_LANG['islisten_no'] = '監控採集模式(檢測當前或所有節點是否有新內容)';
$_LANG['islisten_on'] = '下載種子網址的未下載內容';
$_LANG['islisten_re'] = '重新下載全部內容';
$_LANG['dostart'] ='開始採集網頁';
$_LANG['viewnotes'] ='查看種子網址';
$_LANG['viewdown'] = '查看已下載';
$_LANG['noteurl'] = '節點的種子網址';
$_LANG['divmin'] = '縮小';
$_LANG['divmax'] = '增大';

//採集內容列表
$_LANG['download_no'] = '未下載';
$_LANG['download_yes'] = '已下載';
$_LANG['exdata_no'] = '未導出';
$_LANG['exdata_yes'] = '已導出';
$_LANG['url_title'] = '內容標題';
$_LANG['dtime'] = '獲取時間';
$_LANG['isdown'] = '下載';
$_LANG['isexport'] = '導出';
$_LANG['sourcepage'] = '來源網頁';
$_LANG['sourceurl'] = '[源網址]';

//測試規則
$_LANG['collection_demo'] = '採集內容預覽';
$_LANG['collection_test'] = '測試節點';
$_LANG['listurl_error'] = "配置中指定列表的網址錯誤！\r\n";
$_LANG['firsturl_error'] = "讀取其中的一個網址： %d 時失敗！\r\n";
$_LANG['found_url'] = "按指定規則在 %s 發現的網址：\r\n";
$_LANG['parsehtml_error'] = "分析網頁的HTML時失敗！\r\n";
$_LANG['no_test_url'] = '沒有遞交測試的網址！';
$_LANG['test_list'] = '列表測試信息';
$_LANG['test_rule'] = '網頁規則測試';
$_LANG['test_url'] = '測試網址';
$_LANG['url_demo'] = '　(本網址信息未下載到本地，以下是預覽信息)';

/* 規則信息 */
$_LANG['general-tab'] = '基本信息';
$_LANG['url-tab'] ='內容規則';
$_LANG['url-tab'] ='內容規則';
$_LANG['charset'] = '頁面編碼';
$_LANG['player'] = '播放器';
$_LANG['server'] = '伺服器';
$_LANG['split_type'] = '區域匹配模式';
$_LANG['cosort'] = '內容導入順序';
$_LANG['split_regex'] = '正則表達式';
$_LANG['split_string'] = '字符串';
$_LANG['cosort_asc']= '與目標站一致';
$_LANG['cosort_desc']= '與目標站相反';
$_LANG['runphp_false'] = '使用規則獲取地址';
$_LANG['runphp_true'] = '使用PHP處理接口獲取地址';
$_LANG['generate_list'] = '按規則生成列表網址';
$_LANG['generate_note'] = '(符合特定序列的列表網址)';
$_LANG['varstart'] = '<span style="color:#F00"> &nbsp[page]&nbsp;</span>從';
$_LANG['varend'] = '到';
$_LANG['addv'] = '每頁遞增';
$_LANG['varpage'] = ' (填寫頁碼或規律遞增數字)';
$_LANG['generate_example'] = '（如：　http://www.skyuc.com/list.php?page=[page]，如果不能匹配所有網址，可以在手工指定網址的地方輸入要追加的網址）';
$_LANG['manual_list'] = '手工指定列表網址';
$_LANG['manual_note'] = '(如果列表網址不規範,你可以手工指定,一行為一個列表頁)';
$_LANG['pagerepad'] = '頁面過濾';
$_LANG['pagerepad_note'] = '(目標網頁有妨礙你採集的信息可以指定規則去除它。)<br>格式：  <INPUT TYPE="text" NAME="pad" VALUE="{suc:trim}廣告開始(.*)廣告結束{/suc}" class="text">';
$_LANG['detail_url'] = '列表區域內網址篩選';
$_LANG['detail_need'] = '網址必需包含';
$_LANG['detail_cannot'] = '網址不能包含';
$_LANG['detail_list'] = '限定列表HTML範圍';
$_LANG['detail_note'] = '( 格式：列表開始 <span style="color:#F00"> &nbsp;[!--me--] &nbsp;</span> 列表結束 。如果用限定列表HTML範圍無法正確獲得需要的網址，可以下面進行再次篩選。)';
$_LANG['note_rule'] = '↓內容匹配規則（格式：內容開始 <span style="color:#F00"> &nbsp;[!--me--]&nbsp; </span> 內容結束）';
$_LANG['note_rule_filter'] = '↓內容過濾規則（格式：{suc:trim}...{/suc}）';
$_LANG['title'] = '影片名稱';
$_LANG['actor'] = '領銜主演';
$_LANG['director'] = '導　　演';
$_LANG['image'] = '影片海報';
$_LANG['pubdate'] = '上映日期';
$_LANG['status'] = '影片狀態';
$_LANG['area'] = '出品地區';
$_LANG['lang'] = '配音語言';
$_LANG['detail'] = '簡介內容';
$_LANG['show_url'] = '點播地址';
$_LANG['savepic'] = '保存遠程圖片到本地';
$_LANG['selectrule'] = '常用規則';
$_LANG['runphp'] = 'PHP處理接口';
$_LANG['runphp_desc'] = '用於獲取影片地址的PHP代碼。<BR><BR>@body 表示原始網頁HTML代碼<BR>@me 表示當前標記值和最終結果 ';


/* 採集內容導出數據*/
$_LANG['exportdata'] = '採集內容導出';
$_LANG['totalcol'] = '節點信息';
$_LANG['totalnote'] = '本節點共有 %d 條數據';
$_LANG['progress_status'] = '進行狀態';
$_LANG['export_pagesize'] = '每批導入';
$_LANG['onlytitle'] = '跳過名稱重複的影片';
$_LANG['updateurl'] = '僅更新重名影片的地址';
$_LANG['updateall'] = '更新重名影片所有內容';
$_LANG['show_txt_pre'] = '第';
$_LANG['show_txt_ext'] = '集';

/* 導出或導入規則 */
$_LANG['importrule_desc'] = '請在下面輸入你要導入的文本配置：(建議用base64編碼[支持不編碼的規則，但不兼容舊版規則])';
$_LANG['exportrule_desc'] = '以下為規則 [ %s ]  的文本配置，你可以共享給你的朋友！';
$_LANG['export_text'] = '導出為普通格式';
$_LANG['export_base64'] = '導出為 base64 格式';
$_LANG['import_error_rule']= "你導入的規則不是 SKYUC 的採集規則，或者規則不完整！";
$_LANG['import_error_base64rule'] = '該規則不合法，Base64格式的採集規則為：BASE64:base64編碼後的配置:END !';
$_LANG['import_error_str'] = '配置字符串有錯誤！';



//提示消息
$_LANG['notice'] ='例子：比如採集的影片名稱中含有[01]這樣的序號，我們可以在過濾規則中填寫 {suc:trim}\[(.*)\]{/suc} 用於去除這樣信息，從而得到一個完整的影片名稱。又或者影片信息中沒有配音語言這一項，我們可以直接在「內容匹配規則」中輸入「中文字幕」等信息。';
$_LANG['selectnote'] = '請選擇一個節點！';
$_LANG['information'] = 'SKYUC 提示信息！';
$_LANG['pleaselink'] = '如果你的瀏覽器沒反應，請點擊這裡...';
$_LANG['note_islisten_on'] = "你指定的模式為：<font color='red'>[下載種子網址中未下載內容]</font>，<br />使用這個模式節點必須已經有種子網址，否則請使用其它模式！";
$_LANG['note_downloaded'] = '檢測節點正常，現轉向網頁採集...';
$_LANG['go_getsource_url'] = '已獲得所有種子網址，轉向網頁採集...';
$_LANG['notfound_new'] = '在這節點中沒發現有新內容....';
$_LANG['finish_check'] = '完成所有節點檢測....';
$_LANG['checked_col'] = '已檢測節點( {%d} )，繼續下一個節點...';
$_LANG['limitlist'] = '採集列表剩餘：%d 個頁面，繼續採集...';
$_LANG['notfound_url'] = '按指定規則沒找到任何鏈接！';
$_LANG['no_title'] = '無標題，可能是圖片鏈接';
$_LANG['get_list_faild'] = '獲取列表網址失敗，無法完成採集！';
$_LANG['notfound_all'] = '獲取到的網址為零：可能是規則不對或沒發現新內容！';

$_LANG['confirm'] = '你確定要刪除這個節點嗎?';
$_LANG['delete_succeed'] ='成功刪除一個節點！';
$_LANG['copy_succeed'] = '成功複製一個節點！';
$_LANG['delete_url_succeed'] = '成功清空一個節點採集的內容！';
$_LANG['delete_url_history_succeed'] = '成功刪除指定的網址內容！';
$_LANG['delete_all_succeed'] = '成功清除所有內容!';


$_LANG['all_succeed'] = '完成當前下載任務！';
$_LANG['export_succeed'] = '完成所有數據導入！';
$_LANG['progress'] = "完成線程 %d 的：%d %%，繼續執行任務...";
$_LANG['export_progress'] = "完成 {%d} %% 導入，繼續執行操作...";
$_LANG['trash_progress'] = "完成 {%d} %% 垃圾刪除，繼續執行操作...";

$_LANG['truncate_succed'] = '清空所有已下載內容成功！';
$_LANG['delete_trash_succed'] = '刪除所有垃圾圖片成功！';
$_LANG['notfound_down'] = '沒發現可下載的內容！';
$_LANG['confirm_down']= "本操作會檢測並下載『<a href='col_url.php?act=list'><u>臨時內容</u></a>』中所有未下載的內容，是否繼續？";
$_LANG['confirm_yes']= "是的，我要繼續！";
$_LANG['confirm_no']= "否，我要查看臨時內容。";
?>