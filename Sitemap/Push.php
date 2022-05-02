<?php
include 'common.php';
include 'header.php';
include 'menu.php';

$stat = Typecho_Widget::widget('Widget_Stat');

$user = Typecho_Widget::widget('Widget_User');
if (!$user->pass('administrator')) {
    die('未登录用户!');
}
if ($_GET['action'] === 'update_sitemap') {
    require_once("Action.php");
    update('update','web');
    header("location:" . Typecho_Common::url('/extending.php?panel=Sitemap%2FPush.php', Helper::options()->adminUrl));
}

?>

<head>
    <style type="text/css">
        .description {
            margin: .5em 0 1em;
            color: #999;
            font-size: .92857em;
        }
    </style>
</head>
<div class="main">
    <div class="body container">
        <div class="typecho-page-title">
            <h2>推送百度搜索资源平台</h2>
            <p><button onclick="window.location.href='<?php echo Typecho_Common::url('/extending.php?panel=Sitemap%2FPush.php&action=update_sitemap', Helper::options()->adminUrl)?>'" class="btn primary"><?php _e('更新 sitemap.xml'); ?></button></p>
            <button onclick="window.location.href='<?php echo Typecho_Common::url('/extending.php?panel=Sitemap%2FPush.php&action=baidu_typecho', Helper::options()->adminUrl)?>'" class="btn primary"><?php _e('推送核心页面'); ?></button>
            <p class="description">首页、分类、独立页面</p>
            <button onclick="window.location.href='<?php echo Typecho_Common::url('/extending.php?panel=Sitemap%2FPush.php&action=baidu_archive_all', Helper::options()->adminUrl)?>'" class="btn primary"><?php _e('推送全部文章'); ?></button>
            <p class="description">所有文章的URL</p>
            <button onclick="window.location.href='<?php echo Typecho_Common::url('/extending.php?panel=Sitemap%2FPush.php&action=baidu_archive', Helper::options()->adminUrl)?>'" class="btn primary"><?php _e('推送最新文章'); ?></button>
            <p class="description">最新20篇文章的URL</p>
            <?php
            if ($_GET['action'] === 'baidu_typecho') {
                require_once("Action.php");
                echo '<p style="color:green">稍后自动返回页面</p>';
                echo '<p>' . submit('typecho','web') . '</p>';
                header("Refresh:10;url=" . Typecho_Common::url('/extending.php?panel=Sitemap%2FPush.php', Helper::options()->adminUrl));
            }
            if ($_GET['action'] === 'baidu_archive_all') {
                require_once("Action.php");
                echo '<p style="color:green">稍后自动返回页面</p>';
                echo '<p>' . submit('archive_all','web') . '</p>';
                header("Refresh:10;url=" . Typecho_Common::url('/extending.php?panel=Sitemap%2FPush.php', Helper::options()->adminUrl));
            }
            if ($_GET['action'] === 'baidu_archive') {
                require_once("Action.php");
                echo '<p style="color:green">稍后自动返回页面</p>';
                echo '<p>' . submit('archive','web') . '</p>';
                header("Refresh:10;url=" . Typecho_Common::url('/extending.php?panel=Sitemap%2FPush.php', Helper::options()->adminUrl));
            }
            ?>
        </div>
    </div>
</div>
<?php
include 'copyright.php';
include 'common-js.php';
include 'footer.php';
?>