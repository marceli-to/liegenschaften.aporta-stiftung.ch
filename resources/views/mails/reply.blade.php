@component('mail::message')
<h1>Antwort Wohnungsangebot</h1>
<p>Hallo</p>
<p>Für die angebotene Wohnung {{$item->apartment->description}} ist eine Antwort eingetroffen:</p>
<table class="notification-table" cellspacing="0" cellpadding="0">
<thead>
<th>Liegenschaft</th>
<th>Adresse</th>
<th>Etage</th>
<th>Zimmer</th>
<th>Bezeichnung</th>
<th>M<sup>2</sup></th>
</thead>
<tbody>
<tr>
<td>{{$item->collection->estate->description}}</td>
<td>{{$item->apartment->building->street}}</td>
<td>{{$item->apartment->floor->abbreviation}}</td>
<td>{{$item->apartment->room->abbreviation}}</td>
<td>{{$item->apartment->description}}</td>
<td>{{$item->apartment->size}}</td>
</tr>
</tbody>
</table>
@endcomponent