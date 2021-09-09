<?php
class Sitemap_XML extends Typecho_Widget implements Widget_Interface_Do
{
    public function action()
    {
        header("Content-Type: application/xml");
        $db = Typecho_Db::get();
        $Sitemap_Time = $db->select()->from('table.options')->where('name = ?', 'Sitemap_Time');
        $Sitemap_Time = $db->fetchAll($Sitemap_Time);
        $Sitemap_Time = $Sitemap_Time[0];
        $Sitemap_Time = $Sitemap_Time['value'];
        $cachetime = Helper::options()->plugin('Sitemap')->sitemap_cachetime;
        $cachetime = $cachetime * 86400;
        header('Cache-Control:max-age=' . $cachetime);
        if (time() - $Sitemap_Time > $cachetime) {
            require_once("Action.php");
            update('update');
        };
        $query = $db->select()->from('table.options')->where('name = ?', 'Sitemap');
        $result = $db->fetchAll($query);
        $result = $result[0];
        echo $result['value'];
    }
}
