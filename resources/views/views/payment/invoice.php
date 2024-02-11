<?php
?>
<style>
    body {
        background: grey;
        margin-top: 120px;
        margin-bottom: 120px;
    }
</style>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!------ Include the above in your HEAD tag ---------->

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="row p-5">
                        <div class="col-md-6">
                            <img src="http://via.placeholder.com/400x90?text=logo">
                        </div>

                        <div class="col-md-6 text-right">
                            <p class="font-weight-bold mb-1">Invoice <?php echo e($invoice->id); ?></p>
                            <p class="text-muted">Due to: <?php echo e($invoice->created_at); ?></p>
                        </div>
                    </div>

                    <hr class="my-5">

                    <div class="row pb-5 p-5">
                        <div class="col-md-6">
                            <p class="font-weight-bold mb-4">Client Information</p>
                            <p class="mb-1"><?php echo e($invoice->user->name); ?></p>

                        </div>

                        <div class="col-md-6 text-right">
                            <p class="font-weight-bold mb-4">Payment Details</p>
                            <p class="mb-1"><span class="text-muted">Fort ID: </span> <?php echo e($invoice->fort_id); ?></p>
                            <p class="mb-1"><span class="text-muted">Payment Type: </span> PayFort</p>
                            <p class="mb-1"><span class="text-muted">Name: </span> <?php echo e($invoice->user->name); ?></p>
                        </div>
                    </div>

                    <div class="row p-5">
                        <div class="col-md-12">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th class="border-0 text-uppercase small font-weight-bold">ID</th>
                                    <th class="border-0 text-uppercase small font-weight-bold">Item</th>
                                    <th class="border-0 text-uppercase small font-weight-bold">Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><?php echo e($invoice->id); ?></td>
                                    <td> <?php echo e($invoice->plan->name_en); ?></td>
                                    <td>SAR <?php echo e($invoice->plan->price); ?> </td>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="d-flex flex-row-reverse bg-dark text-white p-4">
                        <div class="py-3 px-5 text-right">
                            <div class="mb-2">Grand Total</div>
                            <div class="h2 font-weight-light">SAR <?php echo e($invoice->plan->price); ?></div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>


</div>


