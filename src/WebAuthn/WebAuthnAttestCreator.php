<?php

namespace RealMrHex\larapasswordwor\WebAuthn;

use Illuminate\Http\Request;
use Webauthn\AuthenticatorSelectionCriteria;
use Illuminate\Contracts\Auth\Authenticatable;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialRpEntity as RelyingParty;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Cache\Factory as CacheFactoryContract;
use RealMrHex\larapasswordwor\Contracts\WebAuthnAuthenticatable;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;

class WebAuthnAttestCreator
{
    /**
     * Application cache.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Application as the Relying Party.
     *
     * @var \Webauthn\PublicKeyCredentialRpEntity
     */
    protected $relyingParty;

    /**
     * Authenticator filters.
     *
     * @var \Webauthn\AuthenticatorSelectionCriteria
     */
    protected $criteria;

    /**
     * Parameters for the credentials creation.
     *
     * @var \RealMrHex\larapasswordwor\WebAuthn\PublicKeyCredentialParametersCollection
     */
    protected $parameters;

    /**
     * Custom extensions the user can accept from the client itself.
     *
     * @var \Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs
     */
    protected $extensions;

    /**
     * Challenge time-to-live, in milliseconds.
     *
     * @var int
     */
    protected $timeout;

    /**
     * Number of bytes to create for a random challenge.
     *
     * @var mixed
     */
    protected $bytes;

    /**
     * If the devices should be further verified.
     *
     * @var string
     */
    protected $conveyance;

    /**
     * If only one credential is allowed for the user in the device.
     *
     * @var bool
     */
    protected $unique;

    /**
     * Laravel HTTP Request.
     *
     * @var \Illuminate\Http\Request
     */
    protected $laravelRequest;

    /**
     * WebAuthnAttestation constructor.
     *
     * @param  \Illuminate\Contracts\Config\Repository  $config
     * @param  \Illuminate\Contracts\Cache\Factory  $cache
     * @param  \Webauthn\PublicKeyCredentialRpEntity  $relyingParty
     * @param  \Webauthn\AuthenticatorSelectionCriteria  $criteria
     * @param  \RealMrHex\larapasswordwor\WebAuthn\PublicKeyCredentialParametersCollection  $parameters
     * @param  \Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs  $extensions
     * @param  \Illuminate\Http\Request  $request
     */
    public function __construct(ConfigContract $config,
                                CacheFactoryContract $cache,
                                RelyingParty $relyingParty,
                                AuthenticatorSelectionCriteria $criteria,
                                PublicKeyCredentialParametersCollection $parameters,
                                AuthenticationExtensionsClientInputs $extensions,
                                Request $request)
    {
        $this->cache = $cache->store($config->get('larapasswordwor.cache'));
        $this->relyingParty = $relyingParty;
        $this->criteria = $criteria;
        $this->parameters = $parameters;
        $this->extensions = $extensions;
        $this->laravelRequest = $request;

        $this->timeout = $config->get('larapasswordwor.timeout') * 1000;
        $this->bytes = $config->get('larapasswordwor.bytes');
        $this->conveyance = $config->get('larapasswordwor.conveyance');
        $this->unique = $config->get('larapasswordwor.unique');
    }

    /**
     * Retrieves an Attestation if it exists.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|\RealMrHex\larapasswordwor\Contracts\WebAuthnAuthenticatable  $user
     * @return \Webauthn\PublicKeyCredentialCreationOptions|null
     */
    public function retrieveAttestation($user)
    {
        return $this->cache->get($this->cacheKey($user));
    }

    /**
     * Generates a new Attestation for a given user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|\RealMrHex\larapasswordwor\Contracts\WebAuthnAuthenticatable  $user
     * @return mixed|\Webauthn\PublicKeyCredentialCreationOptions
     */
    public function generateAttestation($user)
    {
        $attestation = $this->makeAttestationRequest($user);

        $this->cache->put($this->cacheKey($user), $attestation, $this->timeout);

        return $attestation;
    }

    /**
     * Returns the challenge that is remembered specifically for the user.
     *
     * @param  \RealMrHex\larapasswordwor\Contracts\WebAuthnAuthenticatable|\Illuminate\Contracts\Auth\Authenticatable  $user
     * @return mixed
     */
    protected function makeAttestationRequest($user)
    {
        return new PublicKeyCredentialCreationOptions(
            $this->relyingParty,
            $user->userEntity(),
            random_bytes($this->bytes),
            $this->parameters->all(),
            $this->timeout,
            $this->getExcludedCredentials($user),
            $this->criteria,
            $this->conveyance,
            $this->extensions
        );
    }

    /**
     * Returns the cache key to remember the challenge for the user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return string
     */
    protected function cacheKey(Authenticatable $user)
    {
        return implode('|', [
            'larapasswordwor.attestation',
            get_class($user) . ':' . $user->getAuthIdentifier(),
            sha1($this->laravelRequest->getHttpHost() . '|' . $this->laravelRequest->ip()),
        ]);
    }

    /**
     * Return the excluded credentials if the configuration demands it.
     *
     * @param  \RealMrHex\larapasswordwor\Contracts\WebAuthnAuthenticatable  $user
     * @return array
     */
    protected function getExcludedCredentials(WebAuthnAuthenticatable $user)
    {
        return $this->unique ? $user->attestationExcludedCredentials() : [];
    }
}