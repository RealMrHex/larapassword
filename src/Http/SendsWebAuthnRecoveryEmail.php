<?php

namespace RealMrHex\larapasswordwor\Http;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use RealMrHex\larapasswordwor\Facades\WebAuthn;

trait SendsWebAuthnRecoveryEmail
{
    /**
     * Show the Account Recovery form.
     *
     * @return \Illuminate\View\View|mixed
     */
    public function showDeviceLostForm()
    {
        return view('larapasswordwor::lost');
    }

    /**
     * Send a recovery email to the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sendRecoveryEmail(Request $request)
    {
        $credentials = $request->validate($this->recoveryRules());

        $response = WebAuthn::sendRecoveryLink($credentials);

        return $response === WebAuthn::RECOVERY_SENT
            ? $this->sendRecoveryLinkResponse($request, $response)
            : $this->sendRecoveryLinkFailedResponse($request, $response);
    }

    /**
     * The recovery credentials to retrieve through validation rules.
     *
     * @return array|string[]
     */
    protected function recoveryRules()
    {
        return [
            'email' => 'required|email',
        ];
    }

    /**
     * Get the response for a successful account recovery link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendRecoveryLinkResponse(Request $request, $response)
    {
        return $request->wantsJson()
            ? new JsonResponse(['message' => trans($response)], 200)
            : back()->with('status', trans($response));
    }

    /**
     * Get the response for a failed account recovery link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendRecoveryLinkFailedResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                'email' => [trans($response)],
            ]);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => trans($response)]);
    }
}