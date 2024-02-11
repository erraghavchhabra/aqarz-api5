<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<!------ Include the above in your HEAD tag ---------->

<link href="http://www.a1b5.net/metro/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css" />
<link href="http://www.a1b5.net/metro/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

<script src="http://www.a1b5.net/metro/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
<script src="http://www.a1b5.net/metro/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>

<div class="form-horizontal">
    <div class="form-body">
        <!-- Begin cloned dynamic list section -->

        <form action="{{route('exportFund')}}" method="get">
            <div id="date1" class="clonedInput_4">
                <div class="form-group">
                    <label class="control-label col-md-3 label_date">التاريخ</label>
                    <div class="col-md-3 fields">
                        <div id="name_data">
                            <div class="input-group">
                                <input type="date" class="form-control form-control-inline input-medium date-picker" id="datepicker" name="date">
                            </div>
                        </div>
                        <span class="help-block">اختر تاريخ الطلبات</span>
                    </div>

                </div>
            </div><!-- end #date1 -->
            <div class="form-group">
                <label class="control-label col-md-3"></label>
                <div class="col-md-4">
                    <button type="submit" id="btnAdd_4"  class="btn btn-primary">تصدير</button>


                </div>
            </div>
        </form>

    </div>
</div>