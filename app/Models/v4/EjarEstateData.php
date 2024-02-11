<?php

namespace App\Models\v4;

use Illuminate\Database\Eloquent\Model;

class EjarEstateData extends Model
{
    /**
     * The attributes that are guarded from  mass assignable.
     *
     * @var array
     */
    protected $table = 'estates_ejar_info';

    protected $casts = [
        'created_at' => 'datetime:Y-m-d h:i:s',
        'updated_at' => 'datetime:Y-m-d h:i:s',
        'deleted_at' => 'datetime:Y-m-d h:i:s'
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    //

    protected $fillable = [
        'estate_id',
        'issued_date',
        'legal_document_type_name',
        'ownership_document_type',
        'role',
        'entity_type',
        'entity_id',
        'owner_id',
        'region_id',
        'city_id',
        'district_id',
        'contract_type',
        'total_floors',
        'property_usage',
        'established_date',
        'units_per_floor',
        'unit_storeroom',
        'unit_central_ac',
        'unit_desert_cooler',
        'unit_split_unit',
        'unit_backyard',
        'unit_maid_room',
        'unit_is_kitchen_sink_installed',
        'is_cabinet_installed',
        'gas_meter',
        'electricity_meter',
        'water_meter',
        'is_furnished',
        'furnish_type',
        'unit_usage',
        'unit_finishing',
        'length',
        'width',
        'height',
        'include_mezzanine',
        'rooms',
        'issue_place',
        'document_number',
        'ejar_property_type',
        'parking_spaces',
        'elevators',
        'cafeteria',
        'unit_id',
        'properties_id',
        'contract_unit_id',
        'unit_established_date',
        'unit_direction',
        'building_number',
        'postal_code',
        'street_name',
        'additional_code',
        'estate_name',
        'unit_number',
        'kitchen_number',


    ];


}
