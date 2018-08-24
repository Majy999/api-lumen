<?php

if (!function_exists('app_path')) {

    // app路径
    function app_path($path = '')
    {
        return app('path') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if (!function_exists('request')) {

    // 接收请求参数
    function request($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('request');
        }

        if (is_array($key)) {
            return app('request')->only($key);
        }

        $value = app('request')->get($key);

        return is_null($value) ? value($default) : $value;
    }
}

if (!function_exists('alter_table_comment')) {

    // 创建数据表注释
    function alter_table_comment($tableName, $comment, $prefix = null)
    {
        if (!$prefix) $prefix = DB::getTablePrefix();
        $tableName = $prefix . $tableName;
        DB::statement("ALTER TABLE $tableName COMMENT='$comment'");
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string $path
     * @return string
     */
    function public_path()
    {
        return base_path() . '/public';
    }
}

