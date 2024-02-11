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
                            <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plansItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-lg-4 col-md-4">
                                <div class="pricing-table">
                                    <div class="pricing-thumb">
                                        <img src="<?php echo e(asset('site/assets/img/pricing-thumb.png')); ?>" alt="">
                                    </div>
                                    <div class="pricing-text">
                                        <h4>باقــــة <?php echo e($plansItem->name_ar); ?></h4>
                                        <h2><?php echo e($plansItem->price); ?></h2>
                                        <span>ريـال سعـودي</span>
                                        <a href="<?php echo e(route('subscribePlan',[$uuid,$plansItem->id])); ?>">اشتراك</a>
                                    </div>
                                </div>
                            </div>
                          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>