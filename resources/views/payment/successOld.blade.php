@include('payment.layouts.header')

<section class="nav">
        <ul>
            <li class="lead" > طريقة الدفع</li>
            <li class="lead" > الدفع</li>
            <li class="active lead" > تم</li>
        </ul>
    </section>
    <section class="confirmation">
        <label class="success" for="" >تم الدفع بنجاح</label>
        <!-- <label class="failed" for="" >Failed</label> -->
        <small>لقد تم الدفع بنجاح يمكنك الان الدخول للتطبيق ومشاهدة كافة مزايا التطبيق</small>
    </section>

    <section class="order-confirmation">
        <label for="" class="lead">FORT ID : {{$fort_id}} </label>
    </section>

    <div class="h-seperator"></div>
    
    <section class="details">
        <h3>تفاصيل الطلب</h3>
        <br/>
        <table>
            <tr>
                <th>
                    اسم المستفيد
                </th>
                <th>
                   السعر
                </th>
            </tr>
            <tr>
                <td>{{$data['customer_name']}}</td>
                <td>{{$data['amount']}}</td>

            </tr>
        </table>
    </section>
    
    <div class="h-seperator"></div>
    

@include('payment.layouts.footer')
