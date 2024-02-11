<table>
    <thead>
    <tr>
        <th>الرقم المعرف</th>
        <th>نوع العقار</th>
        <th>اسم المدينة</th>
        <th>اسماء الأحياء </th>

        <th>مدى  السعر</th>
        <th>مدى  مساحة الشارع</th>
        <th>عدد العروض</th>
        <th>اتجاه الواجهة</th>
        <th>حالة العقار</th>

    </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $requestItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($requestItem->uuid); ?></td>
            <td><?php echo e($requestItem->estate_type_name_web); ?></td>
            <td><?php echo e($requestItem->city_name_web); ?></td>
            <td><?php echo e($requestItem->neighborhood_name); ?></td>
            <td><?php echo e($requestItem->estate_price_range); ?></td>
            <td><?php echo e($requestItem->street_view_range); ?></td>
            <td><?php echo e($requestItem->offers()->count()); ?></td>
            <td><?php echo e($requestItem->dir_estate); ?></td>
            <td><?php echo e($requestItem->estate_status); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>