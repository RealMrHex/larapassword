<?php

namespace RealMrHex\larapasswordwor\Eloquent\Casting;

use Webauthn\TrustPath\TrustPathLoader;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class TrustPathCast implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return \Webauthn\TrustPath\TrustPath
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return TrustPathLoader::loadTrustPath(json_decode($value, true));
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  \Webauthn\TrustPath\TrustPath|array  $value
     * @param  array  $attributes
     * @return string
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return json_encode($value);
    }
}