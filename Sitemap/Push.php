<?php
include 'header.php';
include 'menu.php';

if (isset($_GET['action']) && $_GET['action'] === 'update_sitemap') {
    require_once("Action.php");
    update('update', 'web');
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
            <h1>百度搜索资源平台</h1>
            <hr>
            <p style="color: green;">常规更新推送</p>
            <button onclick="window.location.href='<?php echo Typecho_Common::url('/extending.php?panel=Sitemap%2FPush.php&action=update_sitemap', Helper::options()->adminUrl) ?>'" class="btn primary"><?php _e('更新 sitemap.xml'); ?></button>
            <p class="description">更新网站sitemap文件</p>

            <button onclick="window.location.href='<?php echo Typecho_Common::url('/extending.php?panel=Sitemap%2FPush.php&action=baidu_archive', Helper::options()->adminUrl) ?>'" class="btn primary"><?php _e('推送最新文章'); ?></button>
            <p class="description">推送最新<?php echo Helper::options()->plugin('Sitemap')->NumberOfLatestArticles; ?>篇文章</p>
            <hr>
            <h1>暴力推送</h1>
            <p style="color: red;">不建议经常使用</p>
            <button onclick="window.location.href='<?php echo Typecho_Common::url('/extending.php?panel=Sitemap%2FPush.php&action=baidu_typecho', Helper::options()->adminUrl) ?>'" class="btn primary"><?php _e('推送核心页面'); ?></button>
            <p class="description">首页、分类、独立页面</p>
            <button onclick="window.location.href='<?php echo Typecho_Common::url('/extending.php?panel=Sitemap%2FPush.php&action=baidu_archive_all', Helper::options()->adminUrl) ?>'" class="btn primary"><?php _e('推送全部文章'); ?></button>
            <p class="description">所有文章的URL</p>

            <?php
            if (isset($_GET['action']) && $_GET['action'] === 'baidu_typecho') {
                require_once("Action.php");
                echo '<p style="color:green">稍后自动返回页面</p>';
                echo '<p>' . submit('typecho', 'web') . '</p>';
                header("Refresh:10;url=" . Typecho_Common::url('/extending.php?panel=Sitemap%2FPush.php', Helper::options()->adminUrl));
            }
            if (isset($_GET['action']) && $_GET['action'] === 'baidu_archive_all') {
                require_once("Action.php");
                echo '<p style="color:green">稍后自动返回页面</p>';
                echo '<p>' . submit('archive_all', 'web') . '</p>';
                header("Refresh:10;url=" . Typecho_Common::url('/extending.php?panel=Sitemap%2FPush.php', Helper::options()->adminUrl));
            }
            if (isset($_GET['action']) && $_GET['action'] === 'baidu_archive') {
                require_once("Action.php");
                echo '<p style="color:green">稍后自动返回页面</p>';
                echo '<p>' . submit('archive', 'web') . '</p>';
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