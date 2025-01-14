<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

namespace App;

use Next\Http\Message\Cookie;
use Next\Http\Message\Response as PsrResponse;
use Next\Http\Message\Stream\FileStream;
use Next\Utils\Contract\Arrayable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Response extends PsrResponse
{
    /**
     * 渲染视图.
     *
     * @throws ContainerExceptionInterface
     * @throws \ReflectionException
     */
    public static function view(string $view, array $arguments = [], ?ServerRequestInterface $request = null): ResponseInterface
    {
        if (!class_exists('Next\View\ViewFactory')) {
            throw new \BadMethodCallException('View is not supported. Please install package next/view.');
        }
        $renderer = make('Next\View\ViewFactory')->getRenderer();
        if (isset($request)) {
            $renderer->assign('request', $request);
        }
        return Response::HTML($renderer->render($view, $arguments));
    }

    /**
     * Create a file download response.
     *
     * @param string $name   文件名（留空则自动生成文件名）
     * @param int    $offset 偏移量
     * @param int    $length 长度
     */
    public static function download(string $file, string $name = '', int $offset = 0, int $length = 0): ResponseInterface
    {
        return new static(200, [
            'Pragma'                    => 'public',
            'Expires'                   => '0',
            'Cache-Control'             => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Type'              => 'application/octet-stream',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Disposition'       => sprintf('attachment;filename=%s', rawurlencode($name ?: pathinfo($file, PATHINFO_BASENAME))),
        ], new FileStream($file, $offset, $length));
    }

    /**
     * Create a JSON response.
     *
     * @param array|Arrayable|\JsonSerializable|string $data
     */
    public static function JSON($data, int $status = 200): ResponseInterface
    {
        if (!is_string($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return new static($status, ['Content-Type' => 'application/json; charset=utf-8'], $data);
    }

    /**
     * Create a JSONP response.
     *
     * @param array|Arrayable $data
     */
    public static function JSONP(ServerRequestInterface $request, $data, int $status = 200): ResponseInterface
    {
        if ($callback = $request->query('callback')) {
            if (!is_string($data)) {
                $data = json_encode($data, JSON_UNESCAPED_UNICODE);
            }
            return new static($status, ['Content-Type' => 'application/javascript; charset=utf-8'], sprintf('%s(%s)', $callback, $data));
        }
        return static::JSON($data, $status);
    }

    /**
     * Create a HTML response.
     *
     * @param string|\Stringable $data
     */
    public static function HTML($data, int $status = 200): ResponseInterface
    {
        return new static($status, ['Content-Type' => 'text/html; charset=utf-8'], (string)$data);
    }

    /**
     * Create a text response.
     */
    public static function text(string $content, int $status = 200): ResponseInterface
    {
        return new static($status, ['Content-Type' => 'text/plain; charset=utf-8'], $content);
    }

    /**
     * Create a redirect response.
     */
    public static function redirect(string $url, int $status = 302): ResponseInterface
    {
        return new static($status, ['Location' => $url]);
    }

    /**
     * Is the response empty?
     */
    public function isEmpty(): bool
    {
        return in_array($this->statusCode, [204, 304]);
    }

    /**
     * Set cookie.
     */
    public function withCookie(
        string $name,
        string $value,
        int    $expires = 3600,
        string $path = '/',
        string $domain = '',
        bool   $secure = false,
        bool   $httponly = false,
        string $sameSite = ''
    )
    {
        $cookie = new Cookie(...func_get_args());
        return $this->withAddedHeader('Set-Cookie', $cookie->__toString());
    }

    public function setCookie(
        string $name,
        string $value,
        int    $expires = 3600,
        string $path = '/',
        string $domain = '',
        bool   $secure = false,
        bool   $httponly = false,
        string $sameSite = ''
    ): static
    {
        $cookie = new Cookie(...func_get_args());
        return $this->setAddedHeader('Set-Cookie', $cookie->__toString());
    }
}
