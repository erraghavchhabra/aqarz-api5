<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
    <!------ Include the above in your HEAD tag ---------->

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="http://getbootstrap.com/dist/js/bootstrap.min.js"></script>
<style>
    table.table-fit {
        width: auto !important;
        table-layout: auto !important;
    }
    table.table-fit thead th, table.table-fit tfoot th {
        width: auto !important;
    }
    table.table-fit tbody td, table.table-fit tfoot td {
        width: auto !important;
    }
</style>
</head>
<body>




<div class="container">
    <div class="row">


        <div class="col-md-12">
            <h4>إحصائيات نظام عقار للمستخدمين</h4>
            <div class="table-responsive">




                <table id="mytable" class="table table-fit table-bordred table-striped">
                    <thead>
                    <tr>
                        <th style="width:1px; white-space:nowrap;"  class="th-sm">رقم الموظف

                        </th>
                        <th style="width:1px; white-space:nowrap;"  class="th-sm">اسم المكتب

                        </th>
                        <th style="width:1px; white-space:nowrap;" class="th-sm">اسم المالك

                        </th>
                        <th style="width:1px; white-space:nowrap;" class="th-sm">نوع الحساب

                        </th>

                        <th style="width:1px; white-space:nowrap;"  class="th-sm">عدد العروض السوق الحقيقي

                        </th>
                        <th style="width:1px; white-space:nowrap;"  class="th-sm">عددالعروض السوق

                        </th>

                        <th style="width:1px; white-space:nowrap;" class="th-sm">عدد العقارات الحقيقي

                        </th>
                        <th style="width:1px; white-space:nowrap;" class="th-sm">عدد العقارات

                        </th>


                        <th style="width:1px; white-space:nowrap;" class="th-sm">عدد العروض قيد الانتظار صندوق الحقيقي

                        </th>
                        <th style="width:1px; white-space:nowrap;" class="th-sm">عدد العروض قيد الانتظار

                        </th>

                        <th style="width:1px; white-space:nowrap;" class="th-sm">عدد طلبات السوق الحقيقي

                        </th>
                        <th style="width:1px; white-space:nowrap;"  class="th-sm">عدد طلبات السوق

                        </th>

                        <th style="width:1px; white-space:nowrap;" class="th-sm">عدد كافة عروضي الصندوق الحقيقي

                        </th>
                        <th style="width:1px; white-space:nowrap;" class="th-sm">عدد كافة عروضي الصندوق

                        </th>


                        <th style="width:1px; white-space:nowrap;" class="th-sm">عدد عروضي السوق مقبولة الحقيقي

                        </th>
                        <th style="width:1px; white-space:nowrap;" class="th-sm">عدد عروضي السوق مقبولة

                        </th>


                        <th style="width:1px; white-space:nowrap;" class="th-sm">عدد عروضي الصندوق قيد المعاينة الحقيقي

                        </th>
                        <th style="width:1px; white-space:nowrap;" class="th-sm">عدد عروضي الصندوق قيد المعاينة

                        </th>

                        <th style="width:1px; white-space:nowrap;" class="th-sm">عدد طلبات الصندوق لديها عروضي الحقيقي

                        </th>
                        <th style="width:1px; white-space:nowrap;" class="th-sm">عدد طلبات الصندوق لديها عروضي

                        </th>

                        <th style="width:1px; white-space:nowrap;" class="th-sm">عدد عروضي الصندوق المقبولة الحقيقي

                        </th>
                        <th style="width:1px; white-space:nowrap;" class="th-sm">عددعروضي الصندوق المقبولة

                        </th>

                        <th style="width:1px; white-space:nowrap;" class="th-sm">عدد الموظفين الحقيقي

                        </th>
                        <th style="width:1px; white-space:nowrap;" class="th-sm">عددالموظفين

                        </th>


                    </tr>
                    </thead>
                    <tbody>

                    @foreach($users as $userItem)
                        <tr>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->id}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->name}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->onwer_name}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->type}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->real_count_offer}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->count_offer}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->real_count_estate}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->count_estate}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->real_count_fund_pending_offer}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->count_fund_pending_offer}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->real_count_request}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->count_request}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->real_count_fund_offer}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->count_fund_offer}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->real_count_accept_offer}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->count_accept_offer}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->real_count_preview_fund_offer}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->count_preview_fund_offer}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->real_count_fund_request}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->count_fund_request}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->real_count_accept_fund_offer}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->count_accept_fund_offer}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->real_count_emp}}</td>
                            <td style="width:1px; white-space:nowrap;">{{@$userItem->count_emp}}</td>


                        </tr>
                    @endforeach
                    </tbody>

                </table>


                <div class="clearfix"></div>



                {{ $users->links('vendor.pagination.default2') }}

            </div>

        </div>
    </div>
</div>




<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
<script>
    $(document).ready(function () {
        $('#mytable').DataTable();

    });
</script>
</body>
