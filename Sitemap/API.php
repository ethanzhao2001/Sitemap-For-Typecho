<?php
class Sitemap_API extends Typecho_Widget implements Widget_Interface_Do
{
    public function action()
    {
        //检查api token
        if (Helper::options()->plugin('Sitemap')->api_token == null) {
            echo 'api closed';
            exit;
        }
        if (isset($_GET['token']) && $_GET['token'] === Helper::options()->plugin('Sitemap')->api_token) {
            require_once("Action.php");
            $array = array();
            if (isset($_GET['sitemap']) && $_GET['sitemap'] === 'update') {
                //add arr
                $arr = array(
                    'update' => update('update', 'api'),
                );
                $array = array_merge($array, $arr);
            }
            if (isset($_GET['push']) && $_GET['push'] === 'main') {
                //add arr
                $arr = array(
                    'push' => submit('typecho', 'api'),
                );
                $array = array_merge($array, $arr);
            }
            if (isset($_GET['push']) && $_GET['push'] === 'all') {
                //add arr
                $arr = array(
                    'push' => submit('archive_all', 'api'),
                );
                $array = array_merge($array, $arr);
            }
            if (isset($_GET['push']) && $_GET['push'] === 'new') {
                //add arr
                $arr = array(
                    'push' => submit('archive', 'api'),
                );
                $array = array_merge($array, $arr);
            }
            echo json_encode($array);
        } else {
            //返回错误
            echo 'token error';
        }
    }
}
?>