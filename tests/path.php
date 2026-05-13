<?php

use Xin\Support\Path;

require_once '../vendor/autoload.php';

var_dump(Path::suffix("00.txt"));
var_dump(Path::suffix("00."));
var_dump(Path::suffix("00"));
