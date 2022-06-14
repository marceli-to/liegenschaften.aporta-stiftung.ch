@extends('layout.web')
@section('seo_title', $collection->estate->description_long . ' • Veranstaltungen')
@section('seo_description', '')
@section('content')
<div id="collection">
  <collection-component :estate="'{{ $collection->estate->description_long }}, {{ $collection->estate->city }}'" />
</div>
@endsection