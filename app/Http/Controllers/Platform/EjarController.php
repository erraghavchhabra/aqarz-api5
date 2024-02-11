<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Resources\v4\DataResource;
use App\Models\v3\Estate;
use App\Models\v3\EstateRequest;
use App\Models\v3\FundRequestOffer;
use App\Models\v3\Region;
use App\Models\v3\RentalContracts;
use App\Models\v3\RequestFund;
use App\Models\v4\EjarCities;
use App\Helpers\JsonResponse;
use App\Models\v4\EjarDistricts;
use App\Models\v4\EjarEstateData;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class EjarController extends Controller
{

    public function regions()
    {
        $regine = Region::select('ejar_id', 'name_ar', 'name_en')->orderBy('ejar_id', 'asc')->get();
        return response()->success("Region", DataResource::collection($regine));
    }


    public function cities($id)
    {
        $region = Region::where('ejar_id', $id)->first();
        if ($region) {
            $cities = EjarCities::where('region_id', $region->ejar_id)->orderBy('ejar_id', 'asc')->get();
            return response()->success("Cities", DataResource::collection($cities));
        } else {
            return response()->error("Region not found");
        }
    }

    public function districts($id)
    {
        $city = EjarCities::where('ejar_id', $id)->first();
        if ($city) {
            $districts = EjarDistricts::where('city_id', $city->ejar_id)->orderBy('ejar_id', 'asc')->get();
            return response()->success("Districts", DataResource::collection($districts));
        } else {
            return response()->error("City not found");
        }
    }


    public function add_properties(Request $request)
    {
        $rules = Validator::make($request->all(), [
            'estate_id' => 'required|exists:estates,id',
            'ejar_document_number' => 'required',
            'ejar_issue_place' => '',
            'ejar_issued_by' => '',
            'ejar_issued_date' => 'required',
            'ejar_ownership_document_type' => 'required',
            'ejar_legal_document_type_name' => 'required_if:ejar_ownership_document_type,==,electronic_title_deed',
            'ejar_scanned_documents_file' => 'required',
            'ejar_role' => 'required_if:ejar_ownership_document_type,!=,electronic_title_deed',
            'ejar_entity_type' => 'required_if:ejar_ownership_document_type,!=,electronic_title_deed',
            'ejar_id_number' => 'required_if:ejar_entity_type,==,individual_entities',
            'ejar_id_type' => 'required_if:ejar_entity_type,==,individual_entities',
            'ejar_date_of_birth_hijri' => 'required_if:ejar_entity_type,==,individual_entities',
            'ejar_registration_number' => 'required_if:ejar_entity_type,==,organization_entities',
            'ejar_registration_date' => 'required_if:ejar_entity_type,==,organization_entities',
            'ejar_entity_id' => '',
            'ejar_owner_id' => 'required_if:ejar_ownership_document_type,==,electronic_title_deed',
            'ejar_region_id' => 'required',
            'ejar_city_id' => 'required',
            'ejar_district_id' => '',
            'ejar_latitude' => '',
            'ejar_longitude' => '',
            'ejar_contract_type' => '',
            'ejar_total_floors' => 'required',
            'ejar_property_usage' => 'required',
            'ejar_property_type' => 'required',
            'ejar_established_date' => 'required',
            'ejar_units_per_floor' => 'required',
            'ejar_parking_spaces' => '',
            'ejar_security_entries' => '',
            'ejar_security_service' => '',
            'ejar_banquet_hall' => '',
            'ejar_elevators' => '',
            'ejar_gyms_fitness_centers' => '',
            'ejar_transfer_service' => '',
            'ejar_cafeteria' => '',
            'ejar_baby_nursery' => '',
            'ejar_games_room' => '',
            'ejar_football_yard' => '',
            'ejar_volleyball_court' => '',
            'ejar_tennis_court' => '',
            'ejar_basketball_court' => '',
            'ejar_swimming_pool' => '',
            'ejar_children_playground' => '',
            'ejar_grocery_store' => '',
            'ejar_laundry' => '',
            'ejar_compound_name' => '',
            'ejar_storeroom' => '',
            'ejar_central_ac' => '',
            'ejar_desert_cooler' => '',
            'ejar_split_unit' => '',
            'ejar_backyard' => '',
            'ejar_maid_room' => '',
            'ejar_is_kitchen_sink_installed' => '',
            'ejar_is_cabinet_installed' => '',
            'ejar_gas_meter' => '',
            'ejar_electricity_meter' => '',
            'ejar_water_meter' => '',
            'ejar_is_furnished' => '',
            'ejar_furnish_type' => '',
            'ejar_unit_usage' => '',
            'ejar_unit_finishing' => '',
            'ejar_length' => '',
            'ejar_width' => '',
            'ejar_height' => '',
            'ejar_include_mezzanine' => '',
            'ejar_rooms' => '',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

//        $user = $request->user();
//        $sub = $user->platform_plan->where('status', 'active')->first();
//        if (!$sub) {
//            return response()->error(__('views.user not subscribed to any plan'));
//        }


//        $check_exist_ejar = EjarEstateData::where('estate_id', $request->get('estate_id'))->first();
//        if ($check_exist_ejar) {
//            return JsonResponse::fail(['error' => 'estate exist'], 400);
//        }
        $estate = Estate::find($request->get('estate_id'));
//        $estate_name = $estate->estate_type_name . ' - ' . $estate->city_name . ' - ' . $estate->neighborhood_name . ' - ' . $estate->street_name . ' - #' . $estate->id;
        $estate_name = Carbon::now()->timestamp . ' - #' . $estate->id;
        $unit_number = $this->uniqe_code_unit_number();
        $request->merge([
            'estate_name' => $estate_name,
            'unit_number' => $unit_number,
        ]);

        $request->merge(['ejar_issued_date' => convert_number_to_english($request->get('ejar_issued_date'))]);
        $request->merge(['ejar_date_of_birth_hijri' => convert_number_to_english($request->get('ejar_date_of_birth_hijri'))]);

        $file = $request->file('ejar_scanned_documents_file');
        $file = curl_file_create($file->getRealPath(), $file->getMimeType(), $file->getClientOriginalName());

        $data = [
            'ejar_document_number' => $request->ejar_document_number ?? '',
            'ejar_issue_place' => $request->ejar_issue_place ?? '',
            'ejar_issued_by' => $request->ejar_issued_by ?? '',
            'ejar_issued_date' => $request->ejar_issued_date,
            'ejar_legal_document_type_name' => $request->ejar_legal_document_type_name ?? null,
            'ejar_ownership_document_type' => $request->ejar_ownership_document_type,
            'scanned_documents' => $file,
            'ejar_role' => $request->get('ejar_role') ?? null,
            'ejar_entity_type' => $request->get('ejar_entity_type') ?? null,
            'entity_id' => $this->ejar_entity_id($request) ?? $request->ejar_entity_id,
            'ejar_owner_id' => $request->get('ejar_owner_id') ?? null,
            'ejar_region_id' => $request->get('ejar_region_id') ?? null,
            'ejar_city_id' => $request->get('ejar_city_id') ?? null,
            'ejar_district_id' => $request->get('ejar_district_id') ?? null,
            'building_number' => $request->building_number,
            'postal_code' => $request->postal_code,
            'street_name' => $request->street_name,
            'additional_code' => $request->additional_code,
            'latitude' => $estate->lat,
            'longitude' => $estate->lan,
            'contract_type' => 'residential',
            'property_name' => $request->estate_name,
            'property_number' => $request->unit_number,
            'ejar_total_floors' => $request->get('ejar_total_floors') ?? null,
            'ejar_property_usage' => $request->get('ejar_property_usage') ?? null,
            'ejar_property_type' => $request->get('ejar_property_type') ?? null,
            'ejar_established_date' => $request->get('ejar_established_date') ?? null,
            'ejar_units_per_floor' => $request->get('ejar_units_per_floor') ?? null,
            'ejar_parking_spaces' => $estate->parking_spaces_numbers ?? 0,
            'ejar_security_entries' => $request->get('ejar_security_entries') ?? 0,
            'ejar_banquet_hall' => $request->get('ejar_banquet_hall') ?? 0,
            'ejar_elevators' => $estate->elevators_number ?? 0,
            'ejar_gyms_fitness_centers' => $request->get('ejar_gyms_fitness_centers') ?? 0,
            'ejar_transfer_service' => $request->get('ejar_transfer_service') ?? 0,
            'ejar_cafeteria' => $estate->kitchen_number ?? 0,
            'ejar_baby_nursery' => $request->get('ejar_baby_nursery') ?? 0,
            'ejar_games_room' => $request->get('ejar_games_room') ?? 0,
            'ejar_football_yard' => $request->get('ejar_football_yard') ?? 0,
            'ejar_volleyball_court' => $request->get('ejar_volleyball_court') ?? 0,
            'ejar_tennis_court' => $request->get('ejar_tennis_court') ?? 0,
            'ejar_basketball_court' => $request->get('ejar_basketball_court') ?? 0,
            'ejar_swimming_pool' => $request->get('ejar_swimming_pool') ?? 0,
            'ejar_children_playground' => $request->get('ejar_children_playground') ?? 0,
            'ejar_grocery_store' => $request->get('ejar_grocery_store') ?? 0,
            'ejar_laundry' => $request->get('ejar_laundry') ?? 0,
            'ejar_compound_name' => $estate->compound_name,
        ];


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => api_url . 'ejar.php?type=add_properties',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);
        curl_close($curl);


        $response = json_decode($response);
        //get id from data
        $id = @$response->data->id;
        if ($id) {
            $ejar_info = EjarEstateData::create([
                'estate_id' => $request->estate_id,
                'issued_date' => $request->ejar_issued_date,
                'issue_place' => $request->ejar_issue_place,
                'issued_by' => $request->ejar_issued_by,
                'document_number' => $request->ejar_document_number,
                'legal_document_type_name' => $request->ejar_legal_document_type_name,
                'ownership_document_type' => $request->ejar_ownership_document_type,
                'scanned_documents' => $request->scanned_documents,
                'role' => $request->ejar_role,
                'entity_type' => $request->ejar_entity_type,
                'entity_id' => $this->ejar_entity_id($request) ?? $request->ejar_entity_id,
                'owner_id' => $request->ejar_owner_id,
                'region_id' => $request->ejar_region_id,
                'city_id' => $request->ejar_city_id,
                'district_id' => $request->ejar_district_id,
                'contract_type' => 'residential',
                'total_floors' => $request->ejar_total_floors,
                'property_usage' => $request->ejar_property_usage,
                'established_date' => $request->ejar_established_date,
                'units_per_floor' => $request->ejar_units_per_floor,
                'ejar_property_type' => $request->ejar_property_type,
                'parking_spaces' => $request->ejar_parking_spaces,
                'security_entries' => $request->ejar_security_entries,
                'security_service' => $request->ejar_security_service,
                'banquet_hall' => $request->ejar_banquet_hall,
                'elevators' => $request->ejar_elevators,
                'gyms_fitness_centers' => $request->ejar_gyms_fitness_centers,
                'transfer_service' => $request->ejar_transfer_service,
                'cafeteria' => $request->ejar_transfer_cafeteria,
                'baby_nursery' => $request->ejar_baby_nursery,
                'games_room' => $request->ejar_games_room,
                'football_yard' => $request->ejar_football_yard,
                'volleyball_court' => $request->ejar_volleyball_court,
                'tennis_court' => $request->ejar_tennis_court,
                'basketball_court' => $request->ejar_basketball_court,
                'swimming_pool' => $request->ejar_swimming_pool,
                'children_playground' => $request->ejar_children_playground,
                'grocery_store' => $request->ejar_grocery_store,
                'laundry' => $request->ejar_laundry,
                'building_number' => $request->building_number,
                'postal_code' => $request->postal_code,
                'street_name' => $request->street_name,
                'additional_code' => $request->additional_code,
                'estate_name' => $request->estate_name,
                'unit_number' => $request->unit_number,
                'properties_id' => @$response->data->id,
            ]);

            RentalContracts::create([
                'user_id' => $estate->user_id,
                'estate_id' => $estate->id,
                'estate_group_id' => $estate->group_estate_id,
                'ejar_id_info' => $ejar_info->id,
                'contract_number' => $this->uniqe_code(),
                'date_of_writing_the_contract' => Carbon::now()->format('Y-m-d'),
                'status' => 'pending',
            ]);
        }
        return $response;

    }

    public function uniqe_code_unit_number()
    {
        $code = rand(100000, 999999);
        $RentalContracts = EjarEstateData::where('unit_number', $code)->first();
        if ($RentalContracts) {
            $this->uniqe_code();
        } else {
            return $code;
        }
    }


    public function add_unit(Request $request)
    {

        $rules = Validator::make($request->all(), [
            'estate_id' => 'required|exists:estates,id',
            'ejar_storeroom' => '',
            'ejar_central_ac' => '',
            'ejar_desert_cooler' => '',
            'ejar_split_unit' => '',
            'ejar_backyard' => '',
            'ejar_maid_room' => '',
            'ejar_is_kitchen_sink_installed' => '',
            'ejar_is_cabinet_installed' => '',
            'ejar_is_furnished' => 'required',
            'ejar_established_date' => 'required',
            'ejar_unit_finishing' => 'required',
            'ejar_length' => 'required',
            'ejar_width' => 'required',
            'ejar_height' => 'required',
            'ejar_include_mezzanine' => 'required',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $check_exist_ejar = EjarEstateData::where('estate_id', $request->get('estate_id'))->first();
        if (!$check_exist_ejar) {
            return JsonResponse::fail(['error' => 'estate not exist'], 400);
        }

        $estate = Estate::find($request->get('estate_id'));

        $direction = $estate->interface ? explode(',', $estate->interface)[0] : 'north';
        $bedrooms = $estate->bedroom_number ?? 0;
        $bathrooms_full = $estate->bathroom_number ?? 0;
        $halls = $estate->lounges_number ?? 0;
        $storeroom = $request->ejar_storeroom ?? 0;
        $central_ac = $request->ejar_central_ac ?? 0;
        $kitchen = $estate->kitchen_number ?? 0;
        $majles = $estate->boards_number ?? 0;
        $desert_cooler = $request->ejar_desert_cooler ?? 0;
        $split_unit = $request->ejar_split_unit ?? 0;
        $backyard = $request->ejar_backyard ?? 0;
        $maid_room = $request->ejar_maid_room ?? 0;
        $is_kitchen_sink_installed = $request->ejar_is_kitchen_sink_installed ?? false;
        $is_cabinet_installed = $request->ejar_is_cabinet_installed ?? false;
        $gas_meter = $request->ejar_gas_meter ?? 0;
        $electricity_meter = $request->ejar_electricity_meter ?? 0;
        $water_meter = $request->ejar_water_meter ?? 0;
        $unit_number = $check_exist_ejar->unit_number ?? 0;
        $floor_number = $estate->floor_number ?? 0;
        $is_furnished = $request->ejar_is_furnished ?? false;
        $furnish_type = $request->ejar_furnish_type;
        $unit_type = $check_exist_ejar->ejar_property_type;
        $unit_usage = $check_exist_ejar->property_usage;
        $area = $estate->total_area ?? 0;
        $established_date = $request->ejar_established_date;
        $unit_finishing = $request->ejar_unit_finishing;
        $length = $request->ejar_length;
        $width = $request->ejar_width;
        $height = $request->ejar_height;
        $include_mezzanine = $request->ejar_include_mezzanine ?? false;
        $rooms = $request->ejar_rooms ?? 0;


        $data = [
            'properties_id'               => $check_exist_ejar->properties_id,
            'bedrooms'                    => $bedrooms,
            'rooms'                       => $rooms,
            'include_mezzanine'           => $include_mezzanine,
            'height'                      => $height,
            'width'                       => $width,
            'length'                      => $length,
            'unit_finishing'              => $unit_finishing,
            'unit_direction'              => $direction,
            'established_date'            => $established_date,
            'area'                        => $area,
            'unit_usage'                  => $unit_usage,
            'unit_type'                   => $unit_type,
            'furnish_type'                => $furnish_type,
            'is_furnished'                => $is_furnished,
            'floor_number'                => $floor_number,
            'unit_number'                 => $unit_number,
            'water_meter'                 => $water_meter,
            'electricity_meter'           => $electricity_meter,
            'gas_meter'                   => $gas_meter,
            'is_cabinet_installed'        => $is_cabinet_installed,
            'is_kitchen_sink_installed'   => $is_kitchen_sink_installed,
            'maid_room'                   => $maid_room,
            'backyard'                    => $backyard,
            'split_unit'                  => $split_unit,
            'desert_cooler'               => $desert_cooler,
            'majles'                      => $majles,
            'kitchen'                     => $kitchen,
            'central_ac'                  => $central_ac,
            'storeroom'                   => $storeroom,
            'halls'                       => $halls,
            'bathrooms_full'              => $bathrooms_full,
        ];


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => api_url . 'ejar.php?type=add_unit',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);
        curl_close($curl);



        $response = json_decode($response);

        $check_exist_ejar = EjarEstateData::where('estate_id', $request->get('estate_id'))->first();
        if (!$check_exist_ejar) {
            return 'no data';
        }

        $check_exist_ejar->update([
            'unit_storeroom' => $request->ejar_storeroom,
            'unit_central_ac' => $request->ejar_central_ac,
            'unit_desert_cooler' => $request->ejar_desert_cooler,
            'unit_split_unit' => $request->ejar_split_unit,
            'unit_backyard' => $request->ejar_backyard,
            'unit_maid_room' => $request->ejar_maid_room,
            'unit_is_kitchen_sink_installed' => $request->ejar_is_kitchen_sink_installed,
            'is_cabinet_installed' => $request->ejar_is_cabinet_installed,
            'gas_meter' => $request->ejar_gas_meter,
            'electricity_meter' => $request->ejar_electricity_meter,
            'water_meter' => $request->ejar_water_meter,
            'is_furnished' => $request->ejar_is_furnished,
            'furnish_type' => $request->ejar_furnish_type,
            'unit_usage' => $check_exist_ejar->ejar_property_usage,
            'unit_finishing' => $request->ejar_unit_finishing,
            'length' => $request->ejar_length,
            'width' => $request->ejar_width,
            'height' => $request->ejar_height,
            'include_mezzanine' => $request->ejar_include_mezzanine,
            'rooms' => $request->ejar_rooms,
            'properties_id' => $check_exist_ejar->properties_id,
            'unit_id' => @$response->data->id,
        ]);
        return $response;
    }

    public function add_contract(Request $request)
    {

        $rules = Validator::make($request->all(), [
            'start_date' => 'required',
            'end_date' => 'required',
            'estate_id' => 'required',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

//        $check = RentalContracts::where('estate_id', $request->get('estate_id'))->where(function ($q) use ($request) {
//            $q->whereBetween('start_date', [$request->start_date, $request->end_date])->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
//        })->first();
//        if ($check) {
//            return 'contract already exist in the same date';
//        }



        $data = [
           'start_date' => $request->start_date,
           'end_date' => $request->end_date,
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => api_url . 'ejar.php?type=add_contract',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($response);
        $id = @$response->data->id;

        $rent = RentalContracts::where('estate_id', $request->get('estate_id'))->orderBy('id', 'desc')->first();
        if ($request->get('start_date') && $request->get('end_date')) {
            $now = strtotime($request->get('start_date'));
            $your_date = strtotime($request->get('end_date'));
            $datediff = $your_date - $now;
            $diff = round($datediff / (60 * 60 * 24));
        }
        if ($rent) {
            $rent->update([
                'ejar_id_info' => $id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'contract_interval' => $diff,
            ]);
        } else {
            $estate = Estate::find($request->get('estate_id'));
            RentalContracts::create([
                'user_id' => $estate->user_id,
                'estate_id' => $estate->id,
                'estate_group_id' => $estate->group_estate_id,
                'ejar_id_info' => $id,
                'contract_number' => $this->uniqe_code(),
                'date_of_writing_the_contract' => Carbon::now()->format('Y-m-d'),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'contract_interval' => $diff,
            ]);
        }
        return $response;

    }

    public function add_property_and_unit_contract(Request $request, $contract_id)
    {

        $rules = Validator::make($request->all(), [
            'estate_id' => 'required',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $check_exist_ejar = EjarEstateData::where('estate_id', $request->get('estate_id'))->first();
        if (!$check_exist_ejar) {
            return 'no data';
        }
        $data = [
            'contract_id' => $contract_id,
            'properties_id' => $check_exist_ejar->properties_id,
            'unit_id' => $check_exist_ejar->unit_id,
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => api_url . 'ejar.php?type=add_property_and_unit_contract',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);
        curl_close($curl);



        $response = json_decode($response);
        $id = @$response->data->id;
        $check_exist_ejar->update([
            'contract_unit_id' => $id
        ]);
        return $response;
    }

    public function select_parties_contract(Request $request, $contract_id)
    {
        $rules = Validator::make($request->all(), [
            'parties_role' => 'required',
            'parties_entity_id' => 'required',
            'parties_entity_type' => 'required',
        ]);

        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }


        $role = $request->parties_role;
        $parties_entity_id = $request->parties_entity_id;
        $parties_entity_type = $request->parties_entity_type;


        $data = [
            'contract_id' => $contract_id,
            'role' => $role,
            'parties_entity_id' =>$parties_entity_id,
            'parties_entity_type' =>$parties_entity_type,
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => api_url . 'ejar.php?type=select_parties_contract',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);
        curl_close($curl);


        $response = json_decode($response);

        return $response;
    }

    public function financial_information_contract(Request $request, $contract_id)
    {


        $security_deposit_required = $request->payment_value > 0 ? 'true' : 'false';
        $retainer_fee_required = $request->rental_commission ? 'true' : 'false';
        $amount = $request->payment_value;
        $rental_commission = $request->rental_commission;
        $ejar_late_fees_charged_required = $request->ejar_late_fees_charged_required;
        $ejar_late_fees_charged_amount = $request->ejar_late_fees_charged_amount;
        $ejar_brokerage_fee_required = $request->ejar_brokerage_fee_required;
        $ejar_iban_number = $request->ejar_iban_number;
        $ejar_iban_belong_to = $request->ejar_iban_belong_to;
        $ejar_brokerage_fee_amount = $request->ejar_brokerage_fee_amount;
        $ejar_brokerage_fee_due_date = $request->brokerage_fee_due_date ?? Carbon::now()->addDays(5)->format('Y-m-d');
        $ejar_brokerage_fee_paid_by = $request->brokerage_fee_paid_by ?? 'tenant';


        $data = [
            'contract_id' => $contract_id,
            'security_deposit_required' => $security_deposit_required,
            'amount' => $amount,
            'retainer_fee_required' => $retainer_fee_required,
            'rental_commission' => $rental_commission,
            'ejar_late_fees_charged_required' => $ejar_late_fees_charged_required,
            'ejar_late_fees_charged_amount' => $ejar_late_fees_charged_amount,
            'ejar_brokerage_fee_required' => $ejar_brokerage_fee_required,
            'ejar_iban_number' => $ejar_iban_number,
            'ejar_iban_belong_to' => $ejar_iban_belong_to,
            'ejar_brokerage_fee_amount' => $ejar_brokerage_fee_amount,
            'ejar_brokerage_fee_due_date' => $ejar_brokerage_fee_due_date,
            'ejar_brokerage_fee_paid_by' => $ejar_brokerage_fee_paid_by,
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => api_url . 'ejar.php?type=financial_information_contract',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);
        curl_close($curl);


        $rent = RentalContracts::where('ejar_id_info', $contract_id)->first();
        if ($rent) {
            $rent->update([
                'payment_value' => $request->payment_value,

            ]);
        }


        return $response;
    }

    public function contract_unit_services_contract(Request $request, $contract_id)
    {

        $check_exist_ejar = EjarEstateData::where('estate_id', $request->get('estate_id'))->first();
        if (!$check_exist_ejar) {
            return 'no data';
        }

        $collect = collect();

        if ($request->waters) {
            $collect->push([
                'type' => 'water',
                'amount' => $request->waters,
                'currency' => 'SAR',
            ]);
        }

        if ($request->electricity) {
            $collect->push([
                'type' => 'electricity',
                'amount' => $request->electricity,
                'currency' => 'SAR',
            ]);
        }

        if ($request->gas) {
            $collect->push([
                'type' => 'gas',
                'amount' => $request->gas,
                'currency' => 'SAR',
            ]);
        }

        foreach ($collect->all() as $key => $value) {

            $type = $value['type'];
            $amount = $value['amount'];

            $data = [
                'contract_id' => $contract_id,
                'contract_unit_id' => $check_exist_ejar->contract_unit_id,
                'type' => $type,
                'amount' => $amount,
            ];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => api_url . 'ejar.php?type=contract_unit_services_contract',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
            ));

            $response = curl_exec($curl);
            curl_close($curl);
        }

        return true;
    }

    public function rental_fee_contract(Request $request, $contract_id)
    {

        $check_exist_ejar = EjarEstateData::where('estate_id', $request->get('estate_id'))->first();
        if (!$check_exist_ejar) {
            return 'no data';
        }
        $total = $request->rent_total_amount;
        if ($request->payment_type) {
            //'annually','monthly','with_payments','one_time'
            if ($request->payment_type == 'annually') {
                $payment_type = 'annual';
            } elseif ($request->payment_type == 'monthly') {
                $payment_type = 'monthly';
            } elseif ($request->payment_type == 'with_payments') {
                $payment_type = 'one_time_pay';
            } elseif ($request->payment_type == 'one_time') {
                $payment_type = 'one_time_pay';
            } else {
                $payment_type = 'one_time_pay';
            }
        } else {
            $payment_type = 'one_time_pay';
        }

        $data = [
            'contract_id' => $contract_id,
            'total' => $total,
            'payment_type' => $payment_type,
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => api_url . 'ejar.php?type=rental_fee_contract',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);
        curl_close($curl);



        $rent = RentalContracts::where('ejar_id_info', $contract_id)->first();
        if ($rent) {
            $rent->update([
                'rent_total_amount' => $total,
                'payment_type' => $request->payment_type,
                'status' => 'active',
            ]);
        }

        $user = $request->user();
        $sub = $user->platform_plan->where('status', 'active')->first();
        if (!$sub) {
            return response()->error(__('views.user not subscribed to any plan'));
        } else {
            $sub->contract_number_used = $sub->contract_number_used + 1;
            $sub->save();

            if ($sub->contract_number_used >= $sub->contract_number) {
                $sub->status = 'expired';
                $sub->save();
            }
        }


        return $response;
    }

//    public function update_fee_contract(Request $request, $contract_id)
//    {
//
//        $total = $request->rent_total_amount;
//
//
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => 'https://integration-test.housingapps.sa/api/v1/ecrs/contracts/' . $contract_id . '/rental_fee',
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => '',
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => 'PATCH',
//            CURLOPT_POSTFIELDS => array('[data][type]' => 'rental_fees', '[data][attributes][total_units_rent]' => '{ "amount": ' . $total . ', "currency": "SAR" }', '[data][attributes][rent_type]' => 'for_each_unit', '[data][attributes][billing_type]' => 'monthly', '[data][attributes][utilities_and_services_required]' => 'true'),
//            CURLOPT_HTTPHEADER => array(
//                'Authorization: Basic ' . ejar_authorization
//            ),
//        ));
//
//        $response = curl_exec($curl);
//
//        curl_close($curl);
//        echo $response;
//
//    }

    public function contract_terms_contract(Request $request, $contract_id)
    {

        $data = [
            'contract_id' => $contract_id,
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => api_url . 'ejar.php?type=contract_terms_contract',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
        ));

        $response = curl_exec($curl);
        curl_close($curl);


        return $response;
    }

    public function submit_contract(Request $request, $contract_id)
    {


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => ejar_url . 'ContractSubmit?contract_id=' . $contract_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . ejar_authorization
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;

    }

    public function ejar_entity_id($request, $full_data = null)
    {
        if ($request->get('ejar_entity_type') == 'individual_entities') {

            $data = [
                'type' => 'individual_entities',
                'ejar_id_number' => $request->ejar_id_number,
                'ejar_id_type' => $request->ejar_id_type,
                'ejar_date_of_birth_hijri' => $request->ejar_date_of_birth_hijri,
            ];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => api_url . 'ejar.php?type=entity_id',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
            ));

            $response = curl_exec($curl);
            curl_close($curl);


            $response = json_decode($response);
            if ($full_data == 'full_data') {
                return $response;
            } else {
                return @$response->data->id;
            }

        } else {
            $data = [
                'type' => 'organization_entities',
                'ejar_registration_number' => $request->ejar_registration_number,
                'ejar_registration_date' => $request->ejar_registration_date,
            ];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => api_url . 'ejar.php?type=entity_id',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            $response = json_decode($response);
            if ($full_data == 'full_data') {
                return $response;
            } else {
                return @$response->data->id;
            }
        }
    }

    public function uniqe_code()
    {
        $code = rand(10000, 99999);
        $RentalContracts = RentalContracts::where('contract_number', $code)->first();
        if ($RentalContracts) {
            $this->uniqe_code();
        } else {
            return $code;
        }
    }


    public function ejar_contracts()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => ejar_url . 'Contracts?page_number=1&page_size=10',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . ejar_authorization
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function ejar_contract_status($contract_id)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => ejar_url . 'ContractStatus?contract_number=' . $contract_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . ejar_authorization
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function ejar_contract_delete($contract_id)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => ejar_url . 'Contracts?contract_id=' . $contract_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . ejar_authorization
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function get_ejar_data($id)
    {
        $ejar = EjarEstateData::where('estate_id', $id)->first();
        if ($ejar) {
            return response()->success("Ejar data", $ejar);
        } else {
            return false;
        }
    }

    public function download($contract_id)
    {
        $url = 'https://integration-test.housingapps.sa/api/v1/contracts/' . $contract_id . '/template/pdf';
        $file = file_get_contents($url);
        $file_name = Carbon::now()->timestamp . '.pdf';
        $file_path = public_path('uploads/contracts/');
        if (!file_exists($file_path) && !is_dir($file_path)) {
            mkdir($file_path, 0777, true);
        }
        $file_path = public_path('uploads/contracts/' . $file_name);
        file_put_contents($file_path, $file);
        $data = [
            'contract_id' => $contract_id,
            'file_name' => $file_name,
            'file_path' => $file_path,
        ];

        return response()->success("Download", $data);


    }

    public function get_properties(Request $request)
    {


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://integration-test.housingapps.sa/api/v1/ecrs/properties',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '{
  "filter": {
 "reference_number": "' . $request->reference_number . '",
    "property_number": "' . $request->property_number . '"
  },
  "page": {
    "number": ' . $request->number . ',
    "size":  ' . $request->size . '
  }
}',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . ejar_authorization,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;

    }

    public function get_unity(Request $request)
    {


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://integration-test.housingapps.sa/api/v1/ecrs/' . $request->properties . '/units',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => '{
    "data": {
        "document_number": "' . $request->document_number . '",
        "issued_date": "' . $request->issued_date . '",
        "ownership_document_type": "paper_title_deed"
    }
}',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic Ym8tMjA1MDEyMjI1MzpjY0hXTG1ieXFSTmhoSzZQdzg1WlFjQnlsUlJueGpPMnFHRUg3dUtWQ3lsSmhVTm9hQmpUOHlLd3RHc2VOUEJn',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;

    }

    public function entity_endpoints(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }
        $user = $request->user();
        $rules = Validator::make($request->all(), [
            'ejar_entity_type' => 'required',
            'ejar_id_number' => 'required_if:ejar_entity_type,==,individual_entities',
            'ejar_id_type' => 'required_if:ejar_entity_type,==,individual_entities',
            'ejar_date_of_birth_hijri' => 'required_if:ejar_entity_type,==,individual_entities',
            'ejar_registration_number' => 'required_if:ejar_entity_type,==,organization_entities',
            'ejar_registration_date' => 'required_if:ejar_entity_type,==,organization_entities',
        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        return response()->success(__("views.endpoints"), $this->ejar_entity_id($request, 'full_data'));
    }

    public function organization_entities(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }
        $user = $request->user();
        $rules = Validator::make($request->all(), [
            'ejar_entity_type' => 'required',
            'ejar_registration_number' => 'required_if:ejar_entity_type,==,organization_entities',
            'ejar_registration_date' => 'required_if:ejar_entity_type,==,organization_entities',
        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }

        $request->merge(['ejar_registration_date' => convert_number_to_english($request->get('ejar_registration_date'))]);
        return response()->success(__("views.endpoints"), $this->ejar_entity_id($request, 'full_data'));
    }


    public function individual_entities(Request $request)
    {
        if (!$request->user()) {
            return response()->error(__('views.not authorized'));
        }
        $user = $request->user();
        $rules = Validator::make($request->all(), [
            'ejar_entity_type' => 'required',
            'ejar_id_number' => 'required_if:ejar_entity_type,==,individual_entities',
            'ejar_id_type' => 'required_if:ejar_entity_type,==,individual_entities',
            'ejar_date_of_birth_hijri' => 'required_if:ejar_entity_type,==,individual_entities',
        ]);
        if ($rules->fails()) {
            return JsonResponse::fail($rules->errors()->first(), 400);
        }
        $request->merge(['ejar_date_of_birth_hijri' => convert_number_to_english($request->get('ejar_date_of_birth_hijri'))]);
        return response()->success(__("views.endpoints"), $this->ejar_entity_id($request, 'full_data'));
    }


}
