


<!DOCTYPE html><html lang='ar'><head><meta charset='UTF-8'><title>رسالة جديدة</title></head><body>
<table style='width: 100%;'>
<thead style='text-align: center;'><tr><td style='border:none;' colspan='2'>
            <a href=<?php echo e($details['link']); ?>><img src=<?php echo e($details['logo']); ?> alt=''></a><br><br>
           </td></tr></thead><tbody><tr>
       <td style='border:none;'><strong>الاسم:</strong> <?php echo e($details['name']); ?></td>
    <td style='border:none;'><strong>البريد الالكتروني:</strong><?php echo e($details['from']); ?></td>
     </tr>
   <tr><td style='border:none;'><strong>العنوان:</strong> <?php echo e($details['subject']); ?></td></tr>
   <tr><td></td></tr>
    <tr><td colspan='2' style='border:none;'><?php echo e($details['text_msg']); ?></td></tr>
 <tr><td colspan='2' style='border:none;'><?php echo e($details['message']); ?></td></tr>
  </tbody></table>
</body></html>