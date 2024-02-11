@extends('layouts.app')


<div class="pricing-area price-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="pricing-boxs">
                    <div class="pricing-title">
                        <h2>قم باختيار الاشتراك المناسب لك</h2>
                    </div>
                    <div class="pricing-main">
                        <div class="row">
                            @foreach($plans as $plansItem)
                            <div class="col-lg-4 col-md-4">
                                <div class="pricing-table">
                                    <div class="pricing-thumb">
                                        <img src="{{asset('site/assets/img/pricing-thumb.png')}}" alt="">
                                    </div>
                                    <div class="pricing-text">
                                        <h4>باقــــة {{$plansItem->name_ar}}</h4>
                                        <h2>{{$plansItem->price}}</h2>
                                        <span>ريـال سعـودي</span>
                                        <a href="{{route('subscribePlan',[$uuid,$plansItem->id])}}">اشتراك</a>
                                    </div>
                                </div>
                            </div>
                          @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>