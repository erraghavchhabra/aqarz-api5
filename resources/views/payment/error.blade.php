@extends('layouts.app')

@section('content')
    <div class="pricing-area congrats-box-card">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="congrets-card congratulation">
                        <div class="card-box">
                            <img src="{{asset('site/assets/img/cong.png')}}" alt="">
                            <h4>هناك مشكلة في الدفع</h4>
                            <p>
                                هناك مشكلة اثناء عملية الدفع لديك محاولة اخرى لدفع وبعد ذلك سوف يتم الغاء الرابط ويمكنك طلب الدفع من خلال التطبيق مرة اخرى

                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
