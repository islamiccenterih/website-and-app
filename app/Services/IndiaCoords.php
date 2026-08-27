<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Lat/lng for Indian cities so salah, ramadan, and moon times
 * can be computed on the server without waiting on a remote API.
 */
final class IndiaCoords
{
    public static function defaultPoint(): array
    {
        return [
            'lat' => (float) setting('location_lat', '27.1591'),
            'lng' => (float) setting('location_lng', '78.3957'),
            'label' => (string) setting('location_label', 'Firozabad, Uttar Pradesh, India 283203'),
        ];
    }

    /**
     * @return array{lat:float,lng:float}
     */
    public static function forCity(string $city, string $state = ''): array
    {
        $cityKey = self::key($city);
        $stateKey = self::key($state);
        $cities = self::cities();
        if ($cityKey !== '' && $stateKey !== '' && isset($cities[$cityKey . '|' . $stateKey])) {
            return $cities[$cityKey . '|' . $stateKey];
        }
        if ($cityKey !== '' && isset($cities[$cityKey])) {
            return $cities[$cityKey];
        }
        $states = self::states();
        if ($stateKey !== '' && isset($states[$stateKey])) {
            return $states[$stateKey];
        }

        return [
            'lat' => self::defaultPoint()['lat'],
            'lng' => self::defaultPoint()['lng'],
        ];
    }

    private static function key(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['.', ',', '-'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return $value;
    }

    /**
     * @return array<string, array{lat:float,lng:float}>
     */
    private static function cities(): array
    {
        return [
            'firozabad' => ['lat' => 27.1591, 'lng' => 78.3957],
            'firozabad|uttar pradesh' => ['lat' => 27.1591, 'lng' => 78.3957],
            'agra' => ['lat' => 27.1767, 'lng' => 78.0081],
            'aligarh' => ['lat' => 27.8974, 'lng' => 78.0880],
            'allahabad' => ['lat' => 25.4358, 'lng' => 81.8463],
            'prayagraj' => ['lat' => 25.4358, 'lng' => 81.8463],
            'bareilly' => ['lat' => 28.3670, 'lng' => 79.4304],
            'kanpur' => ['lat' => 26.4499, 'lng' => 80.3319],
            'lucknow' => ['lat' => 26.8467, 'lng' => 80.9462],
            'meerut' => ['lat' => 28.9845, 'lng' => 77.7064],
            'moradabad' => ['lat' => 28.8386, 'lng' => 78.7733],
            'varanasi' => ['lat' => 25.3176, 'lng' => 82.9739],
            'noida' => ['lat' => 28.5355, 'lng' => 77.3910],
            'ghaziabad' => ['lat' => 28.6692, 'lng' => 77.4538],
            'delhi' => ['lat' => 28.6139, 'lng' => 77.2090],
            'new delhi' => ['lat' => 28.6139, 'lng' => 77.2090],
            'mumbai' => ['lat' => 19.0760, 'lng' => 72.8777],
            'pune' => ['lat' => 18.5204, 'lng' => 73.8567],
            'nagpur' => ['lat' => 21.1458, 'lng' => 79.0882],
            'nashik' => ['lat' => 19.9975, 'lng' => 73.7898],
            'aurangabad|maharashtra' => ['lat' => 19.8762, 'lng' => 75.3433],
            'kolkata' => ['lat' => 22.5726, 'lng' => 88.3639],
            'howrah' => ['lat' => 22.5958, 'lng' => 88.2636],
            'chennai' => ['lat' => 13.0827, 'lng' => 80.2707],
            'coimbatore' => ['lat' => 11.0168, 'lng' => 76.9558],
            'madurai' => ['lat' => 9.9252, 'lng' => 78.1198],
            'bengaluru' => ['lat' => 12.9716, 'lng' => 77.5946],
            'bangalore' => ['lat' => 12.9716, 'lng' => 77.5946],
            'mysore' => ['lat' => 12.2958, 'lng' => 76.6394],
            'hyderabad' => ['lat' => 17.3850, 'lng' => 78.4867],
            'warangal' => ['lat' => 17.9689, 'lng' => 79.5941],
            'ahmedabad' => ['lat' => 23.0225, 'lng' => 72.5714],
            'surat' => ['lat' => 21.1702, 'lng' => 72.8311],
            'vadodara' => ['lat' => 22.3072, 'lng' => 73.1812],
            'rajkot' => ['lat' => 22.3039, 'lng' => 70.8022],
            'jaipur' => ['lat' => 26.9124, 'lng' => 75.7873],
            'jodhpur' => ['lat' => 26.2389, 'lng' => 73.0243],
            'udaipur' => ['lat' => 24.5854, 'lng' => 73.7125],
            'kota' => ['lat' => 25.2138, 'lng' => 75.8648],
            'bhopal' => ['lat' => 23.2599, 'lng' => 77.4126],
            'indore' => ['lat' => 22.7196, 'lng' => 75.8577],
            'gwalior' => ['lat' => 26.2183, 'lng' => 78.1828],
            'jabalpur' => ['lat' => 23.1815, 'lng' => 79.9864],
            'patna' => ['lat' => 25.5941, 'lng' => 85.1376],
            'gaya' => ['lat' => 24.7914, 'lng' => 85.0002],
            'ranchi' => ['lat' => 23.3441, 'lng' => 85.3096],
            'jamshedpur' => ['lat' => 22.8046, 'lng' => 86.2029],
            'bhubaneswar' => ['lat' => 20.2961, 'lng' => 85.8245],
            'cuttack' => ['lat' => 20.4625, 'lng' => 85.8830],
            'guwahati' => ['lat' => 26.1445, 'lng' => 91.7362],
            'imphal' => ['lat' => 24.8170, 'lng' => 93.9368],
            'shillong' => ['lat' => 25.5788, 'lng' => 91.8933],
            'aizawl' => ['lat' => 23.7271, 'lng' => 92.7176],
            'kohima' => ['lat' => 25.6751, 'lng' => 94.1086],
            'itanagar' => ['lat' => 27.0844, 'lng' => 93.6053],
            'gangtok' => ['lat' => 27.3389, 'lng' => 88.6065],
            'agartala' => ['lat' => 23.8315, 'lng' => 91.2868],
            'chandigarh' => ['lat' => 30.7333, 'lng' => 76.7794],
            'amritsar' => ['lat' => 31.6340, 'lng' => 74.8723],
            'ludhiana' => ['lat' => 30.9010, 'lng' => 75.8573],
            'jalandhar' => ['lat' => 31.3260, 'lng' => 75.5762],
            'shimla' => ['lat' => 31.1048, 'lng' => 77.1734],
            'dehradun' => ['lat' => 30.3165, 'lng' => 78.0322],
            'haridwar' => ['lat' => 29.9457, 'lng' => 78.1642],
            'srinagar' => ['lat' => 34.0837, 'lng' => 74.7973],
            'jammu' => ['lat' => 32.7266, 'lng' => 74.8570],
            'kochi' => ['lat' => 9.9312, 'lng' => 76.2673],
            'thiruvananthapuram' => ['lat' => 8.5241, 'lng' => 76.9366],
            'kozhikode' => ['lat' => 11.2588, 'lng' => 75.7804],
            'panaji' => ['lat' => 15.4909, 'lng' => 73.8278],
            'margao' => ['lat' => 15.2736, 'lng' => 73.9581],
            'raipur' => ['lat' => 21.2514, 'lng' => 81.6296],
            'visakhapatnam' => ['lat' => 17.6868, 'lng' => 83.2185],
            'vijayawada' => ['lat' => 16.5062, 'lng' => 80.6480],
            'tirupati' => ['lat' => 13.6288, 'lng' => 79.4192],
            'faridabad' => ['lat' => 28.4089, 'lng' => 77.3178],
            'gurgaon' => ['lat' => 28.4595, 'lng' => 77.0266],
            'gurugram' => ['lat' => 28.4595, 'lng' => 77.0266],
            'mathura' => ['lat' => 27.4924, 'lng' => 77.6737],
            'etah' => ['lat' => 27.5588, 'lng' => 78.6626],
            'mainpuri' => ['lat' => 27.2276, 'lng' => 79.0287],
            'hathras' => ['lat' => 27.5950, 'lng' => 78.0500],
        ];
    }

    /**
     * @return array<string, array{lat:float,lng:float}>
     */
    private static function states(): array
    {
        return [
            'uttar pradesh' => ['lat' => 26.8467, 'lng' => 80.9462],
            'delhi' => ['lat' => 28.6139, 'lng' => 77.2090],
            'maharashtra' => ['lat' => 19.7515, 'lng' => 75.7139],
            'west bengal' => ['lat' => 22.9868, 'lng' => 87.8550],
            'tamil nadu' => ['lat' => 11.1271, 'lng' => 78.6569],
            'karnataka' => ['lat' => 15.3173, 'lng' => 75.7139],
            'telangana' => ['lat' => 18.1124, 'lng' => 79.0193],
            'andhra pradesh' => ['lat' => 15.9129, 'lng' => 79.7400],
            'gujarat' => ['lat' => 22.2587, 'lng' => 71.1924],
            'rajasthan' => ['lat' => 27.0238, 'lng' => 74.2179],
            'madhya pradesh' => ['lat' => 22.9734, 'lng' => 78.6569],
            'bihar' => ['lat' => 25.0961, 'lng' => 85.3131],
            'jharkhand' => ['lat' => 23.6102, 'lng' => 85.2799],
            'odisha' => ['lat' => 20.9517, 'lng' => 85.0985],
            'assam' => ['lat' => 26.2006, 'lng' => 92.9376],
            'punjab' => ['lat' => 31.1471, 'lng' => 75.3412],
            'haryana' => ['lat' => 29.0588, 'lng' => 76.0856],
            'himachal pradesh' => ['lat' => 31.1048, 'lng' => 77.1734],
            'uttarakhand' => ['lat' => 30.0668, 'lng' => 79.0193],
            'jammu and kashmir' => ['lat' => 33.7782, 'lng' => 76.5762],
            'kerala' => ['lat' => 10.8505, 'lng' => 76.2711],
            'goa' => ['lat' => 15.2993, 'lng' => 74.1240],
            'chhattisgarh' => ['lat' => 21.2787, 'lng' => 81.8661],
            'chandigarh' => ['lat' => 30.7333, 'lng' => 76.7794],
            'puducherry' => ['lat' => 11.9416, 'lng' => 79.8083],
            'manipur' => ['lat' => 24.6637, 'lng' => 93.9063],
            'meghalaya' => ['lat' => 25.4670, 'lng' => 91.3662],
            'mizoram' => ['lat' => 23.1645, 'lng' => 92.9376],
            'nagaland' => ['lat' => 26.1584, 'lng' => 94.5624],
            'tripura' => ['lat' => 23.9408, 'lng' => 91.9882],
            'sikkim' => ['lat' => 27.5330, 'lng' => 88.5122],
            'arunachal pradesh' => ['lat' => 28.2180, 'lng' => 94.7278],
            'andaman and nicobar islands' => ['lat' => 11.7401, 'lng' => 92.6586],
            'ladakh' => ['lat' => 34.1526, 'lng' => 77.5770],
        ];
    }
}
