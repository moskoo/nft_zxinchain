<?php
/*
 * This file is part of the moshong/nft_zxinchainn.
 * Tencent Zhixin Chain NFT Platform Interface SDK.
 *
 * (c) moshong <9080@live.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace NftZxinchainn\Tools;

use NftZxinchainn\Tools\Traits\HasHttpRequests;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LogLevel;

/**
 * Class BaseClient.
 *
 * @author overtrue <i@overtrue.me>
 */
class BaseClient
{
    use HasHttpRequests {
        request as performRequest;
    }

    /**
     * @var \NftZxinchainn\Tools\ServiceContainer
     */
    protected $app;
    protected $headers;
    protected $headers2;

    /**
     * @var string
     */
    //protected $signData;

    /**
     * BaseClient constructor.
     *
     * @param \NftZxinchainn\Tools\ServiceContainer                    $app
     */
    public function __construct(ServiceContainer $app)
    {
        $this->app = $app;
        $signData = $this->getSign();
        $this->headers = ['App-Id'=>$this->app->config["appId"],'Signature-Time'=>$signData['SignatureTime'],'Signature'=>$signData['Signature'],'Nonce'=>$signData['Nonce']];
        $signData2 = $this->getSign2();
        $this->headers2 = ['Signature-Time'=>$signData2['SignatureTime'],'Signature'=>$signData2['Signature'],'Nonce'=>$signData2['Nonce']];
    }

    /**
     * Get sign data.
     *
     */
    public function getSign()
    {
        $params = [
            'appId' => $this->app['config']['appId'],
            'appKey' => $this->app['config']['appKey'],
        ];
        $result = $this->httpPostJson('http://127.0.0.1:30505/generateApiSign', $params);
        return isset($result["signData"])?$result["signData"]:false;
    }
    public function getSign2()
    {
        $result = $this->httpPostJson('http://127.0.0.1:30505/generateApiSign', []);
        return isset($result["signData"])?$result["signData"]:false;
    }

    /**
     * GET request.
     *
     * @param string $url
     * @param array  $query
     *
     * @return \Psr\Http\Message\ResponseInterface|\NftZxinchainn\Tools\Support\Collection|array|object|string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpGet(string $url, array $query = [])
    {
        return $this->request($url, 'GET', ['query' => $query]);
    }
    public function httpGet2(string $url, array $query = [])
    {
        return $this->request2($url, 'GET', ['query' => $query]);
    }

    /**
     * POST request.
     *
     * @param string $url
     * @param array  $data
     *
     * @return \Psr\Http\Message\ResponseInterface|\NftZxinchainn\Tools\Support\Collection|array|object|string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpPost(string $url, array $data = [])
    {
        return $this->request($url, 'POST', ['form_params' => $data]);
    }


    /**
     * JSON request.
     *
     * @param string $url
     * @param array  $data
     * @param array  $query
     *
     * @return \Psr\Http\Message\ResponseInterface|\NftZxinchainn\Tools\Support\Collection|array|object|string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpPostJson(string $url, array $data = [], array $query = [])
    {

        return $this->request($url, 'POST', ['query' => $query, 'json' => $data]);
    }

    public function httpPostJson2(string $url, array $data = [], array $query = [])
    {

        return $this->request2($url,'POST', ['query' => $query, 'json' => $data]);
    }

    /**
     * Upload file.
     *
     * @param string $url
     * @param array  $files
     * @param array  $form
     * @param array  $query
     *
     * @return \Psr\Http\Message\ResponseInterface|\NftZxinchainn\Tools\Support\Collection|array|object|string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpUpload(string $url, array $files = [], array $form = [], array $query = [])
    {
        $multipart = [];
        $headers = [];

        if (isset($form['filename'])) {
            $headers = [
                'Content-Disposition' => 'form-data; name="media"; filename="'.$form['filename'].'"'
            ];
        }
        foreach ($files as $name => $path) {
            $multipart[] = [
                'name' => $name,
                'contents' => fopen($path["tmp_name"], 'r'),
                'headers' => $headers
            ];
        }
        foreach ($form as $name => $contents) {
            $multipart[] = compact('name', 'contents');
        }
        return $this->request(
            $url,
            'POST',
            ['query' => $query, 'multipart' => $multipart, 'connect_timeout' => 30, 'timeout' => 30, 'read_timeout' => 30]
        );
    }

    public function httpUpload2(string $url, array $files = [], array $form = [], array $query = [])
    {
        $multipart = [];
        $headers = [];

        if (isset($form['filename'])) {
            $headers = [
                'Content-Disposition' => 'form-data; name="media"; filename="'.$form['filename'].'"'
            ];
        }
        foreach ($files as $name => $path) {
            $multipart[] = [
                'name' => $name,
                'contents' => fopen($path["tmp_name"], 'r'),
                'headers' => $headers
            ];
        }
        foreach ($form as $name => $contents) {
            $multipart[] = compact('name', 'contents');
        }
        return $this->request2(
            $url,
            'POST',
            ['query' => $query, 'multipart' => $multipart, 'connect_timeout' => 30, 'timeout' => 30, 'read_timeout' => 30]
        );
    }



    /**
     * @param string $url
     * @param string $method
     * @param array  $options
     * @param bool   $returnRaw
     *
     * @return \Psr\Http\Message\ResponseInterface|\NftZxinchainn\Tools\Support\Collection|array|object|string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $url, string $method = 'GET', array $options = [], $returnRaw = false)
    {
        if (empty($this->middlewares)) {
            $this->registerHttpMiddlewares();
        }

        $options['headers'] = $this->headers;

        $response = $this->performRequest($url, $method, $options);

        //$this->app->events->dispatch(new Events\HttpResponseCreated($response));

        return $returnRaw ? $response : $this->castResponseToType($response, $this->app->config->get('response_type'));
    }
    public function request2(string $url, string $method = 'GET', array $options = [], $returnRaw = false)
    {
        if (empty($this->middlewares)) {
            $this->registerHttpMiddlewares();
        }

        $options['headers'] = $this->headers2;

        $response = $this->performRequest($url, $method, $options);

        //$this->app->events->dispatch(new Events\HttpResponseCreated($response));

        return $returnRaw ? $response : $this->castResponseToType($response, $this->app->config->get('response_type'));
    }

    /**
     * @param string $url
     * @param string $method
     * @param array  $options
     *
     * @return \NftZxinchainn\Tools\Http\Response
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestRaw(string $url, string $method = 'GET', array $options = [])
    {
        $options['headers'] = $this->headers;
        return Response::buildFromPsrResponse($this->request($url, $method, $options, true));
    }
    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares()
    {
        // retry
        $this->pushMiddleware($this->retryMiddleware(), 'retry');
    }

    /**
     * Return retry middleware.
     *
     * @return \Closure
     */
    protected function retryMiddleware()
    {
        return Middleware::retry(
            function (
                $retries,
                RequestInterface $request,
                ResponseInterface $response = null
            ) {
                // Limit the number of retries to 2
                if ($retries < $this->app->config->get('http.max_retries', 1) && $response && $body = $response->getBody()) {
                    // Retry on server errors
                    $response = json_decode($body, true);
                }
                return false;
            },
            function () {
                return abs($this->app->config->get('http.retry_delay', 500));
            }
        );
    }
}
