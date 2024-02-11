<?php echo $__env->make('payment.layouts.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <section class="nav">
                <ul>
                    <li class="lead" > طريقة الدفع</li>
                    <li class="active lead" > تم</li>
                </ul>
            </section>
            <section class="confirmation">
                <label class="failed" for="" >لقد استنفذت محاولات الدفع</label>
                <!-- <label class="failed" for="" >Failed</label> -->
                <small>لقد استنفذت محاولات الدفع الخاص بك يرجى اعادة الطلب من خلال التطبيق</small>
            </section>



<?php echo $__env->make('payment.layouts.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
