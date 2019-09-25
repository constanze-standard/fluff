<?php

/**
 * Copyright 2019 Alex <blldxt@gmail.com>
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ConstanzeStandard\Fluff\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Close the output buffer.
 * 
 * @author Alex <blldxt@gmail.com>
 */
class EndOutputBuffer implements MiddlewareInterface
{
    /**
     * The chunk size of response.
     * 
     * @var int
     */
    private $chunkSize;

    /**
     * Flush or clean output buffers.
     * 
     * @param bool $isFlush
     */
    public static function cleanOutputBuffers(int $targetLevel)
    {
        $status = ob_get_status(true);
        $level = \count($status);
        $flags = PHP_OUTPUT_HANDLER_REMOVABLE | PHP_OUTPUT_HANDLER_CLEANABLE;
        while ($level > $targetLevel) {
            $level--;
            $s = $status[$level];
            if ((isset($s['del']) ? $s['del'] : !isset($s['flags']) || ($s['flags'] & $flags) === $flags)) {
                ob_end_clean();
            }
        }
    }

    /**
     * @param int $chunkSize
     * @param bool $flush
     */
    public function __construct(int $chunkSize = 4096)
    {
        $this->chunkSize = $chunkSize;
    }

    /**
     * Process an incoming server request.
     * 
     * Close the output buffer, and flush if it's `HEAD` http request.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $this->respondHeader($response);
        $isHead = strcasecmp($request->getMethod(), 'HEAD') === 0;
        $this->respond($response, !$isHead);
        return $response;
    }

    /**
     * Emit the response, flush and clean the output buffer.
     * 
     * @param ResponseInterface $response the PSR-7 response.
     */
    public function respond(ResponseInterface $response, $flush = true)
    {
        if ($flush) {
            $body = $response->getBody();
            if ($body->isSeekable()) {
                $body->rewind();
            }

            $contentLength  = $response->getHeaderLine('Content-Length');
            if (!$contentLength) {
                $contentLength = $body->getSize();
            }

            $outputHandle = fopen('php://output', 'w');

            if ((int) $contentLength) {
                while ($contentLength > 0 && !$body->eof()) {
                    $length = min($this->chunkSize, (int)$contentLength);
                    $contentLength -= $length;
                    fwrite($outputHandle, $body->read($length));

                    if (connection_status() !== CONNECTION_NORMAL) {
                        break;
                    }
                }
            } else {
                while (!$body->eof()) {
                    fwrite($outputHandle, $body->read($this->chunkSize));

                    if (connection_status() !== CONNECTION_NORMAL) {
                        break;
                    }
                }
            }

            fclose($outputHandle);
        }
        static::cleanOutputBuffers(0);
    }

    /**
     * Send accept header from response.
     * 
     * @param ResponseInterface $response
     */
    private function respondHeader(ResponseInterface $response)
    {
        if (!headers_sent()) {
            $version = $response->getProtocolVersion();
            $statusCode = $response->getStatusCode();
            $reasonPhrase = $response->getReasonPhrase();
            header(sprintf('HTTP/%s %s %s', $version, $statusCode, $reasonPhrase));

            foreach ($response->getHeaders() as $key => $headers) {
                if (0 !== strcasecmp($key, 'set-cookie')) {
                    $replace = 0 === strcasecmp($key, 'content-type');
                    foreach ($headers as $header) {
                        header("{$key}: {$header}", $replace);
                    }
                }
            }
        }
    }
}
