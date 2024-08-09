<?php

namespace Xin\Support;

/**
 * 获取客户端的信息
 */
final class ClientInfo
{

    /**
     * 获取客户的IP地址
     *
     * @return string
     */
    public static function getRemoteIp()
    {
        if (isset($_SERVER ["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER ["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER ["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER ["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER ["REMOTE_ADDR"])) {
            $ip = $_SERVER ["REMOTE_ADDR"];
        } else {
            $ip = "0.0.0.0";
        }

        return $ip;
    }

    /**
     * 获取客户端端口号
     *
     * @return int
     */
    public static function getRemotePort()
    {
        $port = 0;

        if (isset($_SERVER ["REMOTE_PORT"])) {
            $port = $_SERVER ["REMOTE_PORT"];
        } elseif (isset($_COOKIE ["REMOTE_PORT"])) {
            $port = $_COOKIE ["REMOTE_PORT"];
        } elseif (isset($_POST ["REMOTE_PORT"])) {
            $port = $_POST ["REMOTE_PORT"];
        } elseif (isset($_GET ["REMOTE_PORT"])) {
            $port = $_GET ["REMOTE_PORT"];
        }

        return $port;
    }


    /**
     * 获取所有请求头信息
     *
     * @return array
     */
    public static function getAllHeader()
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers [str_replace('_', '-', substr($key, 5))] = $value;
            }
        }
        if (isset ($_SERVER ['PHP_AUTH_DIGEST'])) {
            $headers ['AUTHORIZATION'] = $_SERVER ['PHP_AUTH_DIGEST'];
        } elseif (isset($_SERVER ['PHP_AUTH_USER'], $_SERVER ['PHP_AUTH_PW'])) {
            $headers ['AUTHORIZATION'] = base64_encode($_SERVER ['PHP_AUTH_USER'] . ':' . $_SERVER ['PHP_AUTH_PW']);
        }
        if (isset ($_SERVER ['CONTENT_LENGTH'])) {
            $headers ['CONTENT-LENGTH'] = $_SERVER ['CONTENT_LENGTH'];
        }
        if (isset ($_SERVER ['CONTENT_TYPE'])) {
            $headers ['CONTENT-TYPE'] = $_SERVER ['CONTENT_TYPE'];
        }

        return $headers;
    }

    /**
     * 获取终端名称
     *
     * @param bool $isVersion 是否要返回版本号
     * @return string
     */
    public static function getClientName($isVersion = true)
    {
        $info = self::getClientInfo();
        if (strpos($info ['info_str'], 'windows phone') !== false) {
            if (!$isVersion) {
                return "windows phone";
            }

            return self::getVersion($info ['info_str'], 'windows phone');
        }

        if (strpos($info ['info_str'], 'windows') !== false) {
            if (!$isVersion) {
                return "windows";
            }

            return self::getVersion($info ['info_str'], 'windows');
        }

        if (strpos($info ['info_str'], 'android') !== false) {
            if (!$isVersion) {
                return "android";
            }

            return self::getVersion($info ['info_str'], 'android');
        }

        if (strpos($info ['info_str'], 'iphone') !== false) {
            if (!$isVersion) {
                return "iphone";
            }

            return self::getVersion($info ['info_str'], 'iphone');
        }

        if (strpos($info ['info_str'], 'mac os') !== false) {
            if (!$isVersion) {
                return "mac os";
            }

            return self::getVersion($info ['info_str'], 'mac os');
        }

        if (is_array($info ['info'])) {
            if (!$isVersion) {
                $info ['info'] = explode(" ", $info ['info'] [0]);
            }

            return $info ['info'] [0];
        }

        return "other";
    }

    /**
     * 获取客户端版本信息
     * @param string $str
     * @param string $checkName
     * @return string
     */
    public static function getVersion($str, $checkName)
    {
        $pos = strpos($str, $checkName);
        $len = strpos($str, ';', $pos);
        $len = $len ? $len - $pos : strlen($str) - $pos;

        return substr($str, $pos, $len);
    }

    /**
     * 获取终端信息
     *
     * @return array
     */
    public static function getClientInfo()
    {
        $info = [];
        $user_agent = strtolower($_SERVER ['HTTP_USER_AGENT']);
        $firstSpilt = strpos($user_agent, ')');
        $user_agent2 = substr($user_agent, 0, $firstSpilt);
        $user_agents = explode(" ", $user_agent2, 2);
        $mozilla = explode("/", $user_agents [0], 2);
        $info [$mozilla [0]] = $mozilla [1];
        $user_agent2 = substr($user_agents [1], 1);
        $info ['info'] = explode("; ", $user_agent2);
        $info ['info_str'] = $user_agent2;

        // applewebkit/537.36
        $user_agent2 = substr($user_agent, $firstSpilt + 2, strlen($user_agent) - $firstSpilt);
        $user_agent2 = preg_replace('/(\(.*\))\s/', "", $user_agent2);
        $user_agents = explode(" ", $user_agent2);
        foreach ($user_agents as $iValue) {
            $temps = explode("/", $iValue, 2);
            $info [$temps [0]] = $temps [1];
        }

        return $info;
    }

    /**
     * 是否移动端访问访问
     *
     * @return bool
     */
    public static function isMobileDevice()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }

        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stripos($_SERVER['HTTP_VIA'], "wap") !== false;
        }

        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = [
                'nokia', 'sony', 'ericsson', 'mot',
                'samsung', 'htc', 'sgh', 'lg',
                'sharp', 'sie-', 'philips', 'panasonic',
                'alcatel', 'lenovo', 'iphone', 'ipod',
                'blackberry', 'meizu', 'android', 'netfront',
                'symbian', 'ucweb', 'windowsce', 'palm',
                'operamini', 'operamobi', 'openwave', 'nexusone',
                'cldc', 'midp', 'wap', 'mobile',
            ];
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }

        // 协议法，因为有可能不准确，放到最后判断
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        return isset ($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false)
            && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false
                || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')));
    }
}
