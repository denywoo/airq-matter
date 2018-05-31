<?php
namespace app\middlewares;

use Slim\Http\Response;
use Psr\Log\LoggerInterface;
use Slim\Http\Request;

class TokenAuthMiddleware
{
    private $_tokens;
    private $_logger;
    private $_tokenParamName;

    /**
     * TokenAuthMiddleware constructor.
     * @param string[] $tokens
     * @param LoggerInterface|null $logger
     * @param string $tokenParamName
     */
    public function __construct(array $tokens, LoggerInterface $logger = null, string $tokenParamName = 'token')
    {
        $this->_tokens = $tokens;
        $this->_logger = $logger;
        $this->_tokenParamName = $tokenParamName;
    }

    /**
     * middleware invokable class
     *
     * @param  Request $request PSR7 request
     * @param  Response $response PSR7 response
     * @param  callable $next Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $token = null;

        if ($request->hasHeader('Authorization')) {
            $authHeader = $request->getHeaderLine('Authorization');
            $isMatch = preg_match('/Token\s+(?<token>.*)/', $authHeader, $matches);
            if ($isMatch && array_key_exists('token', $matches)) {
                $token = $matches['token'];
            }
        } else {
            $token = $request->getParam($this->_tokenParamName);
        }

        if (empty($token) || !in_array($token, $this->_tokens)) {
            $this->logError("Access denied: incorrect token");
            return $response->withStatus(403);
        }

        $response = $next($request, $response);
        return $response;
    }

    private function logError(string $message, array $context = [])
    {
        if ($this->_logger === null) {
            return;
        }

        $this->_logger->error($message, $context);
    }
}