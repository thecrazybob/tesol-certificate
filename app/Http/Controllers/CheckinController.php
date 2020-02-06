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

        $encrypted_json_data = Crypt::encrypt($raw_json_data);

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
        $qr_link = 'qr/' . Str::random(6) . '.png';

        if (\App::environment('local')) {
           $path_to_qr = '../storage/app/public/';
        }

        else {
            $path_to_qr = 'storage/app/public/';
        }

        $response = QrCode::format('png')->size(300)->margin(1)->generate($url, $path_to_qr . $qr_link);
        $qr_link = url($qr_link);

        return $qr_link;
        
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

        if (empty($data['name'])) {
            return "Name cannot be empty";
            die();
        }

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

        return redirect()->route('generate-pdf', ['name' => $data['name']]);

    }

}
