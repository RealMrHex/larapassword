<?php

namespace RealMrHex\larapasswordwor\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use RealMrHex\larapasswordwor\WebAuthn\WebAuthnAssertValidator;

class EloquentWebAuthnProvider extends EloquentUserProvider
{
    /**
     * If it should fallback to password credentials whenever possible.
     *
     * @var bool
     */
    protected $fallback;

    /**
     * WebAuthn assertion validator.
     *
     * @var \RealMrHex\larapasswordwor\WebAuthn\WebAuthnAssertValidator
     */
    protected $validator;

    /**
     * Create a new database user provider.
     *
     * @param  \Illuminate\Contracts\Config\Repository  $config
     * @param  \RealMrHex\larapasswordwor\WebAuthn\WebAuthnAssertValidator  $validator
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @param  string  $model
     */
    public function __construct(ConfigContract $config,
                                WebAuthnAssertValidator $validator,
                                HasherContract $hasher,
                                $model)
    {
        $this->fallback = $config->get('larapasswordwor.fallback');
        $this->validator = $validator;

        parent::__construct($hasher, $model);
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|\RealMrHex\larapasswordwor\Contracts\WebAuthnAuthenticatable|null|void
     */
    public function retrieveByCredentials(array $credentials)
    {
        if ($this->isSignedChallenge($credentials) && $id = $this->binaryID($credentials['rawId'])) {
            return $this->model::getFromCredentialId($id);
        }

        return parent::retrieveByCredentials($credentials);
    }

    /**
     * Transforms the raw ID string into a binary string.
     *
     * @param  string  $rawId
     * @return null|string
     */
    protected function binaryID(string $rawId)
    {
        return base64_decode(strtr($rawId, '-_', '+/'), true);
    }

    /**
     * Check if the credentials are for a public key signed challenge
     *
     * @param  array  $credentials
     * @return bool
     */
    protected function isSignedChallenge(array $credentials)
    {
        return isset($credentials['id'], $credentials['rawId'], $credentials['type'], $credentials['response']);
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|\RealMrHex\larapasswordwor\Contracts\WebAuthnAuthenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials($user, array $credentials)
    {
        if ($this->isSignedChallenge($credentials)) {
            return (bool)$this->validator->validate($credentials);
        }

        // If the fallback is enabled, we will validate the credential password.
        if ($this->fallback) {
            return parent::validateCredentials($user, $credentials);
        }

        return false;
    }
}