<table>
    <thead>
    <tr>
        <th>رقم المزود</th>
        <th>اسم المزود</th>
        <th>رقم الجوال</th>
        <th>البريد الالكتروني</th>
        <th>رخصة فال</th>
        <th>تاريخ انتهاء رخصة فال</th>
        <th>تاريخ التسجيل</th>
        <th>عدد العقارات</th>
    </tr>
    </thead>
    <tbody>
    @foreach($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->onwer_name }}</td>
            <td>{{ $user->mobile }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->fal_license_number }}</td>
            <td>{{ $user->fal_license_expiry }}</td>
            <td>{{ $user->created_at }}</td>
            <td>{{ \App\Models\v3\Estate::where('user_id' , $user->id)->count() }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
