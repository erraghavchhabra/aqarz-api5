<?php
?>
<?php echo $__env->make('payment.layouts.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<section class="nav">
    <ul>
        <li class="active lead"> طريقة الدفع</li>
        <li class="lead"> ادفع</li>
        <li class="lead"> تم</li>
    </ul>
</section>
<section class="confirmation">
    <label>تأكيد عملية الدفع</label>
</section>
<section class="order-info">
    <ul class="items">
        <span>
            <i class="icon icon-bag"></i>
            <label class="lead" for="">طلبك</label>
        </span>
        <li>تفاصيل الطلب</li>
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
            <span class="curreny">ريال</span> <?php echo e(sprintf("%.2f",$totalAmount)); ?>    </li>
    </ul>
    <ul class="items">
        <span>
            <i class="icon icon-bag"></i>
            <label class="lead" for="">طريقة الدفع</label>
        </span>
        <li><?php echo e($objFort->getPaymentOptionName($paymentMethod)); ?></li>


    </ul>
</section>
<?php if($paymentMethod == 'cc_merchantpage' || $paymentMethod == 'installments_merchantpage'): ?>




    <section class="merchant-page-iframe">

        <div class="cc-iframe-display">
            <div id="div-pf-iframe" style="display:none">
                <div class="pf-iframe-container">
                    <div class="pf-iframe" id="pf_iframe_content">
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
<div class="h-seperator"></div>

<section class="actions">
    <a class="back" id="btn_back" href="">رجوع</a>
</section>

<script type="text/javascript" src="<?php echo e(asset('img_pay/vendors/jquery.min.js')); ?>"></script>
<script type="text/javascript" src="<?php echo e(asset('img_pay/js/checkout.js')); ?>"></script>
<script type="text/javascript">
  $(document).ready(function () {
    var paymentMethod = '<?php echo e($paymentMethod); ?>';

    //load merchant page iframe
    if (paymentMethod == 'cc_merchantpage' || paymentMethod == 'installments_merchantpage') {

      getPaymentPage(paymentMethod,'<?php echo e($r); ?>','<?php echo e($uuid); ?>');
    }
  });
</script>
<?php echo $__env->make('payment.layouts.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
