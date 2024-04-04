<?php
function update($function, $web)
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
		$category_result = $category_result . "\t<url>\n";
		$category_result = $category_result . "\t\t<loc>" . getPermalinkCategory($category) . "</loc>\n";
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
		$archive_result = $archive_result . "\t<url>\n";
		$archive_result = $archive_result . "\t\t<loc>" . getPermalink($archive['cid']) . "</loc>\n";
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
	$result = $header . $index_result . $page_result . $category_result . $archive_result . $tag_result . $footer; //xml内容
	$dir = __TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/Sitemap/sitemap';
	if ($function === 'activate') { //激活
		$myfile = fopen($dir, "w");
		fwrite($myfile, $result);
		fclose($myfile);
	}
	if ($function === 'update') { //更新
		unlink($dir);
		$myfile = fopen($dir, "w");
		fwrite($myfile, $result);
		fclose($myfile);
		//获取时间
		$time = date('Y-m-d H:i:s', time());
		//写入日志
		$log = '【' . $time . '】' . $web . '成功更新sitemap.xml' . "\n";
		if (Helper::options()->plugin('Sitemap')->PluginLog == 1) {
			file_put_contents(__TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/Sitemap/log.txt', $log, FILE_APPEND);
		}
		//返回提示
		if ($web === 'web') {
			Typecho_Widget::widget('Widget_Notice')->set(_t("更新 sitemap.xml 成功"), 'success');
		}
		if ($web === 'api') {
			return "success";
		}
	}
}
function submit($function, $web) //推送百度
{
	$NumberOfLatestArticles = (int)Helper::options()->plugin('Sitemap')->NumberOfLatestArticles;
	//检查baidu_url
	if (Helper::options()->plugin('Sitemap')->baidu_url == NULL) {
		if ($web === 'web') {
			Typecho_Widget::widget('Widget_Notice')->set(_t("请先设置百度站长平台的站点地址"), 'error');
			return;
		}
		if ($web === 'api') {
			return "BadiuApi is null";
		}
	}
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
			array_push($urls, getPermalinkCategory($category));
		}
		echo count($urls);
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
			//获取文章数量
			$postnum = count($archives);
			//如果文章数量大于$NumberOfLatestArticles，只推送最新的$NumberOfLatestArticles篇
			if ($postnum >= $NumberOfLatestArticles) {
				for ($x = 0; $x < $NumberOfLatestArticles; $x++) {
					$archive = $archives[$x];
					array_push($urls, getPermalink($archive['cid']));
				}
			} else {
				if ($web === 'web') {
					Typecho_Widget::widget('Widget_Notice')->set(_t("文章小于" . $NumberOfLatestArticles . "篇"), 'error');
					return;
				}
				if ($web === 'api') {
					return "Number of articles less than " . $NumberOfLatestArticles;
				}
			}
		}

		if ($function === 'archive_all') {
			foreach ($archives as $archive) {
				array_push($urls, getPermalink($archive['cid']));
			}
		}
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
	//获取时间
	$time = date('Y-m-d H:i:s', time());
	//推送状态判断{"error":400,"message":"over quota"}
	if ($result['error'] == 400) {
		if ($result['message'] == "over quota") {
			$message = '超过推送限额';
		}
		if ($result['message'] == "empty content") {
			$message = '推送网站列表为空';
		}
		//写入日志
		$log = '【' . $time . '】' . $web . '推送失败：' . $message . "\n";
		if (Helper::options()->plugin('Sitemap')->PluginLog == 1) {
			file_put_contents(__TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/Sitemap/log.txt', $log, FILE_APPEND);
		}
		//返回提示
		if ($web === 'web') {
			Typecho_Widget::widget('Widget_Notice')->set(_t("推送失败"), 'error');
			return '推送失败：' . $message;
		}
		if ($web === 'api') {
			return $result_json;
		}
	} else {
		//写入日志
		$log = '【' . $time . '】' . $web . '成功推送：' . $result['success'] . '条，今日剩余可推送' . $result['remain'] . '条' . $url_list . "\n";
		if (Helper::options()->plugin('Sitemap')->PluginLog == 1) {
			file_put_contents(__TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ . '/Sitemap/log.txt', $log, FILE_APPEND);
		}
		//返回提示
		if ($web === 'web') {
			Typecho_Widget::widget('Widget_Notice')->set(_t("成功推送"), 'success');
			return '成功推送：' . $result['success'] . '条，今日剩余可推送' . $result['remain'] . '条';
		}
		if ($web === 'api') {
			return $result_json;
		}
	}
}
//生成文章永久链接
function getPermalink($cid)
{
	$db = Typecho_Db::get();
	// 获取文章的创建时间和slug
	$row = $db->fetchRow($db->select('table.contents.created', 'table.contents.slug', 'table.contents.type', 'table.contents.cid')
		->from('table.contents')
		->where('table.contents.type = ?', 'post')
		->where('table.contents.cid = ?', $cid)
		->limit(1));
	$slug = $row['slug'];
	// 获取文章的创建时间
	$created = $row['created'];
	$year = date('Y', $created);
	$month = date('m', $created);
	$day = date('d', $created);
	// 获取分类
	$category = $db->fetchRow($db->select('table.metas.slug')
		->from('table.relationships')
		->join('table.metas', 'table.metas.mid = table.relationships.mid')
		->where('table.relationships.cid = ?', $cid)
		->where('table.metas.type = ?', 'category')
		->limit(1));
	$category = $category['slug'];
	// 获取目录分类
	$directory = '';
	$parent = $db->fetchRow($db->select('table.metas.parent')
		->from('table.metas')
		->where('table.metas.type = ?', 'category')
		->where('table.metas.slug = ?', $category)
		->limit(1));
	while ($parent['parent'] != 0) {
		$parent = $db->fetchRow($db->select('table.metas.slug', 'table.metas.parent')
			->from('table.metas')
			->where('table.metas.type = ?', 'category')
			->where('table.metas.mid = ?', $parent['parent'])
			->limit(1));
		$directory = $parent['slug'] . '/' . $directory;
	}
	$directory = $directory . $category;
	// 返回链接
	if ($row) {
		$options = Typecho_Widget::widget('Widget_Options');
		$permalink = Typecho_Common::url(Typecho_Router::url($row['type'], $row), $options->index);
		$permalink = str_replace('{slug}', $slug, $permalink);
		$permalink = str_replace('{category}', $category, $permalink);
		$permalink = str_replace('{directory}', $directory, $permalink);
		$permalink = str_replace('{year}', $year, $permalink);
		$permalink = str_replace('{month}', $month, $permalink);
		$permalink = str_replace('{day}', $day, $permalink);
		return $permalink;
	}
}
//生成分类永久链接
function getPermalinkCategory($category)
{
	$options = Typecho_Widget::widget('Widget_Options');
	$type = $category['type'];
	$routeExists = (NULL != Typecho_Router::get($type));
	$category['pathinfo'] = $routeExists ? Typecho_Router::url($type, $category) : '#';
	$category['permalink'] = Typecho_Common::url($category['pathinfo'], $options->index);
	$directory = '';
	// 获取目录分类
	$db = Typecho_Db::get();
	$parent = $db->fetchRow($db->select('table.metas.parent')
		->from('table.metas')
		->where('table.metas.type = ?', 'category')
		->where('table.metas.slug = ?', $category['slug'])
		->limit(1));
	while ($parent['parent'] != 0) {
		$parent = $db->fetchRow($db->select('table.metas.slug', 'table.metas.parent')
			->from('table.metas')
			->where('table.metas.type = ?', 'category')
			->where('table.metas.mid = ?', $parent['parent'])
			->limit(1));
		$directory = $parent['slug'] . '/' . $directory;
	}
	$directory = $directory . $category['slug'];
	$category['permalink'] = str_replace('{directory}', $directory, $category['permalink']);
	return $category['permalink'];
}
