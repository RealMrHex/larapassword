<?php

namespace RealMrHex\larapassword\Http;

use Illuminate\Http\Request;
use RealMrHex\larapassword\Facades\WebAuthn;
use RealMrHex\larapassword\Events\AttestationSuccessful;
use RealMrHex\larapassword\Contracts\WebAuthnAuthenticatable;

trait RegistersWebAuthn
{
    use WebAuthnRules;

    /**
     * Returns a challenge to be verified by the user device.
     *
     * @param  \RealMrHex\larapassword\Contracts\WebAuthnAuthenticatable  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function options(WebAuthnAuthenticatable $user)
    {
        return response()->json(WebAuthn::generateAttestation($user));
    }

    /**
     * Registers a device for further WebAuthn authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \RealMrHex\larapassword\Contracts\WebAuthnAuthenticatable  $user
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request, WebAuthnAuthenticatable $user)
    {
        // We'll validate the challenge coming from the authenticator and instantly
        // save it into the credentials store. If the data is invalid we will bail
        // out and return a non-authorized response since we can't save the data.
        $validCredential = WebAuthn::validateAttestation(
            $request->validate($this->attestationRules()), $user
        );

        if ($validCredential) {
            $user->addCredential($validCredential);

            event(new AttestationSuccessful($user, $validCredential));

            return $this->credentialRegistered($user, $validCredential) ?? response()->noContent();
        }

        return response()->noContent(422);
    }

    /**
     * The user has registered a credential.
     *
     * @param  \RealMrHex\larapassword\Contracts\WebAuthnAuthenticatable  $user
     * @param  \Webauthn\PublicKeyCredentialSource  $credentials
     * @return void|mixed
     */
    protected function credentialRegistered($user, $credentials)
    {
        // ...
    }
}