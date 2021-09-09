<?php

/**
 * 自动生成 Sitemap 站点地图
 * 
 * @package Sitemap
 * @author 呆小萌
 * @version 1.1.0
 * @link https://www.zhaoyingtian.com/archives/93.html
 */
class Sitemap_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Helper::addRoute('sitemap', '/sitemap.xml', 'Sitemap_XML', 'action');
        Helper::addPanel(1, 'Sitemap/Push.php', '推送百度', '推送百度', 'administrator');
        require_once("Action.php");
        update('activate');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
        $db = Typecho_Db::get();
        $delete = $db->delete('table.options')->where('name = ?', 'Sitemap');
        $db->query($delete);
        $delete = $db->delete('table.options')->where('name = ?', 'Sitemap_Time');
        $db->query($delete);
        Helper::removeRoute('sitemap');
        Helper::removePanel(1, 'Sitemap/Push.php');
    }

    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        if ($_GET['action'] === 'update_sitemap') {
            self::update_sitemap();
        }
        $sitemap_cachetime = new Typecho_Widget_Helper_Form_Element_Text('sitemap_cachetime', NULL, '7', _t('Sitemap 缓存时间'), '单位（天）');
        $form->addInput($sitemap_cachetime);
        $baidu_url = new Typecho_Widget_Helper_Form_Element_Text('baidu_url', NULL, NULL, _t('百度推送接口 URL'), '请在<a href="https://ziyuan.baidu.com/">百度搜索资源平台</a>查看，目前仅支持普通收录');
        $form->addInput($baidu_url);
        $Btn = new Typecho_Widget_Helper_Form_Element_Submit();
        $Btn->value(_t('更新 sitemap.xml'));
        $Btn->description(_t('用于手动更新<a href="/sitemap.xml">sitemap.xml</a>'));
        $Btn->input->setAttribute('class', 'btn primary');
        $Btn->input->setAttribute('formaction', Typecho_Common::url('/options-plugin.php?config=Sitemap&action=update_sitemap', Helper::options()->adminUrl));
        $form->addItem($Btn);
    }
    /**
     * 更新 sitemap
     */
    public static function update_sitemap()
    {
        require_once("Action.php");
        update('update');
        header("location:" . Typecho_Common::url('/options-plugin.php?config=Sitemap', Helper::options()->adminUrl));
    }
    /**
     * 插件实现方法
     * 
     * @access public
     * @return void
     */
    public static function render()
    {
    }

    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }
}
