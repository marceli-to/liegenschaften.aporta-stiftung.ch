@component('mail::message')
<h1>Wohnungsangebot {{$item->apartment->room->abbreviation}}-Zimmer, {{$item->apartment->building->street}}</h1>
<p>Vielen Dank für Ihre Rückmeldung, welche wir gerne wie folgt Bestätigen:</p>
<p>
@if ($item->accepted == 1)
– Ich habe Interesse an dieser Wohnung<br>
@if ($item->parking == 1)
– Ich habe Interesse an einem Abstellplatz in der Tiefgarage Eichbühlstrasse (Warteliste)
@endif
@elseif ($item->accepted == 2)
– Ich habe kein Interesse an diesem Angebot, bleibe aber für eine Wohnung in einer anderen Siedlung auf der Warteliste<br>
@elseif ($item->accepted == 0)
– Ich bin nicht mehr auf Wohungssuche, bitte löschen Sie meine Anmeldung<br>
@endif
</p>
<p>Freundliche Grüsse<br><br>{{ env('APP_NAME') }}<br>Camilla Walker</p>
@endcomponent