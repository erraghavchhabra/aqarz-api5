<?php echo $__env->make('payment.layouts.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <section class="nav">
                <ul>
                    <li class="lead" > طريقة الدفع</li>
                    <li class="active lead" > تم</li>
                </ul>
            </section>
            <section class="confirmation">
                <label class="failed" for="" >هناك مشكلة في عملية الدفع</label>
                <!-- <label class="failed" for="" >Failed</label> -->
                <small>هناك مشكلة اثناء عملية الدفع لديك محاولة اخرى لدفع وبعد ذلك سوف يتم الغاء الرابط ويمكنك طلب الدفع من خلال التطبيق مرة اخرى</small>
            </section>
            
            <div class="h-seperator"></div>
            
            <?php if(isset($_REQUEST['error_msg'])) { ?>
            <section>
                <div class="error"><?php echo e($_REQUEST['error_msg']); ?> </div>
            </section>
            <div class="h-seperator"></div>
            

            <?php } ?>

            <section class="actions">
                <a class="btm" href="">إعادة الطلب</a>
            </section>
<?php echo $__env->make('payment.layouts.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
