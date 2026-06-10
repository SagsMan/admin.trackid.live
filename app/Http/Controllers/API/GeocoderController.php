<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;




class GeocoderController extends Controller
{


    public function index(Request $request, $type)
    {
        if ($type == null || $type == "forward") {
            return $this->forward($request);
        } else if (!empty($request->place_id)) {
            return $this->reverseDetails($request);
        }
        return $this->reverse($request);
    }

    public function forward(Request $request)
    {


        try {
            $geocoderType = strtolower(env('geocoderType') ?? "google");
            $addresses = [];

            if ($geocoderType == "opencage") {
                $opencageApiKey = env('opencageApiKey');
                $api = "https://api.opencagedata.com/geocode/v1/json?q={$request->lat},{$request->lng}&key=$opencageApiKey";
                $response = Http::get($api);
                if ($response->successful()) {
                    //
                    foreach ($response["results"] as $address) {
                        $addresses[] = [
                            "geometry" => [
                                "location" => [
                                    "lat" => $address["geometry"]["lat"],
                                    "lng" => $address["geometry"]["lng"],
                                ],
                            ],
                            "formatted_address" => $address["formatted"],
                            "country" => $address["components"]["country"],
                            "country_code" => $address["components"]["country_code"],
                            "postal_code" => $address["components"]["postal_code"] ?? "",
                            "locality" => $address["components"]["town"] ?? '',
                            "subLocality" => $address["components"]["town"] ?? '',
                            "administrative_area_level_1" => $address["components"]["state"] ?? '',
                            "administrative_area_level_2" => $address["components"]["state"] ?? '',
                            "thorough_fare" => $address["components"]["thorough_fare"] ?? '',
                            "sub_thorough_fare" => $address["components"]["sub_thorough_fare"] ?? '',
                        ];
                    }
                } else {
                    throw new \Exception($response->body(), 1);
                }
            } else if ($geocoderType == "radar") {
                $radarApiKey = env('radarApiKey');
                $api = "https://api.radar.io/v1/geocode/reverse?coordinates={$request->lat},{$request->lng}&layers=fine";
                $response = Http::withHeaders([
                    'Authorization' => $radarApiKey,
                ])->get($api);

                if ($response->successful()) {
                    //
                    foreach ($response["addresses"] as $address) {
                        $addresses[] = [
                            "geometry" => [
                                "location" => [
                                    "lat" => $address["latitude"],
                                    "lng" => $address["longitude"],
                                ],
                            ],
                            "formatted_address" => $address["placeLabel"] ?? $address["formattedAddress"],
                            "country" => $address["country"],
                            "country_code" => $address["countryCode"],
                            "postal_code" => $address["postal_code"] ?? "",
                            "locality" => $address["city"] ?? '',
                            "subLocality" => $address["city"] ?? '',
                            "administrative_area_level_1" => $address["state"] ?? '',
                            "administrative_area_level_2" => $address["state"] ?? '',
                            "thorough_fare" => $address["thorough_fare"] ?? '',
                            "sub_thorough_fare" => $address["sub_thorough_fare"] ?? '',
                        ];
                    }
                } else {
                    throw new \Exception($response->json()["meta"]["message"] ?? $response->json(), 1);
                }
            } else if ($geocoderType == "locationiq") {
                $locationiqApiKey = env('locationiqApiKey');
                $api = "https://us1.locationiq.com/v1/reverse.php?key={$locationiqApiKey}&lat={$request->lat}&lon={$request->lng}&format=json";
                $response = Http::get($api);
                if ($response->successful()) {
                    //
                    $address = $response->json();
                    $addresses[] = [
                        "geometry" => [
                            "location" => [
                                "lat" => $address["lat"],
                                "lng" => $address["lon"],
                            ],
                        ],
                        "formatted_address" => $address["display_address"] ?? $address["display_name"],
                        "feature_name" => $address["display_name"] ?? $address["display_address"],
                        "country" => $address["address"]["country"],
                        "country_code" => $address["address"]["country_code"],
                        "postal_code" => $address["address"]["postalcode"] ?? "",
                        "locality" => $address["address"]["city"] ?? $address["address"]["town"] ?? '',
                        "subLocality" => $address["address"]["county"] ?? $address["address"]["city"] ?? '',
                        "administrative_area_level_1" => $address["address"]["state"] ?? '',
                        "administrative_area_level_2" => $address["address"]["state"] ?? '',
                        "thorough_fare" => $address["address"]["thorough_fare"] ?? '',
                        "sub_thorough_fare" => $address["address"]["sub_thorough_fare"] ?? '',
                    ];
                } else {
                    throw new \Exception($response->body(), 1);
                }
            } else if ($geocoderType == "mapbox") {
                $locationType = $request->location_type ?? "";
                $mapBoxApiKey = env('MAPBOX_API_KEY');
                $url = "https://api.mapbox.com/search/geocode/v6/reverse";
                $response = Http::get($url, [
                    'latitude' => $request->lat,
                    'longitude' => $request->lng,
                    'access_token' => $mapBoxApiKey,
                ]);

                if ($response->successful()) {
                    $address = $response->json();
                    $features = $address['features'] ?? [];

                    $contextMap = [];
                    $mainFeature = $features[0] ?? null;

                    // Build a map of context features for quick lookup
                    foreach ($features as $feature) {
                        $type = $feature['properties']['feature_type'] ?? null;
                        if ($type) {
                            $contextMap[$type] = $feature['properties'];
                        }
                    }

                    $geometry = $mainFeature['geometry']['coordinates'] ?? [null, null];
                    $properties = $mainFeature['properties'] ?? [];
                    $context = $properties['context'] ?? [];
                    // Helper function to safely extract nested names
                    $contextName = fn($key) => $context[$key]['name'] ?? '';
                    // Final structured object
                    $formattedAddressObject = [
                        "geometry" => [
                            "location" => [
                                "lat" => $geometry[1],
                                "lng" => $geometry[0],
                            ],
                        ],
                        "formatted_address" => $properties['full_address'] ?? '',
                        "feature_name" => $properties['name'] ?? '',
                        "country" => $context['country']['name'] ?? '',
                        "country_code" => $context['country']['country_code'] ?? '',
                        "postal_code" => $context['postcode']['name'] ?? '',
                        "locality" => $contextName('place'), // city
                        "subLocality" => $contextName('locality'), // town/area
                        "administrative_area_level_1" => $contextName('region'), // state
                        "administrative_area_level_2" => $context['place']['alternate']['name'] ?? '', // county if available
                        "thorough_fare" => '', // not available from Mapbox by default
                        "sub_thorough_fare" => '', // not available from Mapbox by default
                    ];
                    $addresses[] = $formattedAddressObject;
                } else {
                    throw new \Exception($response->body(), 1);
                }
            } else {
                //google
                $googleMapKey = env('googleMapKey');
                $resultLimit = $request->result_limit ?? 5;
                $locationType = $request->location_type ?? "";
                $api = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$request->lat},{$request->lng}&key=$googleMapKey";
                $api .= "&location_type=$locationType&limit=$resultLimit";
                $response = Http::get($api);

                if ($response->successful()) {
                    //
                    foreach ($response["results"] as $address) {
                        $addresses[] = [
                            "geometry" => [
                                "location" => [
                                    "lat" => $address["geometry"]["location"]["lat"],
                                    "lng" => $address["geometry"]["location"]["lng"],
                                ],
                            ],
                            "formatted_address" => $address["formatted_address"] ?? '',
                            "country" => $this->getTypeFromAddressComponents("country", $address),
                            "country_code" => $this->getTypeFromAddressComponents("country", $address, "short_name"),
                            "postal_code" => $this->getTypeFromAddressComponents("postal_code", $address),
                            "locality" => $this->getTypeFromAddressComponents("locality", $address),
                            "subLocality" => $this->getTypeFromAddressComponents("sublocality", $address),
                            "administrative_area_level_1" => $this->getTypeFromAddressComponents("administrative_area_level_1", $address),
                            "administrative_area_level_2" => $this->getTypeFromAddressComponents("administrative_area_level_2", $address),
                            "thorough_fare" => $this->getTypeFromAddressComponents("thorough_fare", $address),
                            "sub_thorough_fare" => $this->getTypeFromAddressComponents("sub_thorough_fare", $address),
                        ];
                    }
                } else {
                    logger("GeocoderController Error", [$response->json()]);
                    throw new \Exception($response->json()["meta"]["message"] ?? $response->json()['message'] ?? "Failed", 1);
                }
            }

            return response()->json([
                "data" => $addresses,
            ], 200);

            return [
                $request->all(),
                $geocoderType,
                $api,
                $response->json(),
            ];
        } catch (\Exception $ex) {
            return response()->json([
                "message" => $ex->getMessage(),
            ], 400);
        }
    }


    public function getTypeFromAddressComponents(
        $type,
        $searchResult,
        $nameTye = "long_name"
    ) {
        //
        $result = "";
        //
        foreach ($searchResult["address_components"] as $componenet) {
            $found = in_array($type, $componenet["types"]);
            if ($found) {
                $result = $componenet[$nameTye];
                break;
            }
        }
        return $result;
    }

    public function reverse(Request $request)
    {

        try {
            $geocoderType = env('geocoderType') ?? "google";
            $countiresSearch = env('placeFilterCountryCodes');
            $addresses = [];

            if ($geocoderType == "Opencage") {
                $opencageApiKey = env('opencageApiKey');
                $api = "https://api.opencagedata.com/geocode/v1/json?q={$request->keyword}&key=$opencageApiKey&pretty=1&countrycode={$countiresSearch}";
                $response = Http::get($api);
                if ($response->successful()) {
                    //
                    foreach ($response["results"] as $address) {
                        $addresses[] = [
                            "geometry" => [
                                "location" => [
                                    "lat" => $address["geometry"]["lat"],
                                    "lng" => $address["geometry"]["lng"],
                                ],
                            ],
                            "formatted_address" => $address["formatted"],
                            "country" => $address["components"]["country"],
                            "country_code" => $address["components"]["country_code"],
                            "postal_code" => $address["components"]["postal_code"] ?? "",
                            "locality" => $address["components"]["city"] ?? '',
                            "subLocality" => $address["components"]["county"] ?? $address["components"]["town"] ?? '',
                            "administrative_area_level_1" => $address["components"]["state"] ?? '',
                            "administrative_area_level_2" => $address["components"]["state"] ?? '',
                            "thorough_fare" => $address["components"]["thorough_fare"] ?? '',
                            "sub_thorough_fare" => $address["components"]["sub_thorough_fare"] ?? '',
                        ];
                    }
                } else {
                    throw new \Exception($response->body(), 1);
                }
            } else if ($geocoderType == "Radar") {
                $radarApiKey = env('radarApiKey');
                $api = "https://api.radar.io/v1/search/autocomplete?query={$request->keyword}&country={$countiresSearch}";
                $response = Http::withHeaders([
                    'Authorization' => $radarApiKey,
                ])->get($api);

                if ($response->successful()) {
                    //
                    foreach ($response["addresses"] as $address) {
                        $addresses[] = [
                            "geometry" => [
                                "location" => [
                                    "lat" => $address["latitude"],
                                    "lng" => $address["longitude"],
                                ],
                            ],
                            "formatted_address" => $address["placeLabel"] ?? $address["formattedAddress"],
                            "country" => $address["country"],
                            "country_code" => $address["countryCode"],
                            "postal_code" => $address["postal_code"] ?? "",
                            "locality" => $address["city"] ?? '',
                            "subLocality" => $address["county"] ?? $address["neighborhood"] ?? $address["city"] ?? '',
                            "administrative_area_level_1" => $address["state"] ?? '',
                            "administrative_area_level_2" => $address["state"] ?? '',
                            "thorough_fare" => $address["thorough_fare"] ?? '',
                            "sub_thorough_fare" => $address["sub_thorough_fare"] ?? '',
                        ];
                    }
                } else {
                    throw new \Exception($response->json()["meta"]["message"] ?? $response->json(), 1);
                }
            } else if ($geocoderType == "mapbox") {
                $mapBoxApiKey = env('MAPBOX_API_KEY');
                $api = "https://api.mapbox.com/search/geocode/v6/forward";
                $response = Http::get($api, [
                    "q" => $request->keyword,
                    "proximity" => "ip",
                    "access_token" => $mapBoxApiKey,
                    "country" => $countiresSearch,
                    "limit" => 5,
                ]);

                if ($response->successful()) {
                    $address = $response->json();
                    $features = $address['features'] ?? [];
                    foreach ($features as $feature) {
                        $geometry = $feature['geometry']['coordinates'] ?? [null, null];
                        $properties = $feature['properties'] ?? [];
                        $context = $properties['context'] ?? [];

                        // Helper to get context name safely
                        $contextName = fn($key) => $context[$key]['name'] ?? '';

                        $addresses[] = [
                            "geometry" => [
                                "location" => [
                                    "lat" => $geometry[1],
                                    "lng" => $geometry[0],
                                ],
                            ],
                            "formatted_address" => $properties['full_address'] ?? '',
                            "feature_name" => $properties['name'] ?? '',
                            "country" => $context['country']['name'] ?? '',
                            "country_code" => $context['country']['country_code'] ?? '',
                            "postal_code" => $context['postcode']['name'] ?? '',
                            "locality" => $contextName('place'),
                            "subLocality" => $contextName('locality'),
                            "administrative_area_level_1" => $contextName('region'),
                            "administrative_area_level_2" => $context['place']['alternate']['name'] ?? '',
                            "thorough_fare" => '',
                            "sub_thorough_fare" => '',
                        ];
                    }
                } else {
                    throw new \Exception($response->json()["meta"]["message"] ?? $response->json(), 1);
                }
            } else if ($geocoderType == "Locationiq") {
                $locationiqApiKey = env('locationiqApiKey');
                $api = "https://api.locationiq.com/v1/autocomplete.php?key={$locationiqApiKey}&q={$request->keyword}&limit=5&countrycodes={$countiresSearch}";
                // logger("Locationiq request api", [$api]);
                $response = Http::get($api);

                if ($response->successful()) {
                    //
                    foreach ($response->json() as $address) {
                        $addresses[] = [
                            "geometry" => [
                                "location" => [
                                    "lat" => $address["lat"],
                                    "lng" => $address["lon"],
                                ],
                            ],
                            "formatted_address" => $address["display_address"] ?? $address["display_name"],
                            "feature_name" => $address["display_name"] ?? $address["display_address"],
                            "country" => $address["address"]["country"],
                            "country_code" => $address["address"]["country_code"],
                            "postal_code" => $address["address"]["postalcode"] ?? "",
                            "locality" => $address["address"]["city"] ?? '',
                            "subLocality" => $address["address"]["county"] ?? $address["address"]["city"] ?? '',
                            "administrative_area_level_1" => $address["address"]["state"] ?? '',
                            "administrative_area_level_2" => $address["address"]["state"] ?? '',
                            "thorough_fare" => $address["address"]["thorough_fare"] ?? '',
                            "sub_thorough_fare" => $address["address"]["sub_thorough_fare"] ?? '',
                        ];
                    }
                } else {
                    throw new \Exception($response->json()["meta"]["message"] ?? $response->json(), 1);
                }
            } else {
                //google
                $googleMapKey = env('googleMapKey');
                $api = "https://maps.googleapis.com/maps/api/place/textsearch/json?query={$request->keyword}&key=$googleMapKey&region={$countiresSearch}&location={$request->locoation}";
                $response = Http::get($api);

                if ($response->successful()) {

                    //
                    foreach ($response["results"] as $address) {
                        $addresses[] = [
                            "geometry" => [
                                "location" => [
                                    "lat" => $address["geometry"]["location"]["lat"],
                                    "lng" => $address["geometry"]["location"]["lng"],
                                ],
                            ],
                            "place_id" => $address["place_id"] ?? '',
                            "description" => $address["name"] ?? $address["description"] ?? "",
                            "name" => $address["name"] ?? $address["description"] ?? "",
                            "formatted_address" => $address["formatted_address"] ?? '',
                            "country" => "",
                            "country_code" => "",
                            "postal_code" => "",
                            "locality" => "",
                            "subLocality" => "",
                            "administrative_area_level_1" => "",
                            "administrative_area_level_2" => "",
                            "thorough_fare" => "",
                            "sub_thorough_fare" => "",
                        ];
                    }
                } else {
                    throw new \Exception($response->json()["meta"]["message"] ?? $response->json(), 1);
                }
            }

            return response()->json([
                "data" => $addresses,
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                "message" => $ex->getMessage(),
            ], 400);
        }
    }

    public function newReverse(Request $request)
    {

        try {
            $geocoderType = strtolower(env('geocoderType') ?? "google");
            $countiresSearch = env('placeFilterCountryCodes');
            $addresses = [];

            if ($geocoderType != "google") {
                return $this->reverse($request);
            } else {
                //google
                $googleMapKey = env('googleMapKey');
                $api = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input={$request->keyword}&key=$googleMapKey&location={$request->locoation}&region=" . ($request->region ?? $countiresSearch) . "";
                $response = Http::get($api);

                if ($response->successful()) {

                    //
                    foreach ($response["predictions"] as $address) {
                        $addresses[] = $address;
                    }
                } else {
                    throw new \Exception($response->json()["meta"]["message"] ?? $response->json(), 1);
                }
            }

            return response()->json([
                "data" => $addresses,
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                "message" => $ex->getMessage(),
            ], 400);
        }
    }

    public function reverseDetails(Request $request)
    {

        try {

            $addressData = null;
            //google
            $googleMapKey = env('googleMapKey');
            $api = "https://maps.googleapis.com/maps/api/place/details/json?fields=address_component,formatted_address,name,geometry&place_id={$request->place_id}&key=$googleMapKey";
            $response = Http::get($api);

            if ($response->successful()) {
                // https: //maps.googleapis.com/maps/api/place/details/json?fields=address_component,formatted_address,name,geometry&place_id=
                //
                $address = $response["result"];
                if ($request->plain ?? false) {
                    $addressData = $address;
                } else {
                    $addressData = [
                        "geometry" => [
                            "location" => [
                                "lat" => $address["geometry"]["location"]["lat"],
                                "lng" => $address["geometry"]["location"]["lng"],
                            ],
                        ],
                        "formatted_address" => $address["formatted_address"] ?? '',
                        "country" => $this->getTypeFromAddressComponents("country", $address),
                        "country_code" => $this->getTypeFromAddressComponents("country", $address, "short_name"),
                        "postal_code" => $this->getTypeFromAddressComponents("postal_code", $address),
                        "locality" => $this->getTypeFromAddressComponents("locality", $address),
                        "subLocality" => $this->getTypeFromAddressComponents("sublocality", $address),
                        "administrative_area_level_1" => $this->getTypeFromAddressComponents("administrative_area_level_1", $address),
                        "administrative_area_level_2" => $this->getTypeFromAddressComponents("administrative_area_level_2", $address),
                        "thorough_fare" => $this->getTypeFromAddressComponents("thorough_fare", $address),
                        "sub_thorough_fare" => $this->getTypeFromAddressComponents("sub_thorough_fare", $address),
                    ];
                }
            } else {
                throw new \Exception($response->json()["meta"]["message"] ?? $response->json(), 1);
            }
            return response()->json($addressData, 200);
        } catch (\Exception $ex) {
            return response()->json([
                "message" => $ex->getMessage(),
            ], 400);
        }
    }
}