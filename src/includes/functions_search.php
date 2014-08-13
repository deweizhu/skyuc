<?php
if (! isset($GLOBALS['skyuc']->db)) {
    exit();
}
// #############################################################################
/**
 * remove common syntax errors in search query string
 * 移除搜索词语中常见语法错误
 *
 * @param	string $query	搜索词语
 *
 * @return	string
 */
function sanitize_search_query ($query)
{
    $qu_find = array('/\s+(\s*OR\s+)+/si', // remove multiple OR strings
'/^\s*(OR|AND|NOT|-)\s+/siU',  // remove 'OR/AND/NOT/-' from beginning of query
    '/\s+(OR|AND|NOT|-)\s*$/siU', // remove 'OR/AND/NOT/-' from end of query
'/\s+(-|NOT)\s+/si', // remove trailing whitespace on '-' controls and translate 'not'
'/\s+OR\s+/siU',  // capitalize ' or '
    '/\s+AND\s+/siU', // remove ' and '
'/\s+(-)+/s', // remove ----word
'/\s+/s'); // whitespace to single space
    $qu_replace = array(' OR ', // remove multiple OR strings
'', // remove 'OR/AND/NOT/-' from beginning of query
'', // remove 'OR/AND/NOT/-' from end of query
' -', // remove trailing whitespace on '-' controls and translate 'not'
' OR ', // capitalize 'or '
' ', // remove ' and '
' -', // remove ----word
' '); // whitespace to single space
    $query = trim(preg_replace($qu_find, $qu_replace, " $query "));
    // show error if query logic contains (apple OR -pear) or (-apple OR pear)
    if (strpos($query, ' OR -') !== false or
     preg_match('/ -\w+ OR /siU', $query, $syntaxcheck)) {
        return $query;
    } else 
        if (! empty($query)) {
            // check that we have some words that are NOT boolean controls
            $boolwords = array('AND', 'OR', 'NOT', '-AND', '-OR', 
            '-NOT');
            foreach (explode(' ', strtoupper($query)) as $key => $word) {
                if (! in_array($word, $boolwords)) {
                    // word is good - return the query
                    return $query;
                }
            }
        }
    // no good words found - show no search terms error
    return $query;
}
/**
 * Start searchtextstrip
 *
 * @param	string $text
 * @return	string
 */
function fetch_index_text ($text)
{
    static $find, $replace;
    
    $search = array(",", "/", "\\", ".", ";", ":", "\"", "!", "~", "`", "^", 
    "(", ")", "?", "？", "-", "\t", "\n", "'", "<", ">", "\r", "\r\n", "$", "&", 
    "%", "#", "@", "+", "=", "{", "}", "[", "]", "：", "）", "（", "．", "。", "，", 
    "！", "；", "“", "”", "‘", "’", "〔", "〕", "、", "—", "　", "《", "》", "－", "…", 
    "【", "】", "|", "&hellip;");
    $text = str_replace($search, ' ', $text);
  
    include_once( DIR.'/includes/class_search.php');
    $nt = new normalizeText(4,false);
    $text = $nt->normalize($text);
    
    if (! is_array($find)) {
        $find = array('#[()"\'!\#{};<>]|\\\\|:(?!//)#s',  // allow through +- for boolean operators and strip colons that are not part of URLs
        '#([.,?&/_]+)( |\.|\r|\n|\t)#s', // \?\&\,
'#\s+(-+|\++)+([^\s]+)#si',  // remove leading +/- characters
        '#(\s?\w*\*\w*)#s', // remove words containing asterisks
'#[ \r\n\t]+#s'); // whitespace to space
        $replace = array('', // allow through +- for boolean operators and strip colons that are not part of URLs
' ', // \?\&\,
' \2', // remove leading +/- characters
'', // remove words containing asterisks
' '); // whitespace to space
    }
    $text = strip_tags($text); // clean out HTML as it's probably not going to be indexed well anyway
    // use regular expressions above
    $text = preg_replace($find, $replace, $text);
    return trim(strtolower($text));
}

/**
 * 获得指定分类下所有底层分类的ID，用于全文搜索
 *
 * @access  public
 * @param   integer     $cat        指定的分类ID
 * @return  string
 */
function get_contenttypeid ($cat = 0)
{
    return 'searchcore_text.cat_id ' .
     db_create_in(
    array_unique(
    array_merge(array($cat), array_keys(get_cat_list($cat, 0, false)))));
}
//add search index
function add_search_index ($param = array(), $update = false, $onlytag = false)
{
    if (empty($param) or empty($param['show_id'])) {
        return false;
    }
    $detail = '';
    if ($onlytag === true && $param['tag'] != '') {
        $fields['keywordtext'] = fetch_index_text($param['tag']);
        $sql = 'UPDATE ' . TABLE_PREFIX . 'searchcore_text SET ' .
         " keywordtext = CONCAT(`keywordtext` , ' " .
         $GLOBALS['db']->escape_string_like($fields['keywordtext']) . " ') " .
         ' WHERE searchid= ' . $param['show_id'];
        $GLOBALS['db']->query($sql);
        return;
    }
    if ($GLOBALS['skyuc']->options['dbsearch_full']) {
        $detail = $param['detail'];
    } 
    
    $fields = array();
    $fields['title'] = fetch_index_text(
    $param['title'] .' '. $param['title_alias'] .' '. $param['title_english']);
    $fields['keywordtext'] = fetch_index_text(
    $param['title'] .' '. $param['title_alias'] .' '. $param['title_english'] .
     ' '. $param['actor'] .' '. $param['director'] .' '. $param['tag'].' '. $detail);
    if ($update) {
        $sql = 'UPDATE ' . TABLE_PREFIX . 'searchcore_text SET ' . ' cat_id = ' .
         $param['cat_id'] . ', ' . " keywordtext = '" .
         $GLOBALS['db']->escape_string_like($fields['keywordtext']) . "', " .
         " title = '" . $GLOBALS['db']->escape_string_like($fields['title']) .
         "' " . ' WHERE searchid= ' . $param['show_id'];
        $GLOBALS['db']->query($sql);
        return;
    } else {
        $sql = 'INSERT INTO ' . TABLE_PREFIX .
         'searchcore_text (`searchcoreid`, `searchid`, `cat_id`, `keywordtext`, `title`) ' .
         " VALUES (NULL, " . $param['show_id'] . "," . $param['cat_id'] . ", '" .
        $GLOBALS['db']->escape_string_like($fields['keywordtext']) . "', '" .
        $GLOBALS['db']->escape_string_like($fields['title']) . "')";
        $GLOBALS['db']->query($sql);
        return;
    }
     //end
}
