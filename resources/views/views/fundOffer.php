<table>
    <thead>
    <tr>
        <th>الرقم المعرف</th>
        <th>اسم المستفيد</th>
        <th>رقم المستفيد</th>
        <th>اسم المكتب  </th>
        <th>نوع العقار</th>
        <th>مدينة العقار</th>


    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $offers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $offersItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($offersItem->uuid); ?></td>
            <td><?php echo e($offersItem->beneficiary_name); ?></td>
            <td><?php echo e($offersItem->beneficiary_mobile); ?></td>
            <td><?php echo e($offersItem->provider->name); ?></td>
            <td><?php echo e($offersItem->estate->estate_type_name); ?></td>
            <td><?php echo e($offersItem->estate->city_name); ?></td>


        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>