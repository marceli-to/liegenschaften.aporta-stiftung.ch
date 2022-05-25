@extends('layout.guest')
@section('seo_title', 'E-Mail verifizieren')
@section('seo_description', '')
@section('content')
<h1>E-Mail verifizieren</h1>
@if (session('resent'))
  <x-alert type="success" message="Neuer Bestätigungslink gesendet. Bitte Posteingang prüfen." />
@endif
<p>Bevor Sie weiterfahren können, müssen Sie ihre E-Mail-Adresse bestätigen. Bitte prüfen Sie ihren Posteingang.</p>
<p>Falls Sie keine E-Mail erhalten haben, können Sie einen neuen Link anfordern:</p>
<form method="POST" action="{{ route('verification.resend') }}">
  @csrf
  <div class="form-action">
    <x-button label="Anfordern" name="request-link" btnClass="btn-primary" type="submit" />
  </div>
</form>
@endsection