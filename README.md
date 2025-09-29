# Support 库文档

## 概述

`support` 库是一个功能全面的 PHP 基础库，旨在为开发者提供日常开发中所需的各类工具和实用功能。无论是处理字符串、操作集合、进行加密、管理文件，还是处理时间、反射和 XML，都能为你提供简洁高效的解决方案。

## 许可证

本项目采用 Apache-2.0 许可证 - 详情请参见 LICENSE 文件。

## 安装方式

使用 Composer 安装：

```bash
composer require xin/support
```

## 模块与功能

### 基础工具

| 模块 | 描述 |
|------|------|
| `Arr.php` | 数组操作工具类（如过滤、提取、点符号访问等） |
| `Str.php` | 字符串处理工具（如检查前缀/后缀、驼峰命名转换、随机字符串生成等） |
| `File.php` | 文件系统操作（如检查文件存在性、读取内容、文件哈希计算等） |
| `Number.php` | 数值计算辅助工具 |
| `Time.php` | 时间处理（包括格式化、解析、时间范围计算等） |
| `Regex.php` | 正则表达式验证函数（邮箱、URL、手机号等） |
| `Process.php` | 进程管理工具 |
| `Reflect.php` | 基于反射的类和方法操作 |
| `Xml.php` | XML 解析功能 |
| `Json.php` | JSON 编码/解码，增强错误处理 |
| `Position.php` | 地理位置坐标间距离计算及坐标系转换 |
| `Path.php` | 路径操作工具 |
| `Fluent.php` | 流畅接口模式实现 |
| `OS.php` | 获取操作系统信息 |
| `Retry.php` | 不可靠操作的重试机制 |
| `Pluralizer.php` | 单词复数化功能 |
| `Radix.php` | 进制转换工具（如 base64 编码、62进制转换） |
| `Version.php` | 语义化版本比较工具 |
| `MacroProxy.php` | 对象宏扩展功能 |
| `HigherOrderTapProxy.php` | 高阶代理对象，支持流畅方法链 |

### 安全模块

| 模块 | 描述 |
|------|------|
| `Security\Secure.php` | 证书安全处理 |
| `Security\Encryption.php` | 数据加密和解密工具 |
| `Security\Hash.php` | 哈希生成和验证 |
| `SimpleEncrypt.php` | 简化版加密器，满足基础加密需求 |

### 限流模块

| 模块 | 描述 |
|------|------|
| `LimitThrottle.php` | 简单限流器，支持条件执行路径 |

### Web 工具

| 模块 | 描述 |
|------|------|
| `Web\Javascript.php` | JavaScript 代码生成和渲染 |
| `Web\Redirect.php` | HTTP 重定向工具 |
| `Web\ServerInfo.php` | 服务器环境信息访问 |
| `Web\ClientInfo.php` | 客户端检测和用户代理解析 |

## 使用示例

### 字符串操作

```php
use Xin\Support\Str;

$hello = "hello world!";
echo Str::startsWith($hello, 'hello') ? 'true' : 'false'; // true
echo Str::endsWith($hello, 'world!') ? 'true' : 'false'; // true
echo Str::contains($hello, 'lo wo') ? 'true' : 'false'; // true

// 驼峰与下划线转换
echo Str::snake('camelCase'); // camel_case
echo Str::camel('snake_case'); // snakeCase
echo Str::studly('snake_case'); // SnakeCase

// 生成随机字符串
echo Str::random(10); // 随机10位字符串
echo Str::random(10, 0); // 随机10位数字

// UUID生成
echo Str::uuid(); // 生成UUID对象
echo Str::assignUuid(); // 生成UUID字符串
```

### 数组操作

```php
use Xin\Support\Arr;

$array = [1, 2, 3, 4, 5];
echo Arr::contains($array, 3) ? 'true' : 'false'; // true

$result = Arr::pluck($array, function ($item) {
    return $item * 2;
});
print_r($result); // [2, 4, 6, 8, 10]

// 点符号访问数组
$data = [
    'user' => [
        'name' => 'John',
        'profile' => [
            'email' => 'john@example.com'
        ]
    ]
];

echo Arr::get($data, 'user.name'); // John
echo Arr::get($data, 'user.profile.email'); // john@example.com

// 设置值
Arr::set($data, 'user.profile.phone', '123456789');
print_r($data); // 包含电话号码的新数组

// 检查值是否存在
echo Arr::has($data, 'user.profile.email') ? 'true' : 'false'; // true
```

### 集合操作

```php
use Xin\Support\Collection;

$collection = new Collection([1, 2, 3, 4, 5]);
$result = $collection->map(function ($item) {
    return $item * 2;
})->all();

print_r($result); // [2, 4, 6, 8, 10]

$filtered = $collection->filter(function ($item) {
    return $item > 2;
})->all();

print_r($filtered); // [3, 4, 5]

// 更多集合操作
$collection->each(function ($item, $key) {
    echo "Item {$key}: {$item}\n";
});

echo $collection->first(); // 1
echo $collection->last(); // 5
```

### 文件系统操作

```php
use Xin\Support\File;

if (File::exists('path/to/file.txt')) {
    echo File::get('path/to/file.txt');
}

// 写入文件
File::put('path/to/newfile.txt', 'Hello World');

// 获取文件哈希
$hash = File::hash('path/to/file.txt', File::HASH_ETAG);
$md5 = File::hash('path/to/file.txt', File::HASH_MD5);

// 获取目录下所有文件
$files = File::files('path/to/directory');
foreach ($files as $file) {
    echo $file->getPathname() . "\n";
}
```

### 时间处理

```php
use Xin\Support\Time;

echo Time::now(); // 当前时间
echo Time::parse('2023-10-01')->format('Y-m-d'); // 格式化日期

// 时间计算
$nextWeek = Time::addWeeks(time(), 1);
$lastMonth = Time::subMonths(time(), 1);

// 获取时间范围
[$start, $end] = Time::todayRange();
[$start, $end] = Time::weekRange();
[$start, $end] = Time::monthRange();

// 格式化相对时间
echo Time::formatRelative(strtotime('-2 hours')); // 2小时前

// 格式化时长
echo Time::formatDuration(3661); // 1小时1分1秒
```

### 加密操作

```php
use Xin\Support\SimpleEncrypt;
use Xin\Support\Security\Encryption;
use Xin\Support\Security\Hash;

// 简单加密
$encrypted = SimpleEncrypt::encrypt('secret message', 'mykey');
$decrypted = SimpleEncrypt::decrypt($encrypted, 'mykey');

// 高级加密
$encryption = new Encryption('my-encryption-key');
$encrypted = $encryption->encrypt('secret data');
$decrypted = $encryption->decrypt($encrypted);

// 哈希处理
$hash = new Hash();
$hashedPassword = $hash->make('password123');
$isVerified = $hash->verify('password123', $hashedPassword);
```

### 反射操作

```php
use Xin\Support\Reflect;

$reflection = Reflect::on('SomeClass');
$reflection->call('someMethod');

// 获取类属性
$value = Reflect::get($object, 'privateProperty');
Reflect::set($object, 'privateProperty', 'newValue');
```

### 重试机制

```php
use Xin\Support\Retry;

Retry::make(function ($attempts) {
    // 可能会失败的操作
    if ($attempts < 3) {
        throw new Exception("Failed attempt {$attempts}");
    }
    return "Success on attempt {$attempts}";
}, 5)->invoke(); // 最多重试5次
```

### 限流器

```php
use Xin\Support\LimitThrottle;

LimitThrottle::general(
    function () {
        // 获取当前计数
        return (int) cache()->get('counter', 0);
    },
    function ($limits, $value) {
        // 当达到限制时执行的操作
        echo "Limit reached at {$value}\n";
        return true;
    }
);
```

### 版本比较

```php
use Xin\Support\Version;

echo Version::compare('1.0.0', '1.0.1'); // -1
echo Version::compare('1.0.1', '1.0.0'); // 1
echo Version::compare('1.0.0', '1.0.0'); // 0

// 便捷方法
echo Version::gt('1.0.1', '1.0.0') ? 'true' : 'false'; // true
echo Version::eq('1.0.0', '1.0.0') ? 'true' : 'false'; // true
echo Version::lt('1.0.0', '1.0.1') ? 'true' : 'false'; // true
```

### XML 解析

```php
use Xin\Support\Xml;

$xml = Xml::parse('<root><child>value</child></root>');
echo $xml['child']; // value

// 转换为XML
$array = ['name' => 'John', 'age' => 30];
$xmlString = Xml::encode($array, 'person');
```

### UBB 代码解析

```php
use Xin\Support\Str;

$ubb = '[b]Bold Text[/b]';
$html = Str::ubbToHtml($ubb); // <b>Bold Text</b>
```

### 流畅接口

```php
use Xin\Support\Fluent;

$fluent = new Fluent(['key' => 'value']);
echo $fluent->get('key'); // value
$fluent->set('newKey', 'newValue');
print_r($fluent->all()); // ['key' => 'value', 'newKey' => 'newValue']

// 链式调用
$fluent->merge(['another' => 'value'])
       ->except('key')
       ->only(['newKey', 'another']);
```

### 高阶对象代理

```php
use Xin\Support\HigherOrderTapProxy;

$proxy = new HigherOrderTapProxy($object);
$proxy->method(function ($item) {
    // 对对象进行操作
});
```

### 对象宏扩展

```php
use Xin\Support\MacroProxy;

// 使用自定义宏扩展对象
$proxy = new MacroProxy($object);
$proxy->macro('macroName', function ($item) {
    // 在此处定义宏逻辑
});
```

### 正则表达式

```php
use Xin\Support\Regex;

echo Regex::isEmail('example@example.com') ? '有效邮箱' : '无效邮箱';
echo Regex::isUrl('https://example.com') ? '有效URL' : '无效URL';
echo Regex::isMobile('13800138000') ? '有效手机号' : '无效手机号';
echo Regex::isUsername('username', 3, 20) ? '有效用户名' : '无效用户名';
```

### 服务器信息

```php
use Xin\Support\Web\ServerInfo;

echo ServerInfo::isLocalhost() ? '本地运行' : '非本地';
echo ServerInfo::ip(); // 服务器IP地址
```

### JavaScript 生成

```php
use Xin\Support\Web\Javascript;

Javascript::render('console.log("Hello, World!");');
```

### 距离计算

```php
use Xin\Support\Position;

$distance = Position::calcDistance(
    34.052235, -118.243683,
    40.712776, -74.005974
);
echo $distance; // 计算出的距离(公里)

// 坐标系转换
[$gcjLat, $gcjLng] = Position::gps84ToGcj02(34.052235, -118.243683);
[$bdLat, $bdLng] = Position::gcj02ToBD09($gcjLat, $gcjLng);
```

### 进制转换

```php
use Xin\Support\Radix;

// 62进制转换
$converter = Radix::radix62();
$encoded = $converter->generate(12345); // 数字转62进制
$decoded = $converter->parse($encoded); // 62进制转数字

// Base64
echo base64_encode('Hello, World!');
echo base64_decode('SGVsbG8sIFdvcmxkIQ==');
```

### 页面重定向

```php
use Xin\Support\Web\Redirect;

Redirect::redirect('https://example.com', 3, '页面将在3秒后跳转...');
```

## 贡献

欢迎贡献！请提交拉取请求或开启议题来报告任何错误或功能请求。
