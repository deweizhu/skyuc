<?php

/**
 * SKYUC! 會員賬號管理語言文件
 * ============================================================================
 * 版權所有 (C) 2012 天空網絡，並保留所有權利。
 * 網站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
*/
/* 列表頁面 */
$_LANG['label_user_name'] = '會員名稱';
$_LANG['label_user_type'] = '會員類型';

$_LANG['no_day'] = '天數為零';
$_LANG['no_count'] = '點數為零';
$_LANG['no_money'] = '餘額為零';
$_LANG['no_point'] = '積分為零';

$_LANG['intro_type'] = '過濾器';
$_LANG['is_day'] = '計時會員';
$_LANG['is_count'] = '計點會員';
$_LANG['is_validated'] = '已驗證郵箱';
$_LANG['no_validated'] = '未驗證郵箱';


$_LANG['view_order'] = '查看訂單';
$_LANG['view_deposit'] = '查看賬目明細';

$_LANG['username'] = '會員名稱';
$_LANG['email'] = '郵件地址';
$_LANG['reg_date'] = '註冊時間';
$_LANG['lastactivity'] = '最近活動時間';
$_LANG['lastvisit'] = '上次登錄時間';
$_LANG['playcount'] = '觀看影片數量';
$_LANG['minute'] = '線上時長';
$_LANG['format_minute'] = '%d 分鐘';
$_LANG['format_hour'] = '%d 小時';
$_LANG['last_ip'] = '上次登錄ＩＰ';
$_LANG['visit_count'] = '登錄次數';
$_LANG['button_remove'] = '刪除會員';
$_LANG['users_edit'] = '編輯會員賬號';
$_LANG['goto_list'] = '返回會員賬號列表';
$_LANG['username_empty'] = '會員名稱不能為空！';

/* 表單相關語言項 */
$_LANG['password'] = '登錄密碼';
$_LANG['confirm_password'] = '確認密碼';
$_LANG['newpass'] = '新密碼';
$_LANG['question'] = '密碼提示問題';
$_LANG['answer'] = '密碼提示問題答案';
$_LANG['label_gender'] = '性別';
$_LANG['birthday'] = '出生日期';
$_LANG['gender'][0] = '保密';
$_LANG['gender'][1] = '男';
$_LANG['gender'][2] = '女';
$_LANG['label_pay_point'] = '消費積分';
$_LANG['label_user_money'] = '賬戶餘額';
$_LANG['label_unit_date'] = '到期時間';
$_LANG['label_user_point'] = '剩餘點數';
$_LANG['user_rank'] = '會員等級';
$_LANG['not_rank'] = '沒有等級';
$_LANG['label_qq'] = 'QQ';
$_LANG['label_msn'] = 'MSN';
$_LANG['label_phone'] = '電話';
$_LANG['other_firstname'] = '真實姓名';
$_LANG['other_referrer'] = '推薦人';




$_LANG['view_detail_account'] = '查看賬戶明細';

$_LANG['notice_pay_point'] = '消費積分是一種站內貨幣，允許會員在購買點播權限時支付一定比例的積分。';;
$_LANG['notice_user_money'] = '會員在站內預留下的金額';
$_LANG['notice_unit_date'] = '僅對計時會員有效，超過此日期會員將無法點播影片。';
$_LANG['notice_user_point'] = '僅對計點會員有效，當此點數小於影片所需點數會員將無法點播影片。';

/* 提示信息 */
$_LANG['username_exists'] = '已經存在一個相同的會員名。';
$_LANG['email_exists'] = '該郵件地址已經存在。';
$_LANG['edit_user_failed'] = '修改會員資料失敗。';
$_LANG['invalid_email'] = '輸入了非法的郵件地址。';
$_LANG['update_success'] = '編輯會員信息已經成功。';
$_LANG['remove_confirm'] = '您確定要刪除該會員賬號嗎？';
$_LANG['remove_order_confirm'] = '該會員賬號已經有訂單存在，刪除該會員賬號的同時將清除訂單數據。<br />您確定要刪除嗎？';
$_LANG['remove_order'] = '是，我確定要刪除會員賬號及其訂單數據';
$_LANG['remove_cancel'] = '不，我不想刪除該會員賬號了。';
$_LANG['remove_success'] = '會員賬號 %s 已經刪除成功。';
$_LANG['add_success'] = '會員賬號 %s 已經添加成功。';
$_LANG['batch_remove_success'] = '已經成功刪除了 %d 個會員賬號。';
$_LANG['no_select_user'] = '您現在沒有需要刪除的會員！';

/* JS 語言項 */
$_LANG['js_languages']['no_username'] = '沒有輸入會員名。';
$_LANG['js_languages']['invalid_email'] = '沒有輸入郵件地址或者輸入了一個無效的郵件地址。';
$_LANG['js_languages']['no_password'] = '沒有輸入登錄密碼。';
$_LANG['js_languages']['no_confirm_password'] = '沒有輸入確認密碼。';
$_LANG['js_languages']['password_not_same'] = '輸入的密碼和確認密碼不一致。';
$_LANG['js_languages']['invalid_pay_point'] = '消費積分數不是一個整數。';
?>