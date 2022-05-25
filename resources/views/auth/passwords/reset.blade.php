@extends('layout.guest')
@section('seo_title', 'Passwort zurücksetzen')
@section('content')
<h1>Passwort zurücksetzen</h1>
<p>{{__('messages.password_reset')}}</p>
@if ($errors->any())
  <x-alert type="danger" message="{{__('messages.general_error')}}" />
@endif
@if (session('status'))
  <x-alert type="success" message="{{ session('status') }}" />
@endif
<form method="POST" action="{{ route('password.update') }}">
  @csrf
  <input type="hidden" name="token" value="{{ $token }}">
  <x-text-field type="email" required name="email" placeholder="mail@beispiel.ch" />
  <x-text-field type="password" required name="password" placeholder="Passwort" />
  <x-text-field type="password" name="password_confirmation" placehodler="Passwort bestätigen" required autocomplete="new-password" />
  <div class="form-action">
    <x-button label="Zurücksetzen" name="reset_password" btnClass="btn-primary" type="submit" />
  </div>
</form>
@endsection

