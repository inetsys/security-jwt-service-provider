<?php

namespace Silex\Component\Security\Core\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * AuthenticationInvalidCredentialsException is thrown when an authentication is rejected
 * because received token is invalid
 */
class AuthenticationInvalidCredentialsException extends AuthenticationException {
    /**
     * {@inheritdoc}
     */
    public function getMessageKey(){
        return 'Authentication credentials are invalid.';
    }
}
