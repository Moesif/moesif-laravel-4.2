<?php namespace MoesifLaravel42\MoesifLaravel\Middleware;

use Config;
use DateTime;
use DateTimeZone;
use Input;
use MoesifLaravel42\MoesifLaravel\Sender\MoesifApi;
use Log;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Moesif implements HttpKernelInterface
{

    /**
     * The wrapped kernel implementation.
     *
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    protected $app;

    /**
     * Create a new Moesif instance.
     *
     * @param  \Symfony\Component\HttpKernel\HttpKernelInterface  $app
     * @return void
     */
    public function __construct(HttpKernelInterface $app)
    {
        $this->app = $app;
    }

    /**
     * Handle the given request and get the response.
     *
     * @implements HttpKernelInterface::handle
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  int   $type
     * @param  bool  $catch
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(SymfonyRequest $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        // do action before response
        $t = microtime(true);
        $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
        $startDateTime = new DateTime(date('Y-m-d H:i:s.'.$micro, $t));
        $startDateTime->setTimezone(new DateTimeZone("UTC"));

        // Handle on passed down request
        $response = $this->app->handle($request, $type, $catch);

        $applicationId = Config::get('moesif::config.application_id');
        $apiVersion = Config::get('moesif::config.apiVersion');
        $maskRequestHeaders = Config::get('moesif::config.maskRequestHeaders');
        $maskRequestBody = Config::get('moesif::config.maskRequestBody');
        $maskResponseHeaders = Config::get('moesif::config.maskResponseHeaders');
        $maskResponseBody = Config::get('moesif::config.maskResponseBody');
        $identifyUserId = Config::get('moesif::config.identifyUserId');
        $identifySessionId = Config::get('moesif::config.identifySessionId');
        $getMetadata = Config::get('moesif::config.getMetadata');
        $skip = Config::get('moesif::config.skip');
        $debug = Config::get('moesif::config.debug');
        if (is_null($debug)) {
            $debug = false;
        }
        // if skip is defined, invoke skip function.
        if (!is_null($skip)) {
            if ($skip($request, $response)) {
                if ($debug) {
                    Log::info('[Moesif] : skip function returned true, so skipping this event.');
                }
                return $response;
            }
        }
        if (!$applicationId) {
            throw new \Exception('Moesif application_id is missing. Please provide application_id in package config file.');
        }
        $requestData = [
            'time' => $startDateTime->format('Y-m-d\TH:i:s.uP'),
            'verb' => $request->method(),
            'uri' => $request->fullUrl(),
            'ip_address' => $request->ip(),
        ];
        if (!is_null($apiVersion)) {
            $requestData['api_version'] = $apiVersion($request, $response);
        }
        $requestHeaders = [];
        foreach ($request->headers->keys() as $key) {
            $requestHeaders[$key] = (string) $request->headers->get($key);
        }
        if (!is_null($maskRequestHeaders)) {
            $requestData['headers'] = $maskRequestHeaders($requestHeaders);
        } else {
            $requestData['headers'] = $requestHeaders;
        }
        $requestContent = $request->getContent();
        if (!is_null($requestContent)) {
            $requestBody = Input::all();
            if (is_null($requestBody)) {
                if ($debug) {
                    Log::info('[Moesif] : request body not be empty and not json, base 64 encode');
                }
                $requestData['body'] = base64_encode($requestContent);
                $requestData['transfer_encoding'] = 'base64';
            } else {
                if (!is_null($maskRequestBody)) {
                    $requestData['body'] = $maskRequestBody($requestBody);
                } else {
                    $requestData['body'] = $requestBody;
                }
            }
        }
        $endTime = microTime(true);
        $micro = sprintf("%06d", ($endTime - floor($endTime)) * 1000000);
        $endDateTime = new DateTime(date('Y-m-d H:i:s.'.$micro, $endTime));
        $endDateTime->setTimezone(new DateTimeZone("UTC"));
        $responseData = [
            'time' => $endDateTime->format('Y-m-d\TH:i:s.uP'),
            'status' => $response->getStatusCode(),
        ];
        $responseContent = $response->getContent();
        if (!is_null($responseContent)) {
            $jsonBody = json_decode($response->getContent(), true);
            if (!is_null($jsonBody)) {
                if (!is_null($maskResponseBody)) {
                    $responseData['body'] = $maskResponseBody($jsonBody);
                } else {
                    $responseData['body'] = $jsonBody;
                }
            } else {
                if (!empty($responseContent)) {
                    $responseData['body'] = base64_encode($responseContent);
                    $responseData['transfer_encoding'] = 'base64';
                }
            }
        }
        $responseHeaders = [];
        foreach ($response->headers->keys() as $key) {
            $responseHeaders[$key] = (string) $response->headers->get($key);
        }
        if (!is_null($maskResponseHeaders)) {
            $responseData['headers'] = $maskResponseHeaders($responseHeaders);
        } else {
            $responseData['headers'] = $responseHeaders;
        }
        $data = [
            'request' => $requestData,
            'response' => $responseData,
        ];
        if (!is_null($identifyUserId)) {
            $data['user_id'] = $this->ensureString($identifyUserId($request, $response));
        }
        if (!is_null($identifySessionId)) {
            $data['session_token'] = $this->ensureString($identifySessionId($request, $response));
        } elseif ($request->hasSession()) {
            $data['session_token'] = $this->ensureString($request->session()->getId());
        }
        if (!is_null($getMetadata)) {
            $data['metadata'] = $getMetadata($request, $response);
        }
        $moesifApi = MoesifApi::getInstance($applicationId, [
            'fork' => true,
            'debug' => $debug,
        ]);
        $moesifApi->track($data);

        return $response;
    }

    /**
     * @param $item
     *
     * @return string
     */
    protected function ensureString($item)
    {
        if (is_null($item)) {
            return $item;
        }
        if (is_string($item)) {
            return $item;
        }
        return strval($item);
    }
}
