<table>
    <!--
Advertiser_character
,Advertiser_name
,Advertiser_mobile_number
,The_main_type_of_ad
,Ad_subtype,
Ad_description
,Advertisement_publication_date
,Ad_update_date,
Ad_expiration,
Ad_status,
Ad_Views,
District_Name
,City_Name,
Neighbourhood_Name,
Street_Name,
Longitude,
Lattitude,
Furnished,
Kitchen,
Adaptation,
facilities,
Using_For,
Property_Type,
The_Space,
Land_Number,
Plan_Number,
Number_Of_Units,
Floor_Number,
Unit_Number,
Rooms_Number,
Rooms_Type,
Real_Estate_Facade,
Street_Width
,Construction_Date,
Rental_Price,
Selling_Price,
Selling_Meter_Price,
Property limits and lenghts,
Is there a mortgage or restriction that prevents or limits the use of the property,
Rights and obligations over real estate that are not documented in the real estate document,
"Information that may affect the property,
 whether in reducing its value or affecting the target's decision to advertise",
 Property disputes,Availability of elevators,"Number of elevators, if available",
 Availability of Parking,
 "Number of parking, if available",
 Advertiser category,
 Advertiser license number,
 Advertiser's email
    -->
    <thead>
    <tr>


        <th>Ad_Id</th>
        <th>Advertiser_character</th>
        <th>Advertiser_name</th>
        <th>Advertiser_mobile_number</th>
        <th>The_main_type_of_ad</th>
        <th>Ad_description</th>
        <th>Ad_subtype</th>
        <th>Advertisement_publication_date</th>
        <th>Ad_update_date</th>
        <th>Ad_expiration</th>
        <th>Ad_status</th>
        <th>Ad_Views</th>
        <th>District_Name</th>
        <th>City_Name</th>
        <th>Neighbourhood_Name</th>
        <th>Street_Name</th>
        <th>Longitude</th>
        <th>Lattitude</th>
        <th>Furnished</th>
        <th>Kitchen</th>
        <th>Air_Condition</th>
        <th>facilities</th>
        <th>Using_For</th>
        <th>Property_Type</th>
        <th>The_Space</th>
        <th>Land_Number</th>
        <th>Plan_Number</th>
        <th>Number_Of_Units</th>
        <th>Floor_Number</th>
        <th>Unit_Number</th>
        <th>Rooms_Number</th>
        <th>Rooms_Type</th>
        <th>Real_Estate_Facade</th>
        <th>Street_Width</th>
        <th>Construction_Date</th>
        <th>Rental_Price</th>
        <th>Selling_Price</th>
        <th>Selling_Meter_Price</th>
        <th>Property limits and lenghts</th>
        <th>Is there a mortgage or restriction that prevents or limits the use of the property</th>
        <th>Rights and obligations over real estate that are not documented in the real estate document</th>
        <th>Information that may affect the property, whether in reducing its value or affecting the target's decision
            to advertise
        </th>
        <th>Property disputes</th>
        <th>Availability of elevators</th>
        <th>Number of elevators</th>
        <th>Availability of Parking</th>
        <th> Number of parking</th>
        <th>Advertiser category</th>
        <th>Advertiser license number</th>
        <th>Advertiser's email</th>
        <th>Advertiser registration number</th>
        <th>Authorization number</th>
        <th>adLicenseNumber</th>
        <th>brokerageAndMarketingLicenseNumber</th>
        <th>channels</th>
        <th>creationDate</th>
        <th>endDate</th>
        <th>qrCodeUrl</th>


    </tr>
    </thead>
    <tbody>
    @foreach($estate as $estateItem)
        <tr>
            <td>{{ $estateItem->id }}</td>
            <td>{{ $estateItem->advertiser_character!=null?$estateItem->advertiser_character_name:'null' }}</td>
            <td>{{ $estateItem->owner_name!=null ? $estateItem->owner_name : 'null' }}</td>
            <td>{{ $estateItem->owner_mobile !=null ?   $estateItem->owner_mobile : '0' }}</td>
            <td>عرض</td>
            <td>{{ @$estateItem->note != null ? @$estateItem->note : 'null' }}</td>
            <td>{{ @$estateItem->operation_type->name_ar != null ? @$estateItem->operation_type->name_ar : 'null'   }}</td>
            <td>{{ $estateItem->created_at != null ? @$estateItem->created_at : '0000-00-00 00:00:00' }}</td>
            <td>{{ $estateItem->updated_at != null ? @$estateItem->updated_at : '0000-00-00 00:00:00' }}</td>
            <td>{{ $estateItem->deleted_at != null ?  @date('d-M-y', strtotime($estateItem->deleted_at))  : '0000-00-00' }}</td>
            <td>{{ $estateItem->status != null ? @$estateItem->status : 'null' }}</td>
            <td>{{ $estateItem->seen_count != null ? @$estateItem->seen_count : '0' }}</td>
            <td>{{ @$estateItem->state_name != null ? @$estateItem->state_name : 'null'}}</td>
            <td>{{ @$estateItem->city_name != null ? @$estateItem->city_name : 'null' }}</td>
            <td>{{ @$estateItem->neighborhood_name != null ? @$estateItem->neighborhood_name : 'null' }}</td>
            <td>{{ @$estateItem->street_name != null ? @$estateItem->street_name : 'null' }}</td>
            <td>{{ $estateItem->lat != null ? @$estateItem->lat : '0' }}</td>
            <td>{{ $estateItem->lan != null ? @$estateItem->lan : '0' }}</td>
            <td>{{ $estateItem->finishing_type_name != null ? @$estateItem->finishing_type_name : 'null' }}</td>
            <td>{{ $estateItem->kitchen_number != null ? @$estateItem->kitchen_number : '0' }}</td>
            <td>null</td>
            <td>{{ $estateItem->comfort_names != null ? @$estateItem->comfort_names : 'null' }}</td>
            <td>null</td>
            <td>{{ $estateItem->estate_type_name != null ? @$estateItem->estate_type_name : '0' }}</td>
            <td>{{ $estateItem->total_area != null ? @$estateItem->total_area : '0' }}</td>
            <td>{{ $estateItem->pace_number != null ? @$estateItem->pace_number : '0' }}</td>
            <td>{{ $estateItem->planned_number != null ? @$estateItem->planned_number : '0' }}</td>
            <td>{{ $estateItem->unit_counter != null ? @$estateItem->unit_counter : '0' }}</td>
            <td>{{ $estateItem->floor_number != null ? @$estateItem->floor_number : '0' }}</td>
            <td>{{ $estateItem->unit_number != null ? @$estateItem->unit_number : '0' }}</td>
            <td>0</td>
            <td>null</td>
            <td>{{ $estateItem->interface != null ? @$estateItem->interface : 'null' }}</td>
            <td>0</td>
            <td>{{ $estateItem->estate_age != null ? @date('Y-m-d', strtotime($estateItem->estate_age . ' years ago')) : '0000-00-00' }}</td>
            <td>{{ @$estateItem->rent_price != null ? @$estateItem->rent_price : '0' }}</td>
            <td>{{ @$estateItem->total_price != null ? @$estateItem->total_price : '0' }}</td>
            <td>{{ $estateItem->meter_price != null ? @$estateItem->meter_price : '0' }}</td>
            <td>{{ $estateItem->estate_dimensions != null ? @$estateItem->estate_dimensions : 'null' }}</td>
            <td>{{ $estateItem->is_obligations=='1'?'نعم':'لا' }}</td>
            <td>{{ $estateItem->obligations_information != null ? @$estateItem->obligations_information : 'null' }}</td>
            <td>{{ $estateItem->touching_information != null ? @$estateItem->touching_information : 'null' }}</td>
            <td>null</td>
            <td>{{ $estateItem->elevators_number!=null?'نعم':'لا' }}</td>
            <td>{{ @$estateItem->elevators_number? $estateItem->elevators_number :'0' }}</td>
            <td>{{ $estateItem->parking_spaces_numbers!=null?'نعم':'لا' }}</td>
            <td>{{ $estateItem->parking_spaces_numbers != null ? @$estateItem->parking_spaces_numbers : '0' }}</td>
            <td>{{ $estateItem->advertiser_side_name != null ? @$estateItem->advertiser_side_name : 'null' }}</td>
            <td>{{ @$estateItem->user->license_number != null? $estateItem->user->license_number:'0' }}</td>
            <td>{{ @$estateItem->user->email != null ?$estateItem->user->email:'null' }}</td>
            <td>0</td>
            <td>0</td>
            <td>{{$estateItem->advertising_license_number}}</td>
            <td>{{$estateItem->brokerage_and_marketing_license_number}}</td>
            <td>{{$estateItem->channels}}</td>
            <td>{{$estateItem->creation_date}}</td>
            <td>{{$estateItem->end_date}}</td>
            <td>https://chart.googleapis.com/chart?chs=500x500&cht=qr&chl={{$estateItem->link}}</td>


        </tr>
    @endforeach
    </tbody>
</table>
