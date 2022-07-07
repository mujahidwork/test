<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\User;

class SlotGameController extends Controller
{


    public function auth()
    {
        $user = $this->authUser();

        if ($user) {
            return response([
                'success' =>  true,
                'verified' => $user->verified,
                'token' =>  $user->createToken($user->name)->plainTextToken
            ]);
        }

        return response([
            'success' =>  false,
            'message' => 'User Not Found'
        ]);
    }



    public function startGame()
    {

        $user = auth()->user();
        $randomNumber = mt_rand(1, 2);
        $userGame = new Game();
        $userGame->user_id = $user->id;
        $userGame->result = $randomNumber;
        $userGame->win =  $this->isWinner($randomNumber);
        $userGame->save();

        if ($userGame->win) {
            $user->wins = $user->wins + 1;
        }
        $user->games =  $user->games  + 1;
        $user->save();

        return response([
            'success' =>  true,
            'result' => $userGame->win ? 'win' : 'lose',
        ]);
    }


    public function gamePermissions()
    {
        $user = auth()->user();
        return response([
            'success' =>  true,
            'no_of_wins' =>   $user->wins,
            'no_of_games' =>     $user->games,
            'allowed' =>  $user->games == 0 ?  true : false,
            'no_of_clicks' =>  $user->wins > 0 ? $user->wins * 50 : 50,
        ]);
    }


    public function   authUser()
    {
        $user =  auth()->user();
        if (!$user) {
            $user = $this->registerUser(request()->input('game_data'));
        }
        return $user;
    }

    public function leadersBorad()
    {

        $user = $this->authUser();
        return response([
            'success' =>  true,
            'leaders' =>  User::where('wins', '>', 0)->orderBy('wins', 'desc')->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'points' => $user->wins * 10
                ];
            }),
            'my_points' =>    $user->wins * 10,
            'my_name' =>   $user->name,
            'my_id' =>     $user->id
        ]);
    }





    public function isWinner($number)
    {
        if ($number == 2) {
            return true;
        }
        return false;
    }




    public function registerUser($encryptGameDate)
    {
        $decrypted = $this->encrypt_decrypt($encryptGameDate, 'decrypt');
        
        if ($decrypted) {
            $gameDate = explode(',', $decrypted);
            $userId = $gameDate[0] ?? 0;
            $userName = $gameDate[1] ?? '';
            $userVerified = $gameDate[2] ?? 0;

            if (!$user = User::where('gathern_id', $userId)->first()) {
                $user = new User();
                $user->gathern_id  =  $userId;
                $user->name = $userName;
                $user->verified =  $userVerified;
                $user->save();
            }

            return $user;
        }
        return null;
    }



    public function encrypt_decrypt($string, $action = 'encrypt')
    {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'This is my secret key';
        $secret_iv = 'This is my secret iv';
        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
}
