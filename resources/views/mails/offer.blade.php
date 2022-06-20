@component('mail::message')
<h1>Wohnungsangebot {{$collection->estate->description}}</h1>
<p>Guten Tag {{$collection->firstname}} {{$collection->name}}</p>
<p>Sie haben sich für eine Wohnung in unserem Neubau «Eglistrasse» interessiert. Es freut uns Ihnen mitzuteilen, dass wir ein passendes Angebot für Sie haben.</p>
<p>Sämtliche Informationen zu unserem Angebot sowie die Möglichkeit einer direkten Rückmeldung, finden Sie unter dem untenstehenden Link.</p>
<p><strong>Achtung das Angebot ist nur 5 Tage gültig,</strong> wir bitten Sie um schnelle Rückmeldung. Weitere Angebote im Neubau «Eglistrasse» gibt es nicht.</p>
<p><strong>Block A:</strong><br>Vertragsbeginn ist der 1. November 2022. Die Wohnungsübergabe wird bereits im Oktober erfolgen. Die Wohnungen werden etappiert vermietet. Die Vermietung erfolgt ohne Wohnungsbesichtigung.</p>
<p><strong>Block B (ausser Hohlstrasse):</strong><br>Vertragsbeginn ist der 15. Februar 2023. Die Wohnungen werden etappiert vermietet. Die Vermietung erfolgt ohne Wohnungsbesichtigung. In der Überbauung (Eglistrasse 1/Hohlstrasse) befinden sich im Weiteren ein Kindergarten der Stadt Zürich sowie das Zürcher Lighthouse.</p>
<p><strong>Block B (Hohlstrasse 315):</strong><br>Vertragsbeginn ist der 1. März 2023.<br>Die Wohnungen werden etappiert vermietet. Die Vermietung erfolgt ohne Wohnungsbesichtigung. In der Überbauung (Eglistrasse 1/Hohlstrasse) befinden sich im Weiteren ein Kindergarten der Stadt Zürich sowie das Zürcher Lighthouse.</p>
<p style="padding: 16px 0"><a href="{{ route('offer.list', ['collection' => $collection->uuid, 'hash' => md5($collection->email)]) }}" class="button button-primary">Unser Angebot</a></p>
<p>Freundliche Grüsse<br><br>DR. STEPHAN À PORTA-STIFTUNG<br>Camilla Walker</p>
@endcomponent