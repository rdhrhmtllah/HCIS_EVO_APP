<?php

namespace App\Http\Controllers;

use Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;
use GuzzleHttp\Client;
use App\Models\LoginAttempt;
use Throwable;

class AuthController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function prosesLogin(Request $request)
    {

        // if (!$this->verifyCloudflareCaptcha($request)) {
            // Session::flash('error', 'Username atau password salah!');
        //
        // return back()
        //         ->withErrors(['captcha' => 'Please complete the security check'])
        //         ->withInput($request->except('password'));
        // }

        $user = User::where('Username', $request->username)->first();
        if($user && Hash::check(env('SALT_FRONT').$request->password.env('SALT_BACK'), $user->Password)){
            $request->session()->regenerate();

            // Get authenticated user
            $user = Auth::user();

            // Check user role and redirect accordingly
            switch ($user->Role) {
                case 'superuser':
                    Session::flash('success', 'Welcome back, Admin!');
                    return redirect()->intended('/overtime');
                case 'user':
                    Session::flash('success', 'Welcome back, Manager!');
                    return redirect()->intended('/resetPassword');
                    // return abort(403, 'Akses ditolak!');
                default:
                    Auth::logout();
                    Session::flash('error', 'You do not have a valid role assigned.');
                    return redirect()->route('login');
            }
        }

        Session::flash('error', 'Username atau password salah!');
        return redirect()->back()->withInput();
    }

      private function verifyCloudflareCaptcha(Request $request): bool
    {
        $captchaResponse = $request->input('cf-turnstile-response');

        if (!$captchaResponse) {
            return false;
        }

        try {
            $client = new Client();
            $response = $client->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'form_params' => [
                    'secret' => env('CLOUDFLARE_TURNSTILE_SECRET'),
                    'response' => $captchaResponse,
                    'remoteip' => $request->ip()
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            return $body['success'] ?? false;
        } catch (\Exception $e) {
            \Log::error('Cloudflare Turnstile Verification Failed: ' . $e->getMessage());
            return false;
        }
    }

    public function reset()
    {
        $title = 'Reset Password';


        return view('resetPass', ['title' => $title]);
    }

    public function update(Request $request)
    {
        $validation = $request->validate([
            'oldPass' => 'required',
            'newPass' => 'required|min:6',
            'VerifyPass' => 'required|min:6|same:newPass'
        ]);
        try {
            DB::beginTransaction();

            if (!Hash::check(env('SALT_FRONT').$request->oldPass.env('SALT_BACK '), $request->user()->Password)) {
                DB::rollBack();
                return back()->with('pesan', 'Password Lama Salah!!');
            }

            $request->user()->update([
                'Password' => Hash::make(env('SALT_FRONT').$request->newPass.env('SALT_BACK '))
            ]);

            Alert::Success('Success', 'Case Berhasil Disimpan');
            DB::commit();
            return back();
        } catch (Throwable $error) {
            DB::rollBack();
            Log::info("error ubah password", [
                'pesan' => $error->getMessage()
            ]);
            Alert::Error('Error', 'Terjadi kesalahan');
            return back();
        }
    }

    public function changeData(Request $request){
        // dd($request->all());
        $validation = $request->validate([
            'Nama' => 'required',
            'No_Hp' => [
                'required',
                'min:12',
                'regex:/^(?:\+62|62|0)8[1-9][0-9]{6,9}$/'
            ]
        ]);
        DB::beginTransaction();
        try {
            $user = User::findOrFail($request->Id_Users);

            $isNamaChange = $validation['Nama'] !== $user->Nama;
            $isNo_HPChange = $validation['No_Hp'] !== $user->No_Hp;

            if(!$isNamaChange && !$isNo_HPChange){
                Alert::Error('error', 'Tidak ada perubahan pada data anda!');
                DB::rollBack();
                return back();
            }else{
                User::where('Id_Users', $request->Id_Users)->update($validation);
                Alert::Success('Success', 'Case Berhasil Disimpan');
                DB::commit();
                return back();
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::info('changeData Erro',[
                'pesan' => $th->getMessage()
            ]);
            Alert::Error('Error', 'Terjadi Kesalahan!'. $th->getMessage());
            return back();
        }
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
