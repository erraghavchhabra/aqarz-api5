<?php
DEFINE('ejar_authorization', 'QXFhcnpSRUJyVXNlcjoyMGFkNzdCXzcpYjFeRUZCYSE2ZDA2KDU1MTBlOERB');
DEFINE('ejar_url', 'https://integration-test.housingapps.sa/Ejar/ECRS/');


if (isset($_GET['type'])) {
    $type = $_GET['type'];
    switch ($type) {
        case 'add_properties':
            echo add_properties();
            break;
        case 'add_unit':
            echo add_unit();
            break;
        case 'add_contract':
            echo add_contract();
            break;
        case 'add_property_and_unit_contract':
            echo add_property_and_unit_contract();
            break;
        case 'select_parties_contract':
            echo select_parties_contract();
            break;
        case 'financial_information_contract':
            echo financial_information_contract();
            break;
        case 'contract_unit_services_contract':
            echo contract_unit_services_contract();
            break;
        case 'rental_fee_contract':
            echo rental_fee_contract();
            break;
        case 'contract_terms_contract':
            echo contract_terms_contract();
            break;
        case 'entity_id':
            echo entity_id();
            break;
        default:
            echo 'error 1';
            break;
    }
} else {
    echo 'error 2';
}

function add_properties()
{
    $curl = curl_init();

    $file = $_FILES['scanned_documents'];
    $file = curl_file_create($file['tmp_name'], $file['type'], $file['name']);

    curl_setopt_array($curl, array(
        CURLOPT_URL => ejar_url . 'properties',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => [
            '[data][ownership_document][attributes][document_number]' => $_POST['ejar_document_number'],
            '[data][ownership_document][attributes][issue_place]' => $_POST['ejar_issue_place'],
            '[data][ownership_document][attributes][issued_by]' => $_POST['ejar_issued_by'],
            '[data][ownership_document][attributes][issued_date]' => $_POST['ejar_issued_date'],
            '[data][ownership_document][attributes][legal_document_type_name]' => $_POST['ejar_legal_document_type_name'],
            '[data][ownership_document][attributes][ownership_document_type]' => $_POST['ejar_ownership_document_type'],
            '[data][ownership_document][attributes][scanned_documents]' => $file,
            '[data][owners][attributes][role]' => $_POST['ejar_role'],
            '[data][owners][attributes][entity_type]' => $_POST['ejar_entity_type'],
            '[data][owners][attributes][entity_id]' => $_POST['entity_id'],
            '[data][owners][attributes][owner_id]' => $_POST['ejar_owner_id'],
            '[data][property][attributes][address][attributes][region_id]' => $_POST['ejar_region_id'],
            '[data][property][attributes][address][attributes][city_id]' => $_POST['ejar_city_id'],
            '[data][property][attributes][address][attributes][district_id]' => $_POST['ejar_district_id'],
            '[data][property][attributes][address][attributes][building_number]' => $_POST['building_number'],
            '[data][property][attributes][address][attributes][postal_code]' => $_POST['postal_code'],
            '[data][property][attributes][address][attributes][street_name]' => $_POST['street_name'],
            '[data][property][attributes][address][attributes][additional_number]' => $_POST['additional_code'],
            '[data][property][attributes][address][attributes][latitude]' => $_POST['latitude'],
            '[data][property][attributes][address][attributes][longitude]' => $_POST['longitude'],
            '[data][property][attributes][contract_type]' => 'residential',
            '[data][property][attributes][property_name]' => $_POST['property_name'],
            '[data][property][attributes][property_number]' => $_POST['property_number'],
            '[data][property][attributes][total_floors]' => $_POST['ejar_total_floors'],
            '[data][property][attributes][property_usage]' => $_POST['ejar_property_usage'],
            '[data][property][attributes][property_type]' => $_POST['ejar_property_type'],
            '[data][property][attributes][established_date]' => $_POST['ejar_established_date'],
            '[data][property][attributes][units_per_floor]' => $_POST['ejar_units_per_floor'],
            '[data][property][attributes][associated_facilities][parking_spaces]' => (int) $_POST['ejar_parking_spaces'],
            '[data][property][attributes][associated_facilities][security_entries]' => (int) $_POST['ejar_security_entries'],
            '[data][property][attributes][associated_facilities][banquet_hall]' => (int) $_POST['ejar_banquet_hall'],
            '[data][property][attributes][associated_facilities][elevators]' => (int) $_POST['ejar_elevators'],
            '[data][property][attributes][associated_facilities][gyms_fitness_centers]' => (int) $_POST['ejar_gyms_fitness_centers'],
            '[data][property][attributes][associated_facilities][transfer_service]' => (int) $_POST['ejar_transfer_service'],
            '[data][property][attributes][associated_facilities][cafeteria]' => (int) $_POST['ejar_cafeteria'],
            '[data][property][attributes][associated_facilities][baby_nursery]' => (int) $_POST['ejar_baby_nursery'],
            '[data][property][attributes][associated_facilities][games_room]' => (int) $_POST['ejar_games_room'],
            '[data][property][attributes][associated_facilities][football_yard]' => (int) $_POST['ejar_football_yard'],
            '[data][property][attributes][associated_facilities][volleyball_court]' => (int) $_POST['ejar_volleyball_court'],
            '[data][property][attributes][associated_facilities][tennis_court]' => (int) $_POST['ejar_tennis_court'],
            '[data][property][attributes][associated_facilities][basketball_court]' => (int) $_POST['ejar_basketball_court'],
            '[data][property][attributes][associated_facilities][swimming_pool]' => (int) $_POST['ejar_swimming_pool'],
            '[data][property][attributes][associated_facilities][children_playground]' => (int) $_POST['ejar_children_playground'],
            '[data][property][attributes][associated_facilities][grocery_store]' => (int) $_POST['ejar_grocery_store'],
            '[data][property][attributes][associated_facilities][laundry]' => (int) $_POST['ejar_laundry'],
            '[data][property][attributes][compound_name]' => $_POST['ejar_compound_name'],
        ],
        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic ' . ejar_authorization,
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
//    $response = json_decode($response);


}

function add_unit()
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => ejar_url . 'PropertyUnits?property_id=' . $_POST['properties_id'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
    "data": {
        "attributes": {
            "amenities": {
                "bedrooms": ' . $_POST['bedrooms'] . ',
                "bathrooms_full": ' . $_POST['bathrooms_full'] . ',
                "halls": ' . $_POST['halls'] . ',
                "storeroom": ' . $_POST['storeroom'] . ',
                "central_ac": ' . $_POST['central_ac'] . ',
                "kitchen": ' . $_POST['kitchen'] . ',
                "majles": ' . $_POST['majles'] . ',
                "desert_cooler": ' . $_POST['desert_cooler'] . ',
                "split_unit": ' . $_POST['split_unit'] . ',
                "backyard": ' . $_POST['backyard'] . ',
                "maid_room": ' . $_POST['maid_room'] . ',
                "is_kitchen_sink_installed": ' . $_POST['is_kitchen_sink_installed'] . ',
                "is_cabinet_installed": ' . $_POST['is_cabinet_installed'] . '
            },
            "utilities": {
                "gas_meter": "' . $_POST['gas_meter'] . '",
                "electricity_meter": "' . $_POST['electricity_meter'] . '",
                "water_meter": "' . $_POST['water_meter'] . '"
            },
            "unit_number": "' . $_POST['unit_number'] . '",
            "floor_number": "' . $_POST['floor_number'] . '",
            "is_furnished": "' . $_POST['is_furnished'] . '",
            "furnish_type": "' . $_POST['furnish_type'] . '",
            "unit_type": "' . $_POST['unit_type'] . '",
            "unit_usage": "' . $_POST['unit_usage'] . '",
            "area": ' . $_POST['area'] . ',
            "established_date": "' . $_POST['established_date'] . '",
            "unit_direction": "' . $_POST['unit_direction'] . '",
            "unit_finishing": "' . $_POST['unit_finishing'] . '",
            "unit_dimensions": {
                "length": "' . $_POST['length'] . '",
                "width": "' . $_POST['width'] . '",
                "height": "' . $_POST['height'] . '"
            },
            "include_mezzanine": ' . $_POST['include_mezzanine'] . ',
            "rooms": ' . $_POST['rooms'] . '
        }
    }
}',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic ' . ejar_authorization
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
}

function add_contract()
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => ejar_url . 'Contracts',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
    "data": {
        "attributes": {
            "contract_start_date": "' . $_POST['start_date'] . '",
            "contract_end_date": "' . $_POST['end_date'] . '",
            "contract_type": "residential"
        }
    }
}',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic ' . ejar_authorization
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
}

function add_property_and_unit_contract()
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => ejar_url . 'ContractUnits?contract_id=' . $_POST['contract_id'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
    "data": {
        "contract_property": {
            "id": "' . $_POST['properties_id'] . '",
            "contract_units": [
                {
                    "id": "' . $_POST['unit_id'] . '"
                }
            ]
        }
    }
}',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic ' . ejar_authorization
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
}

function select_parties_contract()
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => ejar_url . 'ContractParties?contract_id=' . $_POST['contract_id'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
    "data": {
        "type": "contract_parties",
        "attributes": {
            "role": "' . $_POST['role'] . '",
            "protect_lessor_identity": false
        },
        "relationships": {
            "entity": {
                "data": {
                    "id": "' . $_POST['parties_entity_id'] . '",
                    "type": "' . $_POST['parties_entity_type'] . '"
                }
            }
        }
    }
}',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic ' . ejar_authorization
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;

}

function financial_information_contract()
{

    $data = '
    {
   "data": {
      "type": "financial_information",
      "attributes": {
         "security_deposit_required": ' . $_POST['security_deposit_required'] . ',
         "security_deposit": {
            "amount": ' . $_POST['amount'] . ',
            "currency": "SAR"
         },
         "retainer_fee_required": ' . $_POST['retainer_fee_required'] . ',
         "retainer_fee": {
            "amount": ' . $_POST['rental_commission'] . ',
            "currency": "SAR"
         },
         "late_fees_charged_required": ' . $_POST['ejar_late_fees_charged_required'] . ',
         "late_fees_charged": {
            "amount": ' . $_POST['ejar_late_fees_charged_amount'] . ',
            "currency": "SAR"
         },
         "brokerage_fee_required": ' . $_POST['ejar_brokerage_fee_required'] . ',
         "brokerage_fee": {
            "amount": ' . $_POST['ejar_brokerage_fee_amount'] . ',
            "currency": "SAR"
         },
         "brokerage_fee_paid_by": "' . $_POST['ejar_brokerage_fee_paid_by'] . '",
         "brokerage_fee_due_date": "' . $_POST['ejar_brokerage_fee_due_date'] . '",
         "iban_number": "' . $_POST['ejar_iban_number'] . '",
         "iban_belong_to": "' . $_POST['ejar_iban_belong_to'] . '"
      }
   }
}
    ';
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => ejar_url . 'ContractFinancialInfo?contract_id=' . $_POST['contract_id'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic ' . ejar_authorization
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
}

function contract_unit_services_contract()
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => ejar_url . 'ContractUnitServices?contract_id=' . $_POST['contract_id'] . '&unit_id=' . $_POST['contract_unit_id'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
    "data": {
        "type": "contract_unit_services",
        "attributes": {
            "utility_service_type": "' . $_POST['type'] . '",
            "to_be_paid_by": "fixed_fee",
            "to_be_paid_at": {
                "amount": ' . $_POST['amount'] . ',
                "currency": "SAR"
            },
            "to_be_paid_per": "annually",
            "selected": true
        }
    }
}',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic ' . ejar_authorization
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function rental_fee_contract()
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => ejar_url . 'ContractRentalFee?contract_id=' . $_POST['contract_id'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
    "data": {
        "type": "rental_fees",
        "attributes": {
            "total_units_rent": {
                "amount": "' . $_POST['total'] . '",
                "currency": "SAR"
            },
            "rent_type": "for_all_units",
            "billing_type": "' . $_POST['payment_type'] . '",
            "utilities_and_services_required": true
        }
    }
}',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic ' . ejar_authorization
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
}

function contract_terms_contract()
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => ejar_url . 'ContractTerms?contract_id=' . $_POST['contract_id'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
  "data": {
    "type": "contract_terms",
    "attributes": {
      "term_type": "residential",
      "ejar_fees_paid_by": "brokerage_office"
    },
    "relationships": {
      "contract_term_answers": {
        "data": [
          {
            "type": "contract_term_answers",
            "attributes": {
              "term_template_id": 39,
              "term_template_key": "residential_followup_with_authorities",
              "enabled": true,
              "answers": {},
              "custom_options": {}
            }
          },
          {
            "type": "contract_term_answers",
            "attributes": {
              "term_template_id": 42,
              "term_template_key": "governing_law_and_dispute_resolution",
              "enabled": true,
              "answers": {
                "governing_law_and_dispute_resolution_option": "real_estate_arbitration"
              },
              "custom_options": {}
            }
          }
        ]
      }
    }
  }
}',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic ' . ejar_authorization
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
}

function entity_id()
{

    if ($_POST['type'] == 'individual_entities') {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => ejar_url . 'Individual_Entities?id_number=' . $_POST['ejar_id_number'] . '&id_type=' . $_POST['ejar_id_type'] . '&date_of_birth_hijri=' . $_POST['ejar_date_of_birth_hijri'],
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
    } else {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => ejar_url . 'Organization_Entities?registration_number=' . $_POST['ejar_registration_number'] . '&registration_date=' . $_POST['ejar_registration_date'],
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
    }

    return $response;
}
