<?php

namespace Xin\Support;

final class Callback
{
    /**
     * 安全调用Callback
     * @param callable $callback
     * @param callable|null $failedCallback
     * @return mixed
     */
    public static function safeCall(callable $callback, callable $failedCallback = null)
    {
        try {
            return call_user_func($callback);
        } catch (\Throwable $e) {
            $failedCallback && call_user_func($failedCallback, $e);
        }

        return null;
    }
}
