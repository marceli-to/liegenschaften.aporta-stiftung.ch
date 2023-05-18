@component('mail::message')
<h1>Antwort Wohnungsangebot</h1>
<p>Unser Angebot für das Objekt {{$item->apartment->number}} ({{$item->apartment->room->abbreviation}}-Zimmer, {{$item->apartment->building->street}}, {{$item->apartment->description}}) wurde beantwortet.</p>
<p>Name:<br>{{ $item->collection->firstname }} {{ $item->collection->name }}</p>
<p>E-Mail:<br>{{ $item->collection->email }}</p>
<p>Objekt:<br>{{$item->apartment->number}}, {{$item->apartment->room->abbreviation}}-Zimmer, {{$item->apartment->building->street}}, {{$item->apartment->description}}</p>
<p>Antwort:<br>
@if ($item->accepted == 1)
– Ich habe Interesse an dieser Wohnung<br>
@if ($item->parking == 1)
– Ich habe Interesse an einem Abstellplatz in der Tiefgarage Eichbühlstrasse (Warteliste)
@endif
@elseif ($item->accepted == 2)
– Ich bin nicht mehr auf Wohungssuche, bitte löschen Sie meine Anmeldung<br>
@elseif ($item->accepted == 0)
– Ich habe kein Interesse an diesem Angebot, bleibe aber für eine Wohnung in einer anderen Siedlung auf der Warteliste<br><br>
Begründung:<br>
{{ $item->comment }}<br>
@endif
</p>
<p style="padding: 12px 0"><a href="{{ url('/') . '/administration/angebote/' . $item->uuid}}" class="button button-primary">Antwort anzeigen</a></p>
<p>Freundliche Grüsse<br><br>{{ env('APP_NAME') }}</p>
@endcomponent