<?php

use Xin\Support\Url;

require_once '../vendor/autoload.php';

$url = 'https://demo.com/123/456?name=zhangsan&age=18#fragment';
var_dump("schema: " . Url::schema($url));
var_dump("host: " . Url::host($url));
var_dump("port: " . Url::port($url));
var_dump("path: " . Url::path($url, true));
var_dump("fragment: " . Url::fragment($url));
var_dump("queryString: " . Url::query($url));
var_dump("parseQuery: " . Json::encode(Url::parseQuery($url)));
var_dump("withQuery: " . Url::withQuery($url, ['name' => '李四', 'age' => 20]));
