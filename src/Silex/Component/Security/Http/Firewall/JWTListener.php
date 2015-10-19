<?php

namespace Silex\Component\Security\Http\Firewall;

use HttpEncodingException;
use Silex\Component\Security\Core\Encoder\TokenEncoderInterface;
use Silex\Component\Security\Core\Exception\AuthenticationInvalidCredentialsException;
use Silex\Component\Security\Http\Token\JWTToken;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class JWTListener implements ListenerInterface {

    /**
     * @var TokenStorageInterface
     */
    protected $securityContext;

    /**
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @var TokenEncoderInterface
     */
    protected $encode;

    /**
     * @var array
     */
    protected $options;

    /**
     * Class constructor
     *
     * @param TokenStorageInterface $securityContext
     * @param AuthenticationManagerInterface $authenticationManager
     * @param TokenEncoderInterface $encoder
     * @param array $options
     */
    public function __construct(TokenStorageInterface $securityContext, AuthenticationManagerInterface $authenticationManager, TokenEncoderInterface $encoder, array $options){
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->encode = $encoder;
        $this->options = $options;
    }

    /**
     * This interface must be implemented by firewall listeners.
     *
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event){
        $request = $event->getRequest();
        $requestToken = $this->getToken(
            $request->headers->get($this->options['header_name'], null)
        );

        if (!empty($requestToken)) {
            try {
                $decoded = $this->encode->decode($requestToken);

                $token = new JWTToken();
                $token->setTokenContext($decoded);
                $token->setUsernameClaim($this->options['username_claim']);

                $authToken = $this->authenticationManager->authenticate($token);
                $this->securityContext->setToken($authToken);

            } catch (HttpEncodingException $e) {
                throw new AuthenticationInvalidCredentialsException();
            } catch (\UnexpectedValueException $e) {
                throw new AuthenticationInvalidCredentialsException();
            }
        }
    }

    /**
     * Convert token with prefix to normal token
     *
     * @param $requestToken
     *
     * @return string
     */
    protected function getToken($requestToken){
        if (null === $requestToken && null !== $this->options['token_prefix']) {
            return $requestToken;
        }

        if (false !== strpos($requestToken, $this->options['token_prefix'])) {
            $requestToken = trim(str_replace($this->options['token_prefix'], "", $requestToken));
        }

        return $requestToken;
    }
}
