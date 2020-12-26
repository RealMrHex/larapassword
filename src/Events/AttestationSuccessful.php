<?php

namespace RealMrHex\larapasswordwor\Events;

use Webauthn\PublicKeyCredentialSource;
use RealMrHex\larapasswordwor\Contracts\WebAuthnAuthenticatable;

class AttestationSuccessful
{
    /**
     * The user who registered a new set of credentials.
     *
     * @var \RealMrHex\larapasswordwor\Contracts\WebAuthnAuthenticatable
     */
    public $user;

    /**
     * The credentials registered.
     *
     * @var \Webauthn\PublicKeyCredentialSource
     */
    public $credential;

    /**
     * Create a new Event instance.
     *
     * @param  \RealMrHex\larapasswordwor\Contracts\WebAuthnAuthenticatable  $user
     * @param  \Webauthn\PublicKeyCredentialSource  $credential
     * @return void
     */
    public function __construct(WebAuthnAuthenticatable $user, PublicKeyCredentialSource $credential)
    {
        $this->user = $user;
        $this->credential = $credential;
    }
}