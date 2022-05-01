# Sitemap-For-Typecho

用着好的话麻烦给个star，十分感谢！！！

## 功能

1、为`Typecho`生成`sitemap`包含**首页、独立页面、分类、标签、文章**

2、推送百度搜索资源平台，目前仅支持普通收录推送，卑微的我没快速收录权限

~~不支持发布文章自动更新`sitemap`及自动推送，可能会影响文章发布速度所以没有做支持~~

### API说明

| 参数 | 值 | 说明 | 
| ------- | --------- | ----------------- | 
| sitemap | update | 更新sitemap |
| push | main | 推送核心文章 |
| push | all | 推送全部文章 |
| push | new | 推送最新文章 |
| token | API token | 插件中的API token |

## 更新

* v1.1.0 修改推送规则
* v1.2.0 修改缓存机制，以前是存数据库会导致内容超出长度，现在改为存储缓存文件
* v1.2.1 修改读取分类的错误
* v1.3.0
增加api刷新sitemap功能
增加api推送文章功能
发布文章自动推送当前文章
发布文章更新sitemap
增加推送日志和Sitemap更新日志存入插件目录下
修复文章不满20篇的推送异常报错
修改手动推送后显示链接乱的问题

*卸禁用后删除插件，再更新！插件目录设置777权限*

博客：[https://www.zhaoyingtian.com/archives/93.html](https://www.zhaoyingtian.com/archives/93.html)