@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-2">
    @component('components.sidebar', ['categories' => $categories, 'major_categories' => $major_categories])
    @endcomponent
  </div>
  <div class="col-9">
    <h1>おすすめ商品</h1>
    <div class="row">
      @foreach ($recommend_products as $recommend_product)
        <div class="col-4">
          <a href="{{ route('products.show', $recommend_product) }}">
            @if ($recommend_product->image !== "")
              <img src="{{ asset($recommend_product->image) }}" class="img-thumbnail">
            @else
              <img src="{{ asset('img/dummy.png') }}" class="img-thumbnail">
            @endif
          </a>
          <div class="row">
            <div class="col-12">
              <p class="samuraimart-product-label mt-2 mb-0">
                {{ $recommend_product->name}}<br>
                <div class="d-flex align-items-center">
                  <div class="samuraimart-star-rating" data-rate="{{ round($recommend_product->averageRating() * 2) / 2 }}"></div>
                  <p class="mb-0 ms-2">{{ $recommend_product->averageRating() }}</p>
                </div>
                <label>￥{{ $recommend_product->price }}</label>
              </p>
            </div>
          </div>
        </div> 
      @endforeach  
    </div>

    <div class="d-flex justify-content-between">
      <h1>新着商品</h1>
      <a href="{{ route('products.index', ['sort' => 'id', 'direction' => 'desc']) }}">もっと見る</a>
    </div>
    <div class="row">
      @foreach ($recently_products as $recently_product)
        <div class="col-3">
          <a href="{{ route('products.show', $recently_product)}}">
            @if ($recently_product->image !== "")
              <img src="{{ asset($recently_product->image) }}" class="img-thumbnail">
            @else
              <img src="{{ asset('img/dummy.png') }}" class="img-thumbnail">
            @endif
          </a>    
          <div class="row">
            <div class="col-12">
              <p class="samuraimart-product-label mt-2 mb-0">
                {{ $recently_product->name }}<br>
                <div class="d-flex align-items-center">
                  <div class="samuraimart-star-rating" data-rate="{{ round($recently_product->averageRating() * 2) / 2 }}"></div>
                  <p class="mb-0 ms-2">{{ $recently_product->averageRating() }}</p>
                </div>
                <label>￥{{ $recently_product->price }}</label>
              </p>
            </div>
          </div>
        </div>
      @endforeach  
    </div>
  </div>
</div>
@endsection
