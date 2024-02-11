<link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<!------ Include the above in your HEAD tag ---------->
<style>
    body {
        height: 400px;
        position: fixed;
        bottom: 0;
    }

    .col-md-2, .col-md-10 {
        padding: 0;
    }

    .panel {
        margin-bottom: 0px;
    }

    .chat-window {
        bottom: 0;
        position: fixed;
        float: right;
        margin-left: 10px;
    }

    .chat-window > div > .panel {
        border-radius: 5px 5px 0 0;
    }

    .icon_minim {
        padding: 2px 10px;
    }

    .msg_container_base {
        background: #e5e5e5;
        margin: 0;
        padding: 0 10px 10px;
        max-height: 300px;
        overflow-x: hidden;
    }

    .top-bar {
        background: #666;
        color: white;
        padding: 10px;
        position: relative;
        overflow: hidden;
    }

    .msg_receive {
        padding-left: 0;
        margin-left: 0;
    }

    .msg_sent {
        padding-bottom: 20px !important;
        margin-right: 0;
    }

    .messages {
        background: white;
        padding: 10px;
        border-radius: 2px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        max-width: 100%;
    }

    .messages > p {
        font-size: 13px;
        margin: 0 0 0.2rem 0;
    }

    .messages > time {
        font-size: 11px;
        color: #ccc;
    }

    .msg_container {
        padding: 10px;
        overflow: hidden;
        display: flex;
    }

    img {
        display: block;
        width: 100%;
    }

    .avatar {
        position: relative;
    }

    .base_receive > .avatar:after {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        width: 0;
        height: 0;
        border: 5px solid #FFF;
        border-left-color: rgba(0, 0, 0, 0);
        border-bottom-color: rgba(0, 0, 0, 0);
    }

    .base_sent {
        justify-content: flex-end;
        align-items: flex-end;
    }

    .base_sent > .avatar:after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 0;
        border: 5px solid white;
        border-right-color: transparent;
        border-top-color: transparent;
        box-shadow: 1px 1px 2px rgba(black, 0.2);
    / / not quite perfect but close
    }

    .msg_sent > time {
        float: right;
    }


    .msg_container_base::-webkit-scrollbar-track {
        -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
        background-color: #F5F5F5;
    }

    .msg_container_base::-webkit-scrollbar {
        width: 12px;
        background-color: #F5F5F5;
    }

    .msg_container_base::-webkit-scrollbar-thumb {
        -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, .3);
        background-color: #555;
    }

    .btn-group.dropup {
        position: fixed;
        left: 0px;
        bottom: 0;
    }
</style>
<div class="container">
    <div class="row chat-window col-xs-5 col-md-3" id="chat_window_1" style="margin-left:10px;">
        <div class="col-xs-12 col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading top-bar">
                    <div class="col-md-8 col-xs-8">
                        <h3 class="panel-title"><span class="glyphicon glyphicon-comment"></span> طلب خدمة</h3>
                    </div>
                    <div class="col-md-4 col-xs-4" style="text-align: right;">
                        <a href="#"><span id="minim_chat_window"
                                          class="glyphicon glyphicon-minus icon_minim"></span></a>
                        <a href="#"><span class="glyphicon glyphicon-remove icon_close" data-id="chat_window_1"></span></a>
                    </div>
                </div>
                <div id="testMsg" class="panel-body msg_container_base">
                    <div style="display: none" class="row msg_container base_receive">
                        <div class="col-md-2 col-xs-2 avatar">
                            <img
                                src="http://www.bitrebels.com/wp-content/uploads/2011/02/Original-Facebook-Geek-Profile-Avatar-1.jpg"
                                class=" img-responsive ">
                        </div>
                        <div class="col-xs-10 col-md-10">
                            <div class="messages msg_receive">
                                <p>that mongodb thing looks good, huh?
                                    tiny master db, and huge document store</p>
                                <time datetime="2009-11-13T20:00">Timothy • 51 min</time>
                            </div>
                        </div>
                    </div>
                    <div style="display: none" class="row msg_container base_sent">
                        <div class="col-md-10 col-xs-10 ">
                            <div class="messages msg_sent">
                                <p>that mongodb thing looks good, huh?
                                    tiny master db, and huge document store</p>
                                <time datetime="2009-11-13T20:00">Timothy • 51 min</time>
                            </div>
                        </div>
                        <div class="col-md-2 col-xs-2 avatar">
                            <img
                                src="http://www.bitrebels.com/wp-content/uploads/2011/02/Original-Facebook-Geek-Profile-Avatar-1.jpg"
                                class=" img-responsive ">
                        </div>
                    </div>

                </div>
                <div class="panel-footer">
                    <div class="input-group">
                        <input id="btn-input" type="text" class="form-control input-sm chat_input"
                               placeholder="Write your message here..."/>
                        <span class="input-group-btn">
                        <button class="btn btn-primary btn-sm" id="create">Send</button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <p type="text" id="token-request">
    </p>

    <div class="btn-group dropup">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            <span class="glyphicon glyphicon-cog"></span>
            <span class="sr-only">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu" role="menu">
            <li><a href="#" id="new_chat"><span class="glyphicon glyphicon-plus"></span> Novo</a></li>
            <li><a href="#"><span class="glyphicon glyphicon-list"></span> Ver outras</a></li>
            <li><a href="#"><span class="glyphicon glyphicon-remove"></span> Fechar Tudo</a></li>
            <li class="divider"></li>
            <li><a href="#"><span class="glyphicon glyphicon-eye-close"></span> Invisivel</a></li>
        </ul>
    </div>
</div>

<script>
    $(document).on('click', '.panel-heading span.icon_minim', function (e) {
        var $this = $(this);
        if (!$this.hasClass('panel-collapsed')) {
            $this.parents('.panel').find('.panel-body').slideUp();
            $this.addClass('panel-collapsed');
            $this.removeClass('glyphicon-minus').addClass('glyphicon-plus');
        } else {
            $this.parents('.panel').find('.panel-body').slideDown();
            $this.removeClass('panel-collapsed');
            $this.removeClass('glyphicon-plus').addClass('glyphicon-minus');
        }
    });
    $(document).on('focus', '.panel-footer input.chat_input', function (e) {
        var $this = $(this);
        if ($('#minim_chat_window').hasClass('panel-collapsed')) {
            $this.parents('.panel').find('.panel-body').slideDown();
            $('#minim_chat_window').removeClass('panel-collapsed');
            $('#minim_chat_window').removeClass('glyphicon-plus').addClass('glyphicon-minus');
        }
    });
    $(document).on('click', '#new_chat', function (e) {
        var size = $(".chat-window:last-child").css("margin-left");
        size_total = parseInt(size) + 400;

        var clone = $("#chat_window_1").clone().appendTo(".container");
        clone.css("margin-left", size_total);
    });
    $(document).on('click', '.icon_close', function (e) {
        //$(this).parent().parent().parent().parent().remove();
        $("#chat_window_1").remove();
    });

</script>

<script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
<script src="https://cdn.ably.io/lib/ably.min-1.js"></script>
<script>
    var clientId = 'abd';

    function authCallback(tokenParams, callback) {
        var rest = new Ably.Rest({key: 'xuuigQ.ignCrQ:BXwoYA62wDSqBYvr'});
        rest.auth.createTokenRequest({clientId: clientId}, function (err, tokenRequest) {
            //    alert('Token request created for clientId: ' + tokenRequest.clientId, 'orange');
            callback(null, tokenRequest);
        });
    }

    var realtime = new Ably.Realtime({authCallback: authCallback}),
        channel = realtime.channels.get('chatbot');


    realtime.connection.on('connected', function () {
        //  alert('✓ Connected to Ably with clientId: ' + realtime.auth.tokenDetails.clientId, 'green');

        channel.history(function (err, resultPage) {

            //   console.log('Last published message:' + resultPage.items[1].data);

            if (resultPage.items) {

            }
            /*channel.publish('event', resultPage.items[1].data
                , function(err) {
                    if (err) {
                        //     alert('✗ Publish valid failed: ' + err.message, 'red');
                    }


                });*/
        });
        channel.publish('event', 'مرحبا بك ' + realtime.auth.clientId + ' كيف يمكنني مساعدتك' +
            '<label for="cars">اختر الخدمة المطلوبة:</label>' +
            ' <select onchange="selectP()"  name="cars" id="cars">' +
            ' <option value="volvo">إضافة عقار</option> ' +
            '<option value="saab">طلب عقار</option>' +
            ' <option value="mercedes">طلب تأجير تقسيط</option> ' +
            '<option value="audi">اخرى</option>' +
            ' </select><br><br>'
            , function (err) {
                if (err) {
                    //  alert('✗ Publish valid failed: ' + err.message, 'red');
                }


            });
    });

    channel.subscribe(function (message) {

        $('#testMsg').append(message.data);
        //  alert('⬅ Received message with clientId: ' + message.data, 'green');
    });


    function selectP() {
        var selectVal = jQuery("#cars option:selected").val();
        var selecttext = jQuery("#cars option:selected").text();

        channel.publish('event', 'لقد اخترت' + selecttext + '<br> <input onchange="YesNo()" type="radio" id="html" name="fav_language" value="yes">' +
            ' <label for="html">نعم</label><br>' +
            ' <input onchange="YesNo()" type="radio" id="css" name="fav_language" value="no"> ' +
            '<label for="css">لا</label><br>'
            , function (err) {
                if (err) {
                    //      alert('✗ Publish valid failed: ' + err.message, 'red');
                }


            });
    }

    function YesNo() {
        // var selectVal = jQuery("name:fav_language option:selected").val();

        //   var selectVal = $('input:radio[name="fav_language"] option:checked').val();
        var selectVal = $('input[name="fav_language"]:checked').val();


        if (selectVal == 'no') {
            channel.history(function (err, resultPage) {
                //   console.log('Last published message:' + resultPage.items[1].data);


                channel.publish('event', resultPage.items[1].data
                    , function (err) {
                        if (err) {
                            //     alert('✗ Publish valid failed: ' + err.message, 'red');
                        }


                    });
            });


        } else if (selectVal == 'yes') {
            var str2 = '';
            @foreach($city as $cityItem)
                str2 += ' <option value="1">{{$cityItem->name_ar}}</option> ';
            @endforeach

            var str3 = '';
            @foreach($neb as $nebItem)
                str3 += ' <option value="1">{{$nebItem->name_ar}}</option> ';
            @endforeach

            console.log(str2)

            $str = '<form>' +
                ' <div class="form-group"> ' +
                '<label for="cars">اختر نوع الطلب:</label>' +
                ' <select onchange="selectP()"  name="opration_type" id="cars">' +
                ' <option value="1">بيع</option> ' +
                '<option value="2">ايجار</option>' +
                ' <option value="3">استثمار</option> ' +
                ' </select>' +
                '</div>' +
                '<div class="form-group">' +
                '<label for="cars">اختر نوع العقار:</label>' +
                ' <select onchange="selectP()"  name="estate_type_id" id="cars">' +
                ' <option value="1">شقة</option> ' +
                '<option value="2">فيلا</option>' +
                ' <option value="3">أرض</option> ' +
                ' <option value="4">دوبلكس </option> ' +
                ' <option value="5">دور</option> ' +
                ' <option value="6">مكتب</option> ' +
                ' <option value="7">مزرعة</option> ' +
                ' <option value="8">مستودع</option> ' +
                ' <option value="9">شاليه</option> ' +
                ' </select>' +
                '</div>' +
                '<div class="form-group">' +
                '<label for="cars">اختر المدينة:</label>' +
                ' <select onchange="selectP()"  name="estate_type_id" id="cars">' +

                str2 +

                ' </select>' +
                '</div>' +
                '<div class="form-group">' +
                '<label for="cars">اختر الحي:</label>' +
                ' <select onchange="selectP()"  name="estate_type_id" id="cars">' +

                str3 +

                ' </select>' +
                '</div>' +
                '<button onclick="done()" type="button" class="btn btn-primary">حفظ</button></form>';
            channel.publish('event', $str
                , function (err) {
                    if (err) {
                        //     alert('✗ Publish valid failed: ' + err.message, 'red');
                    }


                });
        }

    }

    function done() {
      //  alert('تم حفظ الطلب بنجاح')

        $('#testMsg').text('تم اضافة الطلب بنجاح');
    }
</script>


<!------ Include the above in your HEAD tag ---------->
