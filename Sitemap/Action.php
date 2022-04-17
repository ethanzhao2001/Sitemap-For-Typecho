<?php
function update($function)
{
	//更新sitemap.xml
	$db = Typecho_Db::get();
	$options = Typecho_Widget::widget('Widget_Options');
	$header = '<?xml version="1.0" encoding="utf-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";
	$footer = '</urlset>';
	//首页
	$index = Helper::options()->siteUrl;
	$index_result = $index_result . "\t<url>\n";
	$index_result = $index_result . "\t\t<loc>" . $index . "</loc>\n";
	$index_result = $index_result . "\t\t<changefreq>always</changefreq>\n";
	$index_result = $index_result . "\t\t<priority>1</priority>\n";
	$index_result = $index_result . "\t</url>\n";
	//独立页面
	$pages = $db->fetchAll($db->select()->from('table.contents')
		->where('table.contents.status = ?', 'publish')
		->where('table.contents.created < ?', $options->gmtTime)
		->where('table.contents.type = ?', 'page')
		->order('table.contents.created', Typecho_Db::SORT_DESC));
	foreach ($pages as $page) {
		$type = $page['type'];
		$routeExists = (NULL != Typecho_Router::get($type));
		$page['pathinfo'] = $routeExists ? Typecho_Router::url($type, $page) : '#';
		$page['permalink'] = Typecho_Common::url($page['pathinfo'], $options->index);
		$page_result = $page_result . "\t<url>\n";
		$page_result = $page_result . "\t\t<loc>" . $page['permalink'] . "</loc>\n";
		$page_result = $page_result . "\t\t<lastmod>" . date('Y-m-d', $page['modified']) . "</lastmod>\n";
		$page_result = $page_result . "\t\t<changefreq>always</changefreq>\n";
		$page_result = $page_result . "\t\t<priority>0.8</priority>\n";
		$page_result = $page_result . "\t</url>\n";
	}
	//分类
	$categorys = $db->fetchAll($db->select()->from('table.metas')
		->where('table.metas.type = ?', 'category'));
	foreach ($categorys as $category) {
		$type = $category['type'];
		$routeExists = (NULL != Typecho_Router::get($type));
		$category['pathinfo'] = $routeExists ? Typecho_Router::url($type, $category) : '#';
		$category['permalink'] = Typecho_Common::url($category['pathinfo'], $options->index);
		$category_result = $category_result . "\t<url>\n";
		$category_result = $category_result . "\t\t<loc>" . $category['permalink'] . "</loc>\n";
		$category_result = $category_result . "\t\t<changefreq>always</changefreq>\n";
		$category_result = $category_result . "\t\t<priority>0.5</priority>\n";
		$category_result = $category_result . "\t</url>\n";
	}
	//文章
	$archives = $db->fetchAll($db->select()->from('table.contents')
		->where('table.contents.status = ?', 'publish')
		->where('table.contents.created < ?', $options->gmtTime)
		->where('table.contents.type = ?', 'post')
		->order('table.contents.created', Typecho_Db::SORT_DESC));
	foreach ($archives as $archive) {
		$type = $archive['type'];
		$routeExists = (NULL != Typecho_Router::get($type));
		$archive['pathinfo'] = $routeExists ? Typecho_Router::url($type, $archive) : '#';
		$archive['permalink'] = Typecho_Common::url($archive['pathinfo'], $options->index);
		$archive_result = $archive_result . "\t<url>\n";
		$archive_result = $archive_result . "\t\t<loc>" . $archive['permalink'] . "</loc>\n";
		$archive_result = $archive_result . "\t\t<lastmod>" . date('Y-m-d', $archive['modified']) . "</lastmod>\n";
		$archive_result = $archive_result . "\t\t<changefreq>always</changefreq>\n";
		$archive_result = $archive_result . "\t\t<priority>0.8</priority>\n";
		$archive_result = $archive_result . "\t</url>\n";
	}
	//tag
	$tags = $db->fetchAll($db->select()->from('table.metas')
		->where('table.metas.type = ?', 'tag'));
	foreach ($tags as $tag) {
		$type = $tag['type'];
		$routeExists = (NULL != Typecho_Router::get($type));
		$tag['pathinfo'] = $routeExists ? Typecho_Router::url($type, $tag) : '#';
		$tag['permalink'] = Typecho_Common::url($tag['pathinfo'], $options->index);
		$tag_result = $tag_result . "\t<url>\n";
		$tag_result = $tag_result . "\t\t<loc>" . $tag['permalink'] . "</loc>\n";
		$tag_result = $tag_result . "\t\t<changefreq>always</changefreq>\n";
		$tag_result = $tag_result . "\t\t<priority>0.5</priority>\n";
		$tag_result = $tag_result . "\t</url>\n";
	}
	$result = $header . $index_result . $page_result . $category_result . $archive_result . $tag_result . $footer;//xml内容
	$dir = __TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/Sitemap/sitemap';
	if ($function === 'activate') {//激活
		$myfile = fopen($dir, "w");
		fwrite($myfile, $result);
		fclose($myfile);
	}
	if ($function === 'update') {//更新
		unlink($dir);
		$myfile = fopen($dir, "w");
		fwrite($myfile, $result);
		fclose($myfile);
		Typecho_Widget::widget('Widget_Notice')->set(_t("更新 sitemap.xml 成功"), 'success');
	}
}
function submit($function)
{ //推送百度
	$db = Typecho_Db::get();
	$options = Typecho_Widget::widget('Widget_Options');
	if ($function === 'typecho') {
		//首页
		$index = Helper::options()->siteUrl;
		$urls = array($index);
		//独立页面
		$pages = $db->fetchAll($db->select()->from('table.contents')
			->where('table.contents.status = ?', 'publish')
			->where('table.contents.created < ?', $options->gmtTime)
			->where('table.contents.type = ?', 'page')
			->order('table.contents.created', Typecho_Db::SORT_DESC));
		foreach ($pages as $page) {
			$type = $page['type'];
			$routeExists = (NULL != Typecho_Router::get($type));
			$page['pathinfo'] = $routeExists ? Typecho_Router::url($type, $page) : '#';
			$page['permalink'] = Typecho_Common::url($page['pathinfo'], $options->index);
			array_push($urls, $page['permalink']);
		}
		//分类
		$categorys = $db->fetchAll($db->select()->from('table.metas')
			->where('table.metas.type = ?', 'category'));
		foreach ($categorys as $category) {
			$type = $category['type'];
			$routeExists = (NULL != Typecho_Router::get($type));
			$category['pathinfo'] = $routeExists ? Typecho_Router::url($type, $category) : '#';
			$category['permalink'] = Typecho_Common::url($category['pathinfo'], $options->index);
			array_push($urls, $category['permalink']);
		}
	}
	if ($function === 'archive' || $function === 'archive_all') {
		$urls = array();
		//文章
		$archives = $db->fetchAll($db->select()->from('table.contents')
			->where('table.contents.status = ?', 'publish')
			->where('table.contents.created < ?', $options->gmtTime)
			->where('table.contents.type = ?', 'post')
			->order('table.contents.created', Typecho_Db::SORT_DESC));
		if ($function === 'archive') {
			for ($x = 0; $x < 20; $x++) {
				$archive = $archives[$x];
				$type = $archive['type'];
				$routeExists = (NULL != Typecho_Router::get($type));
				$archive['pathinfo'] = $routeExists ? Typecho_Router::url($type, $archive) : '#';
				$archive['permalink'] = Typecho_Common::url($archive['pathinfo'], $options->index);
				array_push($urls, $archive['permalink']);
			}
		}

		if ($function === 'archive_all') {
			foreach ($archives as $archive) {
				$type = $archive['type'];
				$routeExists = (NULL != Typecho_Router::get($type));
				$archive['pathinfo'] = $routeExists ? Typecho_Router::url($type, $archive) : '#';
				$archive['permalink'] = Typecho_Common::url($archive['pathinfo'], $options->index);
				array_push($urls, $archive['permalink']);
			}
		}
	}
	foreach ($urls as $url) {
		$url_list = $url_list . "\n" . $url;
	}


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
	Typecho_Widget::widget('Widget_Notice')->set(_t("推送完成"), 'success');
	return '成功推送' . $result['success'] . '条，今日剩余可推送' . $result['remain'] . '条' . "\n" . "\n" . $url_list;
}
