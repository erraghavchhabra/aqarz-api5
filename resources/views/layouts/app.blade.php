<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Title Here</title>
    <link href="{{asset('site/assets/css/bootstrap.min.css')}}" rel="stylesheet">

    <link href="{{asset('site/assets/css/nice-select.css')}}" rel="stylesheet">
    <link href="{{asset('site/assets/css/owl.carousel.css')}}" rel="stylesheet">
    <link href="{{asset('site/assets/css/meanmenu.css')}}" rel="stylesheet">
    <link href="{{asset('site/assets/css/bootstrap-rtl.min.css')}}" rel="stylesheet">
    <link href="{{asset('site/assets/css/style.css')}}" rel="stylesheet">
    <link href="{{asset('site/assets/css/responsive.css')}}" rel="stylesheet">


    @stack('css')

</head>

<body>


<!-- Preloader Start -->
<div class="proloader">
    <div class="loader_34">
        <!-- Preloader Elements -->
        <div class="ytp-spinner">
            <div class="ytp-spinner-container">
                <div class="ytp-spinner-rotator">
                    <!-- Preloader Container Left Begin -->
                    <div class="ytp-spinner-left">
                        <!-- Preloader Body Left -->
                        <div class="ytp-spinner-circle"></div>
                    </div>
                    <!-- Preloader Container Left End -->

                    <!-- Preloader Container Right Begin -->
                    <div class="ytp-spinner-right">
                        <!-- Preloader Body Right -->
                        <div class="ytp-spinner-circle"></div>
                    </div>
                    <!-- Preloader Container Right End -->
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Preloader End -->



<!-------- Header Area Start ---------->

@if(Request::segment(1)!='plans' && Request::segment(1)!='subscribe' && Request::segment(1)!='try' && Request::segment(1)!='success' && Request::segment(1)!='error'  )
<header class="home-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-3 col-md-7 col-6">
                <div class="logo">
                    <a href=""><img src="{{asset('site/assets/img/logo.png')}}" alt=""> </a>
                </div>
            </div>
            <div class="col-lg-6 col-md-0 dis-none">
                <div class="header-menu">
                    <div class="menu-hide">
                        <img src="{{asset('site/assets/img/cancel.png')}}" alt="">
                    </div>
                    <ul>
                        <li><a href="{{route('home')}}">الرئيسية</a></li>
                        <li><a href="">من نحن</a></li>
                        <li style="display: none"><a href="#">اشتراكات</a></li>
                        <li><a href="">الأخبار العقارية</a></li>
                        <li><a class="active" href="">تواصل معنا</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-md-5 col-6">
                <div class="header-left">

                    @if(!auth()->check())
                    <a href="" data-toggle="modal" data-target="#modal-1">تسجيل دخول</a>
                   @else
                        <a data-confirm="{{__('Are you sure?')}}" data-csrf="{{csrf_token()}}" data-method="get" data-to="{{route('logout')}}" href="{{route('logout')}}" rel="nofollow">
                            {{__('تسجيل خروج')}}
                        </a>
                        @endif


                    <div class="menu-trigger">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

@else


    @if( Request::segment(1)!='subscribe' && Request::segment(1)!='try' && Request::segment(1)!='success' && Request::segment(1)!='error' )
    <header class="header-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-3 col-md-7 col-6">
                    <div class="logo">
                        <a href=""><img src="{{asset('site/assets/img/logo-2.png')}}" alt=""> <span> <img src="{{asset('site/assets/img/1.png')}}" alt=""> صندوق التنمية العقارية</span></a>
                    </div>
                </div>
                <div class="col-lg-6 col-md-0 dis-none">
                    <div class="header-menu">
                        <div class="menu-hide">
                            <img src="{{asset('site/assets/img/cancel.png')}}" alt="">
                        </div>
                        <ul>
                            <li><a href="{{route('home')}}">الرئيسية</a></li>
                            <li><a href="">من نحن</a></li>
                            <li style="display: none"><a href="#">اشتراكات</a></li>
                            <li><a href="">الأخبار العقارية</a></li>
                            <li><a class="active" href="">تواصل معنا</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-5 col-6">
                    <div class="header-left">
                        @if(!auth()->check())
                            <a href="" data-toggle="modal" data-target="#modal-1">تسجيل دخول</a>
                        @else
                            <a data-confirm="{{__('Are you sure?')}}" data-csrf="{{csrf_token()}}" data-method="get" data-to="{{route('logout')}}" href="{{route('logout')}}" rel="nofollow">
                                {{__('تسجيل خروج')}}
                            </a>
                        @endif
                        <div class="menu-trigger">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
@endif
    @endif



<!-------- Header Area End ---------->


@yield('content')





@if(Request::segment(1)!='plans' && Request::segment(1)!='subscribe' && Request::segment(1)!='try' && Request::segment(1)!='success' && Request::segment(1)!='error' )
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="social-link">
                        <ul>
                            <li><a href=""><img src="{{asset('site/assets/img/s1.png')}}" alt=""></a></li>
                            <li><a href=""><img src="{{asset('site/assets/img/s2.png')}}" alt=""></a></li>
                            <li><a href=""><img src="{{asset('site/assets/img/s3.png')}}" alt=""></a></li>
                            <li><a href=""><img src="{{asset('site/assets/img/s4.png')}}" alt=""></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="footer-link">
                        <div class="link-single">
                            <ul>
                                <li><a href="">تصميم هندسي</a></li>
                                <li><a href="">أراضي في السعودية </a></li>
                                <li><a href="">مستودع</a></li>
                            </ul>
                        </div>
                        <div class="link-single">
                            <ul>
                                <li><a href="">مزرعة للبيع </a></li>
                                <li><a href="">تأجير استراحة </a></li>
                                <li><a href="">استشارة هندسية</a></li>
                            </ul>
                        </div>
                        <div class="link-single">
                            <ul>
                                <li><a href="">فلل في الرياض</a></li>
                                <li><a href="">شقة سكنية</a></li>
                                <li><a href="">التقسيط</a></li>
                            </ul>
                        </div>
                        <div class="link-single">
                            <ul>
                                <li><a href="">بيت</a></li>
                                <li><a href="">مزرعة</a></li>
                                <li><a href="">مكتب تجاري</a></li>
                            </ul>
                        </div>
                        <div class="link-single">
                            <ul>
                                <li><a href="">أخبار عقار</a></li>
                                <li><a href="">تقسيط التأجير</a></li>
                                <li><a href="">الاشتراكات</a></li>
                            </ul>
                        </div>
                        <div class="link-single">
                            <ul>
                                <li><a href="">مساعدة</a></li>
                                <li><a href="">التواصل</a></li>
                                <li><a href="">المدونة</a></li>
                            </ul>
                        </div>
                        <div class="link-single">
                            <ul>
                                <li><a href="">الشروط والأحكام</a></li>
                                <li><a href="">سياسة الخصوصية</a></li>
                                <li><a href="">الدعم</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="footer-bottom">
                        <a href="#"><img src="{{asset('site/assets/img/f-logo.png')}}" alt=""></a>
                        <p> تطبيق عقارزجميع الحقوق محفوظة &copy; 2020</p>
                    </div>
                </div>
            </div>
        </div>
        <img src="{{asset('site/assets/img/footer-bg.png')}}" alt="" class="footer-shp">
    </footer>

@else


@endif













<!-- Modal -->
<div class="modal fade" id="modal-1" tabindex="-1" role="dialog" aria-labelledby="modal-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="submition-box">
                    <div class="home">
                        <img src="{{asset('site/assets/img/hoom.png')}}" alt="">
                    </div>
                    <div class="submition-form">
                        <h4>تسجيل الدخول</h4>
                        <p>تطبيق عقارز خيارك الأمثل، يمكنك من خلاله تصفح العقارات من حولك واختيارها والتواصل مع أصحابها.</p>
                        <form  method="POST" action="#">
                            {{ csrf_field() }}
                            <div class="int">
                                <input type="text" name="username" id="" placeholder="رقم الجوال">
                                <img src="{{asset('site/assets/img/phn.png')}}" alt="">
                            </div>
                            <div class="int">
                                <input type="password" name="password" id="" placeholder="كلمة المرور">
                                <img src="{{asset('site/assets/img/phn.png')}}" alt="">
                            </div>
                            <button type="submit">تسجيل الدخول</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>












<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="{{asset('site/assets/js/jquery.min.js')}}"></script>
<script src="{{asset('site/assets/js/Popper.js')}}"></script>
<script src="{{asset('site/assets/js/owl.carousel.min.js')}}"></script>
<script src="{{asset('site/assets/js/jquery.meanmenu.js')}}"></script>
<script src="{{asset('site/assets/js/jquery.nice-select.min.js')}}"></script>
<script src="{{asset('site/assets/js/bootstrap.min.js')}}"></script>
<script src="{{asset('site/assets/js/main.js')}}"></script>
<script src="{{asset('site/assets/js/main.js')}}"></script>
<script src="{{asset('site/assets/js/bootstrap-notify.js')}}"></script>
@stack('js')
<script>
  window.url_order = '#';
  window.token_app =  '{{csrf_token()}}' ;


  function valedtion (type,msg){



    if(type=='danger')
    {
      jQuery.notify({
          title: '<strong>'+msg+'</strong>',
          icon: 'glyphicon glyphicon-star',
          message: ""+msg+""
        },
        {
          type: ''+type+'',
          animate: {
            enter: 'animated fadeInUp',
            exit: 'animated fadeOutRight'
          },
          placement: {
            from: "top",
            align: "right"
          },
          offset: 40,
          spacing: 30,
          z_index: 10000000000000000,
          allow_dismiss: true,
          newest_on_top: false,
          showProgressbar: false,
        });
    }
    else
    {
      jQuery.notify({
          title: '<strong>'+msg+'</strong>',
          icon: 'glyphicon glyphicon-star',
          message: ""+msg+""
        },
        {
          type: ''+type+'',
          animate: {
            enter: 'animated fadeInUp',
            exit: 'animated fadeOutRight'
          },
          placement: {
            from: "top",
            align: "right"
          },
          offset: 40,
          spacing: 30,
          z_index: 10000000000000000,
          allow_dismiss: true,
          newest_on_top: false,
          showProgressbar: false,
        });
    }

  };
</script>


@if (count($errors) > 0)
    <ul>
        @foreach ($errors->all() as $error)



            <script>
              valedtion (type='danger','{{ $error }}');
            </script>
        @endforeach
    </ul>
@endif

@if (session('status'))

    <script>
      valedtion (type='success','{{session('status')}}');
    </script>

@endif

@if (session('error'))

    <script>
      valedtion (type='danger','{{session('error')}}');
    </script>

@endif







</body>

</html>
