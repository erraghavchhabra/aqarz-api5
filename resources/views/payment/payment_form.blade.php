@extends('layouts.app')

@section('content')
<div class="pricing-area form-dxx">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="congrets-card form-card">
                    <div class="card-form-left">
                        <img src="{{asset('site/assets/img/card.png')}}" alt="">
                    </div>
                    <div class="card-fotm-box">
                        <form action="">
                            <div class="single-form">
                                <label for="">اختر طريقة الدفع</label>
                                <select  name="payment_method" id="payment_method">
                                    <option value="">اختر طريقة الدفع</option>
                                    <option name="payment_option" value="cc_merchantpage" >بطاقة ائتمانية / مدى</option>
                                    <option value="">STC</option>

                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<section class="actions">
    <a class="back" href="#">Back</a>
    <a class="continue" id="btn_continue" href="javascript:void(0)">Continue</a>
</section>
@endsection

@push('js')
<script>
  $('#payment_method').change( function() {




    var paymentMethod = this.value ;

    if (paymentMethod == '' || paymentMethod === undefined || paymentMethod === null) {
      alert('Pelase Select Payment Method!');
      return;
    }
    if (paymentMethod == 'cc_merchantpage') {




      $.ajax({
        url: '{{url('order')}}?payment_method=' + paymentMethod + '&uuid={{$uuid}}&r=getPaymentPage',
        type: 'get',
        headers: {
          'X-CSRF-TOKEN': token_app,
        },
        dataType: 'json',
       // data: {paymentMethod: paymentMethod,r:r,uuid:uuid},
        success: function (response) {


          if (paymentMethod == 'cc_merchantpage' || paymentMethod == 'installments_merchantpage') {

            getPaymentPage(paymentMethod,response.r,response.uuid);
          }


          console.log(response);


          return 111;

          /*  alert(1111);
            return;*/
          if (response.form) {
            $('body').append(response.form);


            if(response.paymentMethod == 'cc_merchantpage' || response.paymentMethod == 'installments_merchantpage') {
              showMerchantPage(response.url);
            }
            else if(response.paymentMethod == 'cc_merchantpage2') {
              var expDate = $('#payfort_fort_mp2_expiry_year').val()+''+$('#payfort_fort_mp2_expiry_month').val();
              var mp2_params = {};
              mp2_params.card_holder_name = $('#payfort_fort_mp2_card_holder_name').val();
              mp2_params.card_number = $('#payfort_fort_mp2_card_number').val();
              mp2_params.expiry_date = expDate;
              mp2_params.card_security_code = $('#payfort_fort_mp2_cvv').val();
              $.each(mp2_params, function(k, v){
                $('<input>').attr({
                  type: 'hidden',
                  id: k,
                  name: k,
                  value: v
                }).appendTo('#payfort_payment_form');
              });
              $('#payfort_payment_form input[type=submit]').click();
            }
            else{
              $('#payfort_payment_form input[type=submit]').click();
            }
          }
        }
      });









   //  window.location.href = '{{url('order')}}?payment_method=' + paymentMethod + '&uuid={{$uuid}}&r=getPaymentPage';
    } else {
      getPaymentPage(paymentMethod);
    }
  });
</script>

    <script type="text/javascript" src="{{asset('img_pay/vendors/jquery.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('img_pay/js/jquery.creditCardValidator.js')}}"></script>
    <script type="text/javascript" src="{{asset('img_pay/js/checkout.js')}}"></script>
    <script type="text/javascript">

      $(document).ready(function () {

        $('#btn_continue').click(function () {



        });
      });
    </script>
@endpush
