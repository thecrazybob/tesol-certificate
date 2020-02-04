<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Bitly;
use Illuminate\Support\Facades\DB;

class CheckinController extends Controller
{
    public function qrcode(Request $request) {
        $raw_json_data = $request->get('data');
        $raw_json_data = '
        {
    "name": "John Doe",
    "email": "john@doe.com"
}';
        $encrypted_json_data = Crypt::encrypt($raw_json_data);

        // Dummy Data for Testing purposes
        // $data['name'] = 'John Doe';
        // $data['email'] = 'john@doe.com';
        
        // Route URL
        $basicUrl = route('checkin');

        // Route Parameters
        $paramUrl = '?data=' . $encrypted_json_data; 

        // Complete (Unshortened) URL
        $url = $basicUrl . $paramUrl;
        
        // Shorten URL through Bit.ly
        $url = Bitly::getUrl($url);
        
        /**
         * Generate QR Code
         */
        $qr_link = '/qr/' . Str::random(6) . '.png';
        $response = QrCode::format('png')->size(300)->margin(0)->generate($url, '../public/' . $qr_link);

        // return url($qr_link);
        return $url;
        // return $response;
        
    }

    public function scan(Request $request)
    {
        
    //         function base64_url_encode($input)
    // {
    //     return strtr($input, '+/=', '._-');
    // }
    
    // function base64_url_decode($input)
    // {
    //     return strtr($input, '._-', '+/=');
    // }
        
        // Get Encrypted Data from Request
        $encrypted_json_data = $request->get('data');

        // Decrypted the data
        $decrypted_json_data = Crypt::decrypt($encrypted_json_data);

        // Convert to Array
        $data = json_decode($decrypted_json_data, true);

        

$duplicates = DB::table('checked_in')
        ->where('email', '=', $data['email'])
        ->where('name', 'like', $data['name'])
        ->get();

        if ($duplicates->isEmpty()) {
            DB::table('checked_in')->insert(
    [
        'name' => $data['name'],
        'email' => $data['email'],
    ]
);

        }
        return $duplicates;

        // Return Data
        return $data['name'] . ' ' . $data['email'];
        
        


    }
}
