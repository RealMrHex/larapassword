<?php

namespace RealMrHex\larapasswordwor\Http;

use Illuminate\Http\Request;
use RealMrHex\larapasswordwor\Facades\WebAuthn;
use RealMrHex\larapasswordwor\Contracts\WebAuthnAuthenticatable;

trait ConfirmsWebAuthn
{
    use WebAuthnRules;

    /**
     * Display the password confirmation view.
     *
     * @return \Illuminate\View\View
     */
    public function showConfirmForm()
    {
        return view('larapasswordwor::confirm');
    }

    /**
     * Return a request to assert the device.
     *
     * @param  \RealMrHex\larapasswordwor\Contracts\WebAuthnAuthenticatable  $user
     * @return \Webauthn\PublicKeyCredentialRequestOptions
     */
    public function options(WebAuthnAuthenticatable $user)
    {
        return WebAuthn::generateAssertion($user);
    }

    /**
     * Confirm the device assertion.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function confirm(Request $request)
    {
        $credential = $request->validate($this->assertionRules());

        if (WebAuthn::validateAssertion($credential)) {
            $this->resetAuthenticatorConfirmationTimeout($request);

            return response()->json([
                'redirectTo' => redirect()->intended($this->redirectPath())->getTargetUrl()
            ]);
        }

        return response()->noContent(422);
    }

    /**
     * Reset the password confirmation timeout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function resetAuthenticatorConfirmationTimeout(Request $request)
    {
        $request->session()->put('auth.webauthn.confirm', now()->timestamp);
    }

    /**
     * Get the post recovery redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
    }
}