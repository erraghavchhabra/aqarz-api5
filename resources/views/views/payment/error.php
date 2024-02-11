<?php $__env->startSection('content'); ?>
    <div class="pricing-area congrats-box-card">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="congrets-card congratulation">
                        <div class="card-box">
                            <img src="<?php echo e(asset('site/assets/img/cong.png')); ?>" alt="">
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>