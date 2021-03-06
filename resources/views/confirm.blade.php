@extends('larapasswordword::layout')

@section('title', __('Authenticator confirmation'))

@section('body')
    <form id="form">
        <h2 class="card-title h5 text-center">{{ __('Please confirm with your device before continuing') }}</h2>
        <hr>
        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-lg">
                {{ __('Confirm') }}
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/larapassword/js/larapassword.js') }}"></script>
    <script>
        const larapassword = new larapassword({
            login: '/webauthn/confirm',
            loginOptions: '/webauthn/confirm/options'
        });

        document.getElementById('form').addEventListener('submit', function (event) {
            event.preventDefault()

            larapassword.login()
                .then(response => window.location.replace(response.redirectTo))
                .catch(response => {
                    alert('{{ __('Confirmation unsuccessful, try again!') }}')
                    console.error('Confirmation unsuccessful', response);
                })
        })
    </script>
@endpush