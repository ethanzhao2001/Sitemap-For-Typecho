<?php

/**
 * 自动生成 Sitemap 站点地图
 * 
 * @package Sitemap
 * @author 呆小萌
 * @version 1.3.2
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
        Helper::addRoute('sitemap-api', '/sitemap-api', 'Sitemap_API', 'action');
        Helper::addRoute('sitemap', '/sitemap.xml', 'Sitemap_XML', 'action');
        Helper::addPanel(1, 'Sitemap/Push.php', '推送百度', '推送百度', 'administrator');
        require_once("Action.php"); //引入
        update('activate', 'web'); //激活xml
        Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishPublish = array('Sitemap_Plugin', 'auto');
        Typecho_Plugin::factory('Widget_Contents_Page_Edit')->finishPublish = array('Sitemap_Plugin', 'auto');
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
        Helper::removeRoute('sitemap-api');
        Helper::removeRoute('sitemap');
        Helper::removePanel(1, 'Sitemap/Push.php');
        $dir = __TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/Sitemap/sitemap';
        unlink($dir);
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
        if (isset($_GET['action']) && $_GET['action']=== 'update_sitemap') {
            self::update_sitemap();
        }
        $sitemap_cachetime = new Typecho_Widget_Helper_Form_Element_Text('sitemap_cachetime', NULL, '7', _t('Sitemap 缓存时间'), '单位（天）');
        $form->addInput($sitemap_cachetime);
        $baidu_url = new Typecho_Widget_Helper_Form_Element_Text('baidu_url', NULL, NULL, _t('百度推送接口 URL'), '请在<a href="https://ziyuan.baidu.com/">百度搜索资源平台</a>查看，目前仅支持普通收录');
        $form->addInput($baidu_url);
        $web_token = new Typecho_Widget_Helper_Form_Element_Text('api_token', NULL, Typecho_Common::randString(32), _t('API token'), '自动生成无需修改，留空则不开启API功能<br>' . Helper::options()->index . '/sitemap-api?sitemap=[update]&push=[main/all/new]&token=[API_TOKEN]');
        $form->addInput($web_token);
        $AutoPush = new Typecho_Widget_Helper_Form_Element_Radio('AutoPush', array(0 => _t('不开启'), 1 => _t('开启')), 0, _t('自动推送文章'), '发布文章自动推送当前文章，自定义文章路径需使用wordpress风格，或其他带有{slug}的路径');
        $form->addInput($AutoPush);
        $AutoSitemap = new Typecho_Widget_Helper_Form_Element_Radio('AutoSitemap', array(0 => _t('不开启'), 1 => _t('开启')), 0, _t('自动更新Sitemap'), '发布文章更新Sitemap，可能降低发布速度不推荐开启');
        $form->addInput($AutoSitemap);
        $PluginLog = new Typecho_Widget_Helper_Form_Element_Radio('PluginLog', array(0 => _t('不开启'), 1 => _t('开启')), 0, _t('插件日志'), '将推送日志和Sitemap更新日志存入插件目录下');
        $form->addInput($PluginLog);
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
        require_once("Action.php"); //引入
        update('update', 'web'); //更新xml
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

    /**
     * 自动推送
     * @param $contents 文章内容
     * @param $class 调用接口的类
     * @throws Typecho_Plugin_Exception
     */
    public static function auto($contents, $class)
    {
        //如果文章属性为隐藏或滞后发布
        if( 'publish' != $contents['visibility'] || $contents['created'] > time()){
            return;
        }
        //获取文章类型
        $type = $contents['type'];
        //获取路由信息
        $routeExists = (NULL != Typecho_Router::get($type)); 
        //生成永久连接
        $path_info = $routeExists ? Typecho_Router::url($type, $contents) : '#';
        $url = Typecho_Common::url($path_info, Helper::options()->index);
        $urls = array(
            $url,
        );
        //检查自动推送文章开关
        if (Helper::options()->plugin('Sitemap')->AutoPush == 1) {
            $api = Helper::options()->plugin('Sitemap')->baidu_url;
            $ch = curl_init();
            $options =  array(
                CURLOPT_URL => $api,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => implode("\n", $urls),
                CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
            );
            curl_setopt_array($ch, $options);
            $result_json = curl_exec($ch);
            $result = json_decode($result_json, true);
            //获取时间
            $time = date('Y-m-d H:i:s', time());
            //写入日志
            $log = '【' . $time . '】' . 'auto' . '成功推送' . $result['success'] . '条，今日剩余可推送' . $result['remain'] . '条' . "\n" . $url . "\n";
            if (Helper::options()->plugin('Sitemap')->baidu_url == null) {
                $log = '【' . $time . '】' . 'auto' . '推送失败，未填写百度推送接口 URL' ;
            } else {
                $api = Helper::options()->plugin('Sitemap')->baidu_url;
            }
            if (Helper::options()->plugin('Sitemap')->PluginLog == 1) {
                file_put_contents(__TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/Sitemap/log.txt', $log, FILE_APPEND);
            }
        }

        //检查自动更新Sitemap开关
        if (Helper::options()->plugin('Sitemap')->AutoSitemap == 1) {
            //获取时间
            $time = date('Y-m-d H:i:s', time());
            //更新sitemap
            require_once("Action.php");
            update('update', 'auto');
        }
    }
}
