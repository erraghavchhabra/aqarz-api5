<table>
    <thead>
    <tr>
        <th>رقم الطلب</th>
        <th>نوع العقار</th>
        <th>اتجاه العقار</th>
        <th>رقم المستفيد</th>
        <th>المدينة</th>
        <th>الحي</th>
        <th>تاريخ الطلب</th>
        <th>السعر المطلوب</th>
        <th>المساحة المطلوبة</th>
        <th>العروض</th>
        <th>حالة العقار</th>

    </tr>
    </thead>
    <tbody>
    @foreach($requests as $requestItem)
        <tr>
            <td>{{ $requestItem->id }}</td>
            <td>{{ $requestItem->estate_type_name_web }}</td>
            <td>{{ $requestItem->dir_estate }}</td>
            <td>{{ $requestItem->beneficiary_mobile }}</td>
            <td>{{ $requestItem->city_name_web }}</td>
            <td>{{ $requestItem->neighborhood_name }}</td>
            <td>{{$requestItem->created_at->format('d/m/Y - h:i A')}}</td>
            <td>{{ $requestItem->estate_price_range }}</td>
            <td>{{ $requestItem->street_view_range }}</td>
            <td>{{ $requestItem->offers()->count() }}</td>
            <td>{{ $requestItem->estate_status }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
