## 介绍
坦白言 —— 微信小程序

官网： www.tanbaiyan.com

顾名思义 好友间的坦白工具/社交玩具

本套代码是小程序后端代码，小程序前端开源地址：https://github.com/wwcxjun/wechat_tanbaiyan

## 玩法
进入小程序后转发到群聊 || 进入小程序保存小程序二维码转发到朋友圈或群聊

好友在群聊里点击小程序卡片进入留言界面 || 朋友圈好友扫码进入留言界面

好友留言后 在小程序内可查看

## 环境
PHP版本 >=7.1.3

## 使用
1. 第一步当然是下载代码

2. 执行命令 `cp .env.example .env`

3. 执行命令 `php artisan key:generate`

4. 打开.env文件 配置数据库及小程序Appid和AppSecret

5. 执行命令 `php artisan migrate`

以上代码均在项目根目录执行

需要将运行目录改为 `public` 而不是根目录，因为项目依赖Laravel

## 示例
![](https://www.tanbaiyan.com/images/1.jpg)

![](https://www.tanbaiyan.com/images/2.jpg)

![](https://www.tanbaiyan.com/images/3.jpg)

## 为什么要开源？
运营“坦白言”两个月，日活好不容易达到了10K+ 然后毫无预兆的就被官方封禁了。

目前“坦白言”在微信上依然能搜索到，但名存实亡，只有一个壳在。

原因是官方不给个人开放这样的小程序，所以决定开源。

个人开发者使用这套代码运营时建议做好被封禁，然后申诉转型的准备，留住活跃用户。

企业资质注册的随意使用~
