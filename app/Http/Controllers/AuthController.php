<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use RealRashid\SweetAlert\Facades\Alert;
use GuzzleHttp\Client;
use App\Models\LoginAttempt;
use Throwable;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function prosesLogin(Request $request)
    {
        // dd(env('SALT_FRONT').$request->input('password').env('SALT_BACK'));
        try{


            if (!$this->verifyCloudflareCaptcha($request)) {

            Session::flash('error', 'Tolong tunggu security check selesai!');
            return back()
                    ->withErrors(['captcha' => 'Please complete the security check'])
                    ->withInput($request->except('password'));
            }

            $userLogin = User::where('Username', $request->username)->first();
        if ($userLogin && Hash::check(env('SALT_FRONT').$request->password.env('SALT_BACK'), $userLogin->Password)) {
                Auth::login($userLogin);
                $request->session()->regenerate();
                $user = Auth::user();
                // dd($user);
                // dd($user);
                // Check user role and redirect accordingly
                switch ($user->Role) {
                    case 'superuser':
                        User::where("Username", $user->Username)->update(['angka_login' => DB::raw('COALESCE(angka_login, 0) + 1')]);
                        Session::flash('success', 'Welcome back, Admin!');
                        return redirect()->intended('/uDash');
                    case 'user':
                        User::where("Username", $user->Username)->update(['angka_login' => DB::raw('COALESCE(angka_login, 0) + 1')]);
                        Session::flash('success', 'Welcome back, Manager!');
                        return redirect()->intended('/uDash');
                        // return abort(403, 'Akses ditolak!');
                    default:
                        Auth::logout();
                        Session::flash('error', 'You do not have a valid role assigned.');
                        return redirect()->route('login');
                }
            }

            Session::flash('error', 'Username atau password salah!');
            return redirect()->back()->withInput();
        }catch (\Throwable $e) {
            Log::channel('loginLog')->error('Terjadi kesalahan Login : '. auth()->user()->Kode_Users . $e->getMessage());
            Session::flash('error', 'Terjadi Error saat login!');
            return back();
        }
    }

    public function loginRe()
    {
        return redirect('/login');
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
            if (!Hash::check(env('SALT_FRONT').$request->oldPass.env('SALT_BACK'), $request->user()->Password)) {
                DB::rollBack();
                return back()->with('pesan', 'Password Lama Salah!!');
            }

            $request->user()->update([
                'Password' => Hash::make(env('SALT_FRONT').$request->newPass.env('SALT_BACK'))
            ]);

            Alert::Success('Success', 'Password Berhasil Diupdate');
            DB::commit();
            return back();
        } catch (Throwable $error) {
            DB::rollBack();
            Log::channel('loginLog')->info("error ubah password resetPass", [
                'pesan' => $error->getMessage()
            ]);
            Alert::Error('Error', 'Terjadi kesalahan');
            return back();
        }
    }

    public function changeData(Request $request){
        // dd($request->all());
        $validation = $request->validate([
            'HP' => [
                'required',
                'min:12', // Minimal 12 digit (628 + 9 digit)
                'max:14', // Maksimal 14 digit (misal 628xxxxxxxxxx) - sesuaikan jika ada kemungkinan lebih panjang
                'regex:/^628[0-9]{8,11}$/' // Dimulai dengan 628, lalu 8 hingga 11 digit angka
            ]
        ]);
        DB::beginTransaction();
        try {
            $user = Karyawan::where('UserID_Web', $request->Id_Users)->first();

            // $isNamaChange = $validation['Nama'] !== $user->Nama;
            $isNo_HPChange = $validation['HP'] !== $user->No_Hp;

            if(!$isNo_HPChange){
                Alert::Error('error', 'Tidak ada perubahan pada data anda!');
                DB::rollBack();
                return back();
            }else{
                Karyawan::where('UserID_Web', $request->Id_Users)->update($validation);
                Alert::Success('Success', 'Case Berhasil Disimpan');
                DB::commit();
                return back();
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::channel('loginLog')->info('changeData Error',[
                'pesan' => $th->getMessage()
            ]);
            Alert::Error('Error', 'Terjadi Kesalahan!');
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
