# support

#### 介绍

日常开发必备基础库【字符串、集合、数值、加密、时间、文件、反射、重试、服务器、XML】

- 数组相关（Arr.php）
- 字符串相关（Str.php）
- 简单集合（Collection.php）
- 文件相关（File.php）【待升级】
- Fluent（Fluent.php）
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

以下是一些简单的使用示例：

- **字符串操作**

```php
<?php
$hello = "hello world!";
Str::startsWith($hello, 'hello'); // true
Str::endsWith($hello, 'world!'); // true
```


- **数组操作**

```php
<?php
$array = [1, 2, 3, 4, 5];
Arr::contains($array, 3); // true
Arr::pluck($array, function ($item) { return $item * 2; }); // [2, 4, 6, 8, 10]
```


- **集合操作**

```php
<?php
$collection = new Collection([1, 2, 3, 4, 5]);
$collection->map(function ($item) { return $item * 2; })->all(); // [2, 4, 6, 8, 10]
$collection->filter(function ($item) { return $item > 2; })->all(); // [3, 4, 5]
```


- **文件操作**

```php
<?php
File::exists('path/to/file.txt'); // true or false
File::get('path/to/file.txt'); // 文件内容
```


- **时间操作**

```php
<?php
Time::now(); // 当前时间
Time::parse('2023-10-01')->format('Y-m-d'); // 格式化时间
```


- **加密操作**

```php
<?php
$encrypted = SimpleEncrypt::encrypt('secret message');
$decrypted = SimpleEncrypt::decrypt($encrypted);
```


- **反射操作**

```php
<?php
$reflection = Reflect::on('SomeClass');
$reflection->call('someMethod');
```


- **重试机制**

```php
<?php
Retry::times(3)->run(function () {
    // 可能会失败的操作
});
```


- **限流器**

```php
<?php
LimitThrottle::allowIf(function () {
    // 判断是否允许操作
})->then(function () {
    // 允许操作时执行的代码
})->otherwise(function () {
    // 不允许操作时执行的代码
});
```


- **版本判断**

```php
<?php
Version::compare('1.0.0', '1.0.1'); // -1
Version::compare('1.0.1', '1.0.0'); // 1
Version::compare('1.0.0', '1.0.0'); // 0
```


- **XML操作**

```php
<?php
$xml = XML::parse('<root><child>value</child></root>');
$xml->child; // value
```


- **UBB代码解析**

```php
<?php
$ubb = '[b]Bold Text[/b]';
UBB::parse($ubb); // <strong>Bold Text</strong>
```


- **Fluent接口**

```php
<?php
$fluent = new Fluent(['key' => 'value']);
$fluent->get('key'); // value
$fluent->set('newKey', 'newValue');
$fluent->all(); // ['key' => 'value', 'newKey' => 'newValue']
```


- **高阶对象代理**

```php
<?php
$proxy = new HigherOrderTapProxy($object);
$proxy->method(function ($item) {
    // 对对象进行操作
});
```


- **对象微代理器**

```php
<?php
// 删除: $proxy = new MacroProxy($object);
// 删除: $proxy->macro('macroName', function ($item) {
// 删除:     // 定义宏
// 删除: });
```


- **常用正则**

```php
<?php
Regex::isEmail('example@example.com'); // true
Regex::isUrl('https://example.com'); // true
```


- **服务器操作**

```php
<?php
Server::isLocalhost(); // true or false
Server::ip(); // 服务器IP地址
```


- **JavaScript交互**

```php
<?php
Javascript::render('console.log("Hello, World!");'); // 渲染JavaScript代码
```


- **距离转换**

```php
<?php
Position::distance(34.052235, -118.243683, 40.712776, -74.005974); // 距离计算
```


- **进制转换**

```php
<?php
Radix::toBase64('Hello, World!'); // 转换为Base64
Radix::fromBase64('SGVsbG8sIFdvcmxkIQ=='); // 从Base64转换
```


- **跳转**

```php
<?php
Redirect::to('https://example.com'); // 重定向到指定URL
```
