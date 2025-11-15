<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\City;
use App\Models\Neighborhood;
use App\Models\Street;
use App\Models\Tariff;
use Illuminate\Http\Request;

class MasterDataApiController extends Controller
{
    public function regions()
    {
        $regions = Region::all();

        return response()->json([
            'data' => $regions->map(function ($region) {
                return [
                    'id' => $region->id,
                    'name' => $region->name,
                ];
            }),
        ]);
    }

    public function cities(Request $request)
    {
        $query = City::query();

        if ($request->has('region_id')) {
            $query->where('region_id', $request->region_id);
        }

        $cities = $query->get();

        return response()->json([
            'data' => $cities->map(function ($city) {
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                    'region_id' => $city->region_id,
                ];
            }),
        ]);
    }

    public function neighborhoods(Request $request)
    {
        $query = Neighborhood::query();

        if ($request->has('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        $neighborhoods = $query->get();

        return response()->json([
            'data' => $neighborhoods->map(function ($neighborhood) {
                return [
                    'id' => $neighborhood->id,
                    'name' => $neighborhood->name,
                    'city_id' => $neighborhood->city_id,
                ];
            }),
        ]);
    }

    public function streets(Request $request)
    {
        $query = Street::with(['neighborhood.city.region']);

        if ($request->has('neighborhood_id')) {
            $query->where('neighborhood_id', $request->neighborhood_id);
        }

        // If city_id is provided, get all streets in that city
        if ($request->has('city_id')) {
            $query->whereHas('neighborhood', function ($q) use ($request) {
                $q->where('city_id', $request->city_id);
            });
        }

        $streets = $query->get();

        return response()->json([
            'data' => $streets->map(function ($street) {
                return [
                    'id' => $street->id,
                    'name' => $street->name,
                    'neighborhood_id' => $street->neighborhood_id,
                    'neighborhood_name' => $street->neighborhood->name ?? null,
                    'city_name' => $street->neighborhood->city->name ?? null,
                    'region_name' => $street->neighborhood->city->region->name ?? null,
                ];
            }),
        ]);
    }

    public function tariffs(Request $request)
    {
        $query = Tariff::where('company_id', $request->user()->company_id)
            ->where('is_active', true);

        $tariffs = $query->orderBy('valid_from', 'desc')->get();

        return response()->json([
            'data' => $tariffs->map(function ($tariff) {
                return [
                    'id' => $tariff->id,
                    'name' => $tariff->name,
                    'price_per_m3' => $tariff->price_per_m3,
                    'for_one_person' => $tariff->for_one_person,
                    'valid_from' => $tariff->valid_from,
                    'valid_to' => $tariff->valid_to,
                    'is_active' => $tariff->is_active,
                ];
            }),
        ]);
    }
}
