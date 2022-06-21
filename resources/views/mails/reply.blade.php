@component('mail::message')
<h1>Antwort Wohnungsangebot</h1>
<p>Das Angebot ({{$item->apartment->building->street}}, {{$item->apartment->room->abbreviation}}-Zimmer, {{$item->apartment->description}}) wurde beantwortet.</p>
<p style="padding: 12px 0"><a href="{{ url('/') . '/administration/angebote/' . $item->uuid}}" class="button button-primary">Antwort anzeigen</a></p>
@endcomponent