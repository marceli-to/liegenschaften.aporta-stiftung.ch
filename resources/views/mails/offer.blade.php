@component('mail::message')
<h1>Wohnungsangebot {{$collection->estate->description}}</h1>
<p>Guten Tag {{$collection->salutation}} {{$collection->name}}</p>
<p>Sie haben sich für eine Wohnung in unserem Neubau «Eglistrasse» interessiert. Es freut uns Ihnen mitzuteilen, dass wir ein passendes Angebot für Sie haben.</p>
<p>Sämtliche Informationen zu unserem Angebot sowie die Möglichkeit einer direkten Rückmeldung, finden Sie unter dem untenstehenden Link.</p>
<p><strong>Achtung das Angebot ist nur 5 Tage gültig</strong>, wir bitten Sie um schnelle Rückmeldung. Weitere Angebote im Neubau «Eglistrasse» gibt es nicht. Die Vermietung erfolgt <strong>ohne</strong> Wohnungsbesichtigung.</p>
<p style="padding: 12px 0"><a href="{{ env('APP_FRONTEND_URL') . '/angebot/' . $collection->uuid . '/' . md5($collection->email) }}" class="button button-primary" target="_blank">Unser Angebot</a></p>
<p>Freundliche Grüsse<br><br>{{ env('APP_NAME') }}<br>Camilla Walker</p>
@endcomponent