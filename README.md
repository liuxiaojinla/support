# support

#### 介绍

日常开发必备基础库【字符串、集合、数值、哈希、加密、时间、文件、反射、重试、服务器、XML】

- 数组相关（Arr.php）
- 字符串相关（Str.php）
- 简单集合（Collection.php）
- 文件相关（File.php）【待升级】
- Fluent（Fluent.php）
- 哈希（Hasher.php）
- 高阶对象代理（HigherOrderTapProxy.php）
- Javascript（Javascript.php）
- 简约限流器（LimitThrottle.php）
- 对象微代理器（MacroProxy.php）【待废弃】
- 数值相关（Number.php）
- 单词复数器（Pluralizer.php）
- 距离转换（Position.php）
- 进制转换器（Radix.php）
- 跳转（Redirect.php）
- 反射（Reflect.php）
- 常用正则（Regex.php）
- 重试器（Retry.php）
- 证书安全（Secure.php）
- 服务器相关（Server.php）【待整合】
- 简约加密器（SimpleEncrypt.php）
- 时间相关（Time.php）
- UBB（UBB.php）
- 版本判断（Version.php）
- XML（XML.php）

#### 安装教程

`composer require xin/support`

#### 使用说明

```php
<?php
$hello = "hello world!";
Str::startsWith($hello,'hello'); // true
Str::endsWith($hello ,'world!'); // true
```

更多请参考源代码

#### 参与贡献

1. Fork 本仓库
2. 新建 Feat_xxx 分支
3. 提交代码
4. 新建 Pull Request

#### 特技

1. 使用 Readme\_XXX.md 来支持不同的语言，例如 Readme\_en.md, Readme\_zh.md
