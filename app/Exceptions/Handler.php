<?php

namespace App\Exceptions;

use App\Helpers\Tools;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        // return parent::render($request, $e);
        return parent::render($request, $e);
    }

    /**
     * 全局异常处理
     * @param $request
     * @param Exception $exception
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @author huangjinbing <373768442@qq.com>
     */
    public function handle($request, Exception $exception)
    {
        Tools::logUnusualError($exception);

        if ($exception instanceof ValidationException) {
            // 表单验证沿用原有的
            return parent::render($request, $exception);
        } else if ($exception instanceof RequestException) {
            // 接口传参缺少
            return response()->json(Tools::error($exception->getMessage()));
        } else {
            // 接口报错
            return response()->json(Tools::error('系统异常'));
        }
    }
}
