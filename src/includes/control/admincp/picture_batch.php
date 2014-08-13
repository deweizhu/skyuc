<?php
// #######################################################################
// ######################## picture_batch.php 私有函数    #################
// #######################################################################
/**
 * 图片处理函数
 *
 * @access  public
 * @param   integer $page
 * @param   integer $page_size
 * @param   boolen  $thumb      是否生成缩略图
 * @param   boolen  $thumb_big  是否生成详情页缩略图
 * @param   boolen  $change     1 生成新图，删除旧图 0 用新图覆盖旧图
 * @param   boolen  $silent     是否执行能忽略错误
 * @param   string  $show_where   sql查询条件
 *
 * @return void
 */
function process_image ($page = 1, $page_size = 100, $thumb = 1, $thumb_big = 1, 
$change = 0, $silent = 1, $show_where = '')
{
    $sql = 'SELECT show_id, source, image, thumb FROM ' . TABLE_PREFIX . 'show' .
     " AS m WHERE source != ''" . $show_where .' ORDER by show_id DESC ';
    $sql = $GLOBALS['db']->query_limit($sql, $page_size, 
    ($page - 1) * $page_size);
    $res = $GLOBALS['db']->query_read($sql);
    while ($row = $GLOBALS['db']->fetch_array($res)) {
        $imagepath = DIR . '/' . $row['source'];
        if (!is_file($imagepath)) {
            continue;
        }
        // 详情页缩略图
        if ($thumb_big) {
            // 详情页缩略图的目录
            if (empty($row['image'])) {
                $dir = dirname($row['source']);
            } else {
                $dir = dirname($row['image']);
            }
            require_once (DIR . '/includes/class_upload.php');
            require_once (DIR . '/includes/class_image.php');
            $image = & Image::fetch_library($GLOBALS['skyuc']);
            $image->path = $dir;
            if (! $change) {
                //新生成图片使用新名称，并删除旧图片
                $image->filename = basename($row['image']);
            }
            
            $posterimage = make_thumb($image, $imagepath, 
            $GLOBALS['skyuc']->options['image_width'], 
            $GLOBALS['skyuc']->options['image_height'], false);
            if (is_array($posterimage)) {
                //出错返回
                $msg = sprintf($GLOBALS['_LANG']['error_pos'], 
                $row['show_id']) . "\n" .
                 $GLOBALS['_LANG'][$posterimage['error']];
                if ($silent) {
                    $GLOBALS['err_msg'][] = $msg;
                    continue;
                } else {
                    make_json_error($msg);
                }
            }
            if ($change || empty($row['image'])) {
                // 要生成新链接的处理过程
                if ($posterimage != $row['image']) {
                    $sql = 'UPDATE ' . TABLE_PREFIX . 'show' . " SET image = '" .
                     $GLOBALS['db']->escape_string($posterimage) .
                     "' WHERE show_id = '" . $row['show_id'] . "'";
                    $GLOBALS['db']->query_write($sql);
                    // 防止原图被删除
                    if ($row['image'] != $row['source']) {
                        @unlink(DIR . '/' . $row['image']);
                    }
                }
            }
        }
        // 缩略图
        if ($thumb) {
            // 详情页缩略图的目录
            if (empty($row['image'])) {
                $dir = dirname($row['source']) . '/';
            } else {
                $dir = dirname($row['thumb']) . '/';
            }
            require_once (DIR . '/includes/class_upload.php');
            require_once (DIR . '/includes/class_image.php');
            $image = & Image::fetch_library($GLOBALS['skyuc']);
            $image->path = $dir;
            if ($change == false) {
                //新生成图片使用新名称，并删除旧图片
                $image->filename = basename($row['thumb']);
            }

            $thumbimage = make_thumb($image, $imagepath, 
            $GLOBALS['skyuc']->options['thumb_width'], 
            $GLOBALS['skyuc']->options['thumb_height'], false);
            if (is_array($thumbimage)) {
                //出错返回
                $msg = sprintf($GLOBALS['_LANG']['error_pos'], 
                $row['show_id']) . "\n" .
                 $GLOBALS['_LANG'][$thumbimage['error']];
                if ($silent) {
                    $GLOBALS['err_msg'][] = $msg;
                    continue;
                } else {
                    make_json_error($msg);
                }
            }
            //更新数据库:start
            if ($change || empty($row['thumb'])) {
                // 要生成新链接的处理过程
                if ($thumbimage != $row['thumb']) {
                    $sql = 'UPDATE ' . TABLE_PREFIX . 'show' . " SET thumb = '" .
                     $GLOBALS['db']->escape_string($thumbimage) .
                     "' WHERE show_id = '" . $row['show_id'] . "'";
                    $GLOBALS['db']->query_write($sql);
                    // 防止原图被删除
                    if ($row['thumb'] != $row['source']) {
                        @unlink(DIR . '/' . $row['thumb']);
                    }
                }
            }
             //更新数据库:end
        }
    }
}
?>