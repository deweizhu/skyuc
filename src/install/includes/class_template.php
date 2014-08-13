<?php

/**
 * SKYUC! 模板类
 * ============================================================================
 * 版权所有 (C) 2012 天空网络，并保留所有权利。
 * 网站地址: http://www.skyuc.com
 * ----------------------------------------------------------------------------
 * This is NOT a freeware, use is subject to license terms
 * ============================================================================
*/

if (!defined('SKYUC_AREA'))
{
    die('Hacking attempt');
}

class template
{
    /**
    * 用来存储变量的空间
    *
    * @access  private
    * @public     array      $vars
    */
    public $vars = array();

   /**
    * 模板存放的目录路径
    *
    * @access  private
    * @public     string      $path
    */
    public $path = '';

    /**
     * 构造函数
     *
     * @access  public
     * @param   string       $path
     * @return  void
     */
    function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * 模拟smarty的assign函数
     *
     * @access  public
     * @param   string       $name    变量的名字
     * @param   mix           $value   变量的值
     * @return  void
     */
    function assign($name, $value)
    {
        $this->vars[$name] = $value;
    }

    /**
     * 模拟smarty的fetch函数
     *
     * @access  public
     * @param   string       $file   文件相对路径
     * @return  string      模板的内容(文本格式)
     */
    function fetch($file)
    {
        extract($this->vars);
        ob_start();
        include($this->path . $file);
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }

    /**
     * 模拟smarty的display函数
     *
     * @access  public
     * @param   string       $file   文件相对路径
     * @return  void
     */
    function display($file)
    {
        echo $this->fetch($file);
    }
}

?>