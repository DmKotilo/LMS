<?php

namespace Address\Services;

use Address\Model\Address;
use User\Models\User;
use City\Model\Region;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PHPUnit\Logging\Exception;
use Team\Models\Team;

class AddressService
{

    static function create(array $dadata_address) : Model
    {
        try {
            $dadata_address_array = $dadata_address;
            $address = [];
            $address['dadata_json'] = $dadata_address_array;
            $address['value'] = $dadata_address_array['value'];
            $address['added'] = ($dadata_address_array['added']) ?? null;
            $address['region'] = $dadata_address_array['data']['region_with_type'] ?? null;
            return Address::firstOrCreate(['value' => $address['value'], 'added' => $address['added'], 'region' => $address['region']], $address);
        } catch (\Exception $e) {
            throw new Exception('Произошла ошибка при создании адреса');
        }
    }




}


