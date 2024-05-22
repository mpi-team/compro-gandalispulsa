<?php

namespace App\Http\Controllers;

use Faker\Factory;
use Illuminate\Http\Request;
use App\Models\Pembayaran;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

class CronosController extends Controller
{
    const BANK_CODE = [
        // 'BCAVA' => '014',
        'MYBVA' => '',
        'PERMATAVA' => '013',
        'BNIVA' => '009',
        'BRIVA' => '002',
        'MANDIRIVA' => '008',
        'SMSVA' => '',
        'MUAMALATVA' => '',
        'CIMBVA' => '022',
        'SAMPOERNAVA' => '153',
        'BSIVA' => '',
        'OCBCVA' => '',
        'DANAMONVA' => '011',
        'BNCVA' => '',
    ];

    public function request($idOrder, $jumlah, $method, $dataUser, $nohp)
    {
        $api = \DB::table('setting_webs')->where('id', 1)->first();
        // {
        //     "bankCode": "014",
        //     "singleUse": true,
        //     "type": "ClosedAmount",
        //     "reference": "123456",
        //     "amount": 10000,
        //     "expiryMinutes": 30,
        //     "viewName": "Mr. Gentur",
        //     "additionalInfo": {
        //         "callback": "http://your-site-callback.com/notify"
        //     }
        // }
        
        // $this->requestVirtualAccount($api, $idOrder, $jumlah);
        // $this->requestQris($api, $idOrder, $jumlah);

        // $data = [
        //     'method' => $method,
        //     'merchant_ref' => $idOrder,
        //     'amount' => $jumlah,
        //     'customer_name' => env("APP_NAME"),
        //     'customer_email' => $dataUser,
        //     'customer_phone' => $nohp,
        //     'order_items' => [
        //         [
        //             'name' => 'Pembayaran ' . $method . ' ' . $idOrder,
        //             'price' => $jumlah,
        //             'quantity' => 1,
        //         ]
        //     ],
        //     'callback_url' => env('APP_URL') . '/callback',
        //     'return_url' => env('APP_URL') . '/pembelian/invoice/' . $idOrder,
        //     'expired_time' => (time() + (24 * 60 * 60)), // 24 jam
        //     'signature' => hash_hmac('sha256', $api->tripay_merchant_code . $idOrder . $jumlah, $api->tripay_private_key)
        // ];

        // $curl = curl_init();

        // curl_setopt_array($curl, [
        //     CURLOPT_FRESH_CONNECT => true,
        //     CURLOPT_URL => 'https://tripay.co.id/api/transaction/create',
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_HEADER => false,
        //     CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $api->tripay_api],
        //     CURLOPT_FAILONERROR => false,
        //     CURLOPT_POST => true,
        //     CURLOPT_POSTFIELDS => http_build_query($data),
        //     CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
        // ]);

        // $response = json_decode(curl_exec($curl));
        // // dd($response);
        // $error = curl_error($curl);
        // $paymentNumber = '';
        // // if ($method == "QRISC" || $method == "QRISD" || $method == "QRIS" || $method == "QRISOP") {
        // //     $paymentNumber = $response->data->qr_url;
        // // } else if ($response->data->pay_code == null) {
        // //     $paymentNumber = $response->data->checkout_url;
        // // } else {
        // //     $paymentNumber = $response->data->pay_code;
        // // }


        // if ($response->success == true) {


        //     if (empty($response->data->pay_code)) {

        //         if (empty($response->data->pay_url)) {

        //             $paymentNumber = $response->data->qr_url;
        //         } else {

        //             $paymentNumber = $response->data->pay_url;
        //         }
        //     } else {

        //         $paymentNumber = $response->data->pay_code;
        //     }

        //     return array('success' => $response->success, 'amount' => $response->data->amount, 'no_pembayaran' => $paymentNumber, 'reference' => $response->data->reference);
        // } else {

        //     $err = strtolower($response->message);

        //     $msg = '';

        //     if (str_contains($err, 'minimum')) {

        //         $pch = explode('rp', $err);

        //         $msg = 'Minimum jumlah pembayaran untuk metode pembayaran ini adalah Rp ' . $pch[1] . ' ';
        //     } elseif (str_contains($err, 'maximum')) {

        //         $pch = explode('rp', $err);

        //         $msg = 'Maksimal jumlah pembayaran untuk metode pembayaran ini adalah Rp ' . $pch[1] . ' ';
        //     } else {

        //         $msg = 'Metode pembayaran ini sedang tidak dapat digunakan';
        //     }

        //     return array('success' => false, 'msg' => $msg);
        // }
    }

    public function fee($jumlah, $code)
    {
        $api = \DB::table('setting_webs')->where('id', 1)->first();

        $payload = [
            'code' => $code,
            'amount' => $jumlah
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_URL => 'https://tripay.co.id/api/merchant/fee-calculator?' . http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $api->tripay_api],
            CURLOPT_FAILONERROR => false
        ]);

        $response = json_decode(curl_exec($curl));
        $error = curl_error($curl);

        curl_close($curl);

        return $response->data['0']->total_fee->customer + $response->data['0']->total_fee->merchant;
    }

    public function channel()
    {
        $api = \DB::table('setting_webs')->where('id', 1)->first();

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_URL => 'https://tripay.co.id/api/merchant/payment-channel',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $api->tripay_api],
            CURLOPT_FAILONERROR => false
        ]);

        $response = json_decode(curl_exec($curl));
        $error = curl_error($curl);

        curl_close($curl);

        return $response;
    }

    public function detail($reference)
    {
        $api = \DB::table('setting_webs')->where('id', 1)->first();

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_URL => 'https://tripay.co.id/api/transaction/detail?reference=' . $reference,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $api->tripay_api],
            CURLOPT_FAILONERROR => false
        ]);

        $response = json_decode(curl_exec($curl));
        $error = curl_error($curl);

        curl_close($curl);

        return $response;
    }

    public function requestVirtualAccount($idOrder, $jumlah, $method, $dataUser, $nohp)
    {
        $api = \DB::table('setting_webs')->where('id', 1)->first();

        $data = [
            'bankCode' => self::BANK_CODE[$method],
            'singleUse' => true,
            'type' => 'ClosedAmount',
            'reference' => $idOrder,
            'amount' => $jumlah,
            'expiryMinutes' => 1440,
            'viewName' => env('APP_NAME'),
            'additionalInfo' => [
                'callback' => env('APP_URL') . '/callback'
            ]
        ];

        $headers = [
            'On-Key: ' . $api->cronos_key,
            'On-Token: ' . $api->cronos_token, 
            'On-Signature: ' . hash_hmac('sha512', $api->cronos_key.json_encode($data), $api->cronos_token),
            'Content-Type: application/json'
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_URL => 'https://api.cronosengine.com/api/virtual-account',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FAILONERROR => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
        ]);

        try {
            $response = json_decode(curl_exec($curl));
        } catch (\Throwable $th) {
            throw $th;
        }

        $paymentNumber = $response->responseData->virtualAccount->vaNumber;

        return [
            'success' => $response->responseCode == 200, 
            'amount' => $response->responseData->totalAmount, 
            'no_pembayaran' => $paymentNumber, 
            'reference' => $response->responseData->id
        ];
    }

    public function requestQris($idOrder, $jumlah, $method, $dataUser, $nohp)
    {
        $api = \DB::table('setting_webs')->where('id', 1)->first();

        $data = [
            'reference' => $idOrder,
            'amount' => $jumlah,
            'expiryMinutes' => 1440,
            'viewName' => env('APP_NAME'),
            'additionalInfo' => [
                'callback' => env('APP_URL') . '/callback'
            ]
        ];

        $headers = [
            'On-Key: ' . $api->cronos_key,
            'On-Token: ' . $api->cronos_token, 
            'On-Signature: ' . hash_hmac('sha512', $api->cronos_key.json_encode($data), $api->cronos_token),
            'Content-Type: application/json'
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_URL => 'https://api.cronosengine.com/api/qris',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FAILONERROR => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
        ]);

        try {
            $response = json_decode(curl_exec($curl));
        } catch (\Throwable $th) {
            throw $th;
        }

        $paymentNumber = $response->responseData->qris->content;
        
        return [
            'success' => $response->responseCode == 200, 
            'amount' => $response->responseData->totalAmount, 
            'no_pembayaran' => $paymentNumber, 
            'reference' => $response->responseData->id
        ];
    }
}
