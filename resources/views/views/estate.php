<?php $__env->startPush('css'); ?>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">
    <style>

        img {
            max-width: 100%;
        }

        .preview {
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -webkit-flex-direction: column;
            -ms-flex-direction: column;
            flex-direction: column;
        }

        @media  screen and (max-width: 996px) {
            .preview {
                margin-bottom: 20px;
            }
        }

        .preview-pic {
            -webkit-box-flex: 1;
            -webkit-flex-grow: 1;
            -ms-flex-positive: 1;
            flex-grow: 1;
        }

        .preview-thumbnail.nav-tabs {
            border: none;
            margin-top: 15px;
        }

        .preview-thumbnail.nav-tabs li {
            width: 18%;
            margin-right: 2.5%;
        }

        .preview-thumbnail.nav-tabs li img {
            max-width: 100%;
            display: block;
        }

        .preview-thumbnail.nav-tabs li a {
            padding: 0;
            margin: 0;
        }

        .preview-thumbnail.nav-tabs li:last-of-type {
            margin-right: 0;
        }

        .tab-content {
            overflow: hidden;
        }

        .tab-content img {
            width: 100%;
            -webkit-animation-name: opacity;
            animation-name: opacity;
            -webkit-animation-duration: .3s;
            animation-duration: .3s;
        }

        .card {
            margin-top: 50px;
            background: #00cfff;
            padding: 3em;
            line-height: 1.5em;
        }

        @media  screen and (min-width: 997px) {
            .wrapper {
                display: -webkit-box;
                display: -webkit-flex;
                display: -ms-flexbox;
                display: flex;
            }
        }

        .details {
            display: -webkit-box;
            display: -webkit-flex;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -webkit-flex-direction: column;
            -ms-flex-direction: column;
            flex-direction: column;
        }

        .colors {
            -webkit-box-flex: 1;
            -webkit-flex-grow: 1;
            -ms-flex-positive: 1;
            flex-grow: 1;
        }

        .product-title, .price, .sizes, .colors {
            text-transform: UPPERCASE;
            font-weight: bold;
        }

        .checked, .price span {
            color: #ff9f1a;
        }

        .product-title, .rating, .product-description, .price, .vote, .sizes {
            margin-bottom: 15px;
        }

        .product-title {
            margin-top: 0;
        }

        .size {
            margin-right: 10px;
        }

        .size:first-of-type {
            margin-left: 40px;
        }

        .color {
            display: inline-block;
            vertical-align: middle;
            margin-right: 10px;
            height: 2em;
            width: 2em;
            border-radius: 2px;
        }

        .color:first-of-type {
            margin-left: 20px;
        }

        .add-to-cart, .like {
            background: #ff9f1a;
            padding: 1.2em 1.5em;
            border: none;
            text-transform: UPPERCASE;
            font-weight: bold;
            color: #fff;
            -webkit-transition: background .3s ease;
            transition: background .3s ease;
        }

        .add-to-cart:hover, .like:hover {
            background: #b36800;
            color: #fff;
        }

        .not-available {
            text-align: center;
            line-height: 2em;
        }

        .not-available:before {
            font-family: fontawesome;
            content: "\f00d";
            color: #fff;
        }

        .orange {
            background: #ff9f1a;
        }

        .green {
            background: #85ad00;
        }

        .blue {
            background: #0076ad;
        }

        .tooltip-inner {
            padding: 1.3em;
        }

        @-webkit-keyframes opacity {
            0% {
                opacity: 0;
                -webkit-transform: scale(3);
                transform: scale(3);
            }
            100% {
                opacity: 1;
                -webkit-transform: scale(1);
                transform: scale(1);
            }
        }

        @keyframes  opacity {
            0% {
                opacity: 0;
                -webkit-transform: scale(3);
                transform: scale(3);
            }
            100% {
                opacity: 1;
                -webkit-transform: scale(1);
                transform: scale(1);
            }
        }

        .hero-area2 {
            /* height: 100vh; */
            align-items: center;
            justify-content: center;
            padding-top: 207px;
            display: flex;
        }
    </style>
<?php $__env->stopPush(); ?>


<?php $__env->startSection('content'); ?>


    <div class="hero-area2">

        <div class="container">
            <div class="card">
                <div class="container-fliud">
                    <div class="wrapper row">
                        <div class="preview col-md-6">

                            <div class="preview-pic tab-content">
                                <div class="tab-pane active" id="pic-1"><img width="250px" src="<?php echo e($estate->first_image); ?>"/>
                                </div>


                                <?php $__currentLoopData = $estate->EstateFile; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $EstateFileItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="tab-pane" id="pic-<?php echo e($loop->index+1); ?>"><img src="<?php echo e($EstateFileItem->file); ?>"/></div>
                               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <ul class="preview-thumbnail nav nav-tabs">

                                <?php $__currentLoopData = $estate->EstateFile; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $EstateFileItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="<?php if($loop->index==0): ?>active <?php endif; ?>">
                                    <a data-target="#pic-<?php echo e($loop->index+1); ?>" data-toggle="tab"><img src="<?php echo e($EstateFileItem->file); ?>"/></a>
                                </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </ul>

                        </div>
                        <div class="details col-md-6">
                            <h3 class="product-title"><?php echo e($estate->estate_type_name_web); ?></h3>
                            <div class="rating">
                                <div class="stars">
                                    <?php echo rate($estate->user->rate); ?>

                                </div>

                            </div>
                            <p class="product-description"><?php echo e($estate->neighborhood_name_web); ?> <?php echo e($estate->city_name_web); ?></p>
                            <h4 class="price">السعر:
                                <span><?php echo e($estate->total_price); ?></span>
                            </h4>
                            <p class="vote"><strong><?php echo e($estate->address); ?></strong></p>
                            <h5 class="sizes">وسائل الراحة:

                                <?php $__currentLoopData = $estate->comforts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comfortsItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="size" data-toggle="tooltip" title="small"><?php echo e($comfortsItem->name_ar); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </h5>


                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>




<?php $__env->stopSection(); ?>



<!-------- Hero Area Start ---------->


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>