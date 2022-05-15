@extends('layouts.login')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <img src="{{ asset('assets/images/undraw_remotely_2j6y.svg') }}" alt="Image" class="img-fluid">
            </div>
            <div class="col-md-6 contents">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="mb-4">
                            <h3>Sign In</h3>
                        </div>
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form-group first">
                                <label for="email">{{ __('Email Address') }}</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror


                            </div>
                            <div class="form-group last mb-4">
                                <label for="password">{{ __('Password') }}</label>
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password" required
                                    autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror

                            </div>

                            <div class="d-flex mb-5 align-items-center">
                                <label class="control control--checkbox mb-0"><span class="caption">
                                        {{ __('Remember Me') }}</span>
                                    <input type="checkbox" name="remember" id="remember" checked="checked"
                                        {{ old('remember') ? 'checked' : '' }} />
                                    <div class="control__indicator"></div>
                                </label>
                                @if (Route::has('password.request'))
                                    <span class="ml-auto"><a href="{{ route('password.request') }}"
                                            class="forgot-pass"> {{ __('Forgot Your Password?') }}
                                        </a>
                                    </span>
                                @endif

                            </div>

                            <button type="submit" class="btn btn-block btn-primary">
                                {{ __('Login') }}
                            </button>

                        </form>

                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
