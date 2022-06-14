@component('mail::message')
<h1>Wohnungsangebot {{$collection->estate->description}}</h1>
<p>Guten Tag {{$collection->firstname}} {{$collection->name}}</p>
<p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quis aliquam nostrum optio nam tempore:</p>
<p><a href="{{ route('offer.list', ['collection' => $collection->uuid, 'hash' => md5($collection->email)]) }}" class="button button-primary">Unser Angebot</a></p>
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
@foreach($collection->items as $item)
<tr>
<td>
{{$collection->estate->description}}
</td>
<td>
{{$item->apartment->building->street}}
</td>
<td>
{{$item->apartment->floor->abbreviation}}
</td>
<td>
{{$item->apartment->room->abbreviation}}
</td>
<td>
{{$item->apartment->description}}
</td>
<td>
{{$item->apartment->size}}
</td>
</tr>
@endforeach
</tbody>
</table>
@endcomponent