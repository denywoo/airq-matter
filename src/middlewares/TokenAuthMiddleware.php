<?php
namespace app\middlewares;

use Psr\Log\LoggerInterface;

class TokenAuthMiddleware
{
    private $_tokens = [];
    private $_logger = null;

    /**
     * TokenAuthMiddleware constructor.
     * @param string[] $tokens
     * @param LoggerInterface|null $logger
     */
    public function __construct(array $tokens, LoggerInterface $logger = null)
    {
        $this->_tokens = $tokens;
        $this->_logger = $logger;
    }

    /**
     * middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface $response PSR7 response
     * @param  callable $next Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $queryParams = $request->getQueryParams();
        $token = $queryParams['token'];
        if (!in_array($token, $this->_tokens)) {
            $this->logError("Access denied incorrect token", $queryParams);
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