<?php
?>
@include('payment.layouts.header')


<section class="nav">
    <ul>
        <li class="active lead"> طريقة الدفع</li>
        <li class="lead"> ادفع</li>
        <li class="lead"> تم</li>
    </ul>
</section>

<section class="order-info">
    <ul class="items">
        <span>
            <i class="icon icon-bag"></i>
            <label class="lead" for="">طلبك</label>
        </span>
        <li>{{$itemName}}</li>
        <!-- <li>Lorem ipsum dolor sit amet, consectetur adipisicing elit. A ex magni delectus aliquam debitis</li> -->
    </ul>
    <ul>
        <li>
            <div class="v-seperator"></div>
        </li>
    </ul>
    <ul class="price">
        <span>
            <i class="icon icon-tag"></i>
            <label class="lead" for="">السعر</label>
        </span>

        <li>
            <span class="curreny">ريال</span> {{sprintf("%.2f",$totalAmount)}}    </li>
    </ul>
</section>

<div class="h-seperator"></div>

<section class="payment-method">
    <label class="lead" for="">
        أختر طريقة الدفع المناسبة لك</small>
    </label>
    <ul>

        <li>
            <input id="po_cc_merchantpage" type="radio" name="payment_option" value="cc_merchantpage" style="display: none">
            <label class="payment-option" for="po_cc_merchantpage">
                <img src="{{asset('img_pay/cc.png')}}" alt="">
                <span class="name">ادفع عن طريق الباي فورت</span>
                <em class="seperator hidden"></em>
                <div class="demo-container hidden"> <!--  Area for the iframe section -->
                    <iframe src="" frameborder="0"></iframe>
                </div>

            </label>
        </li>

    </ul>
</section>

<div class="h-seperator"></div>

<section class="actions">
    <a class="back" href="#">Back</a>
    <a class="continue" id="btn_continue" href="javascript:void(0)">Continue</a>
</section>
<script type="text/javascript" src="{{asset('img_pay/vendors/jquery.min.js')}}"></script>
<script type="text/javascript" src="{{asset('img_pay/js/jquery.creditCardValidator.js')}}"></script>
<script type="text/javascript" src="{{asset('img_pay/js/checkout.js')}}"></script>
<script type="text/javascript">
  $(document).ready(function () {
    $('input:radio[name=payment_option]').click(function () {
      $('input:radio[name=payment_option]').each(function () {
        if ($(this).is(':checked')) {
          $(this).addClass('active');
          $(this).parent('li').children('label').css('font-weight', 'bold');
          $(this).parent('li').children('div.details').show();
        } else {
          $(this).removeClass('active');
          $(this).parent('li').children('label').css('font-weight', 'normal');
          $(this).parent('li').children('div.details').hide();
        }
      });
    });
    $('#btn_continue').click(function () {


      var paymentMethod = $('input:radio[name=payment_option]:checked').val();

      if (paymentMethod == '' || paymentMethod === undefined || paymentMethod === null) {
        alert('Pelase Select Payment Method!');
        return;
      }
      if (paymentMethod == 'cc_merchantpage') {

        window.location.href = '{{url('order')}}?payment_method=' + paymentMethod + '&uuid={{$uuid}}&r=getPaymentPage';
      } else {
        getPaymentPage(paymentMethod);
      }
    });
  });
</script>
@include('payment.layouts.footer')