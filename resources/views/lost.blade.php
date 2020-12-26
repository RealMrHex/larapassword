@extends('larapassword::layout')

@section('title', trans('larapassword::recovery.title'))

@section('body')
    <form id="form" action="{{ route('webauthn.lost.send') }}" method="post">
        @csrf
        <h2 class="card-title h5 text-center">{{ trans('larapassword::recovery.title') }}</h2>
        <hr>
        <p>{{ trans('larapassword::recovery.description') }}</p>
        @if($errors->any())
            <div class="alert alert-danger small">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @elseif(session('status'))
            <div class="alert alert-success small">
                {{ session('status') }}
            </div>
        @endif
        <div class="form-group pb-3">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" class="form-control" placeholder="john.doe@mail.com" required>
            <small class="form-text text-muted">{{ trans('larapassword::recovery.details') }}</small>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-lg">{{ trans('larapassword::recovery.button.send') }}</button>
        </div>
    </form>
@endsection