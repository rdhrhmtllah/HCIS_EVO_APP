<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Whatsapp;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;


class karyawanTeamController extends Controller
{
    public function index(){
        $namaUser = Auth::user()->karyawan->Nama;


        // dd($PageAccess);


        return inertia("addKaryawanTeam", [
            'namaKaryawan' => $namaUser,

        ]);
    }

    public function getDataAll(){
         $allKaryawan = DB::table('Karyawan as a')
            ->leftJoin("View_Divisi_Sub_Divisi as b", 'a.ID_Divisi_Sub_Divisi', '=', 'b.ID_DIVISI_SUB_DIVISI')
            ->leftJoin("KPI_Users as c", "c.Id_Users", '=', 'a.UserID_Web')
            ->leftJoin("View_Golongan_Sub_Golongan_Level_Jabatan as d", 'd.ID_Level_Jabatan', '=', 'a.ID_Level_Jabatan')
            // ->leftJoin("HRIS_Approval_Flow as d",  "a.Kode_Karyawan", "=", "d.Kode_Karyawan_Requester")
            ->select(
                "a.Nama",
                "a.Kode_Karyawan",
                "b.nama_divisi",
                "a.HP",
                "c.Username",
                "a.UserID_Web",
                "d.nama_level as nama_jabatan"
            )
            ->get();



        $teamData = DB::table('HRIS_Karyawan_Team')
        ->select("Kode_Karyawan_Team as LeaderTeam", "Kode_Karyawan")
        ->where("Kode_Karyawan_Team", '<>', 'RIDHO RAHMAT')
        ->get()
        ->groupBy('LeaderTeam');

        $karyawanTeam = $teamData->map(function($items, $leader) {
            return [
                'LeaderTeam' => $leader,
                'Team'       => $items->pluck('Kode_Karyawan')->values()->all()
            ];
        })->values();


            $Flow = DB::table("HRIS_Approval_Flow")
            ->select("Kode_Karyawan", "Kode_Karyawan_Requester as Requester", "order_flow")
            ->whereNull("Status")
            ->orderBy("order_flow")
            ->get()
            ->groupBy("Requester");

        $approvalFlow = $Flow->map(function($items, $requester) {
            return [
                'Requester' => $requester,
                'Approver' => $items->map(function($item) {
                    return [
                        'Kode_Karyawan' => $item->Kode_Karyawan,
                        'order_flow' => $item->order_flow
                    ];
                })->values()->all()
            ];
        })->values();


        $page = DB::table("HRIS_Page_Access")
                        ->select("Jenis_page" , "UserID_Web")
                        ->get()
                        ->groupBy("UserID_Web");
        $PageAccess = $page->map(function($items, $UserID_Web){
            return [
                'UserID_Web' => $UserID_Web,
                'Jenis_page' => $items->pluck("Jenis_page")->values()->all()
            ];
        })->values();

        return response()->json([
            'allKaryawan' => $allKaryawan,
            'karyawanTeam' => $karyawanTeam,
            'ApprovalFlow' => $approvalFlow,
            'PageAccess' => $PageAccess
        ]);
    }

    public function getData(Request $request){
        try{

            $validated = $request->validate([
                'search' => ['nullable', 'string', 'max:100'],
                'page' => ['nullable', 'integer', 'min:1'],
            ]);

            $search = strip_tags($validated['search'] ?? '');
            $perPage = 10;
            $page = $validated['page'] ?? 1;
            $offset = ($page - 1) * $perPage;
            $searchColoms = [
                'a.Nama',
                'b.nama_divisi',
                'c.nama_jabatan'
            ];

            $queryCount = "
                select
                count(a.Kode_Karyawan) as total
                from
                Karyawan a,
                View_Divisi_Sub_Divisi b,
                View_Golongan_Sub_Golongan_Level_Jabatan c
                where
                a.ID_Divisi_Sub_Divisi = b.ID_DIVISI_SUB_DIVISI
                and a.ID_Level_Jabatan = c.ID_Level_Jabatan
                and 1=1

            ";
            $queryData = "
                select
                a.Nama as namaUser,
                a.Kode_Karyawan,
                b.nama_divisi,
                c.nama_jabatan,
                 ISNULL(
                    (
                        select STRING_AGG(yk.Nama, ' | ')
                        from HRIS_Approval_Flow y
                        join Karyawan yk on y.Kode_Karyawan = yk.Kode_Karyawan
                        where a.Kode_Karyawan = y.Kode_Karyawan_Requester
                        and y.Status is null
                    ),
                    'TIDAK ADA'
                ) as Approvers,
                ISNULL(
                    (
                    select
                        count(Kode_Karyawan)
                    from
                        HRIS_Karyawan_Team z
                    where
                        a.Kode_Karyawan = z.Kode_Karyawan_Team
                    group by
                        Kode_Karyawan_Team
                    ),
                    0
                ) as Team,
                ISNULL((a.UserID_Web), 0) as UserID_Web
                from
                Karyawan a,
                View_Divisi_Sub_Divisi b,
                View_Golongan_Sub_Golongan_Level_Jabatan c
                where
                a.ID_Divisi_Sub_Divisi = b.ID_DIVISI_SUB_DIVISI
                and a.ID_Level_Jabatan = c.ID_Level_Jabatan
                and 1=1
            ";


            $params = [];
            if($search){
                $searchParam = '%' . $search .'%';
                $likeParts = [];
                foreach($searchColoms as $col){
                    $likeParts[]= "$col Like ?";
                    $params[] = $searchParam;
                }

                $likeQuery = implode(' OR ', $likeParts);
                $queryCount .= " AND ($likeQuery) ";
                $queryData .= " AND ($likeQuery) ";

            }

            $total = DB::selectOne($queryCount, $params)->total;

            $queryData .= " ORDER BY Team desc OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
            $params[]=$offset;
            $params[]=$perPage;



            $result = DB::select($queryData, $params);

            $final_page = new LengthAwarePaginator(
                    collect($result),
                    $total,
                    $perPage,
                    $page,
                    [
                        'path' => request()->url(),
                        'query' => request()->query(),
                    ]
            );


            // dd($result);

            return response()->json([
                'status' =>200,
                'data' => $final_page
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('KaryawanTeamLog')->error('Input Tidak Valid'. $e->errors());
            return response()->json([
                'message' => 'Input tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::channel('KaryawanTeamLog')->error('Gagal Mengambil data Karyawan Team '. $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data',
                // 'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function simpanUser(Request $request){
        // dd($request->all());
          try{
            DB::beginTransaction();

            $Kode_Karyawan = $request->Kode_Karyawan;
            $Karyawan = Karyawan::where("Kode_Karyawan", $Kode_Karyawan)->first();

            $HPKaryawan = $Karyawan->HP;

            if(!$HPKaryawan || $HPKaryawan == ' ' || $HPKaryawan == '-'){
                Log::channel('CreateUserLog')->error("Tidak ada NO HP untuk {$Karyawan->Nama}, silahkan ubah NO HP di HCIS desktop");
                DB::rollback();
                return response()->json([
                    'status' =>500,
                    'message' => "Tidak ada NO HP untuk {$Karyawan->Nama}, silahkan ubah NO HP di HCIS desktop!"
                ], 500);
            }

            $username = $request->Username;

            $divisi = $Karyawan->division->ID_Divisi;
            $level = $Karyawan->level->ID_Level;

            // dd($Karyawan->UserID_Web );

            if($username && (!$Karyawan->UserID_Web || $Karyawan->UserID_Web == ' ' || $Karyawan->UserID_Web == '-')){
                $cleanPasswordPart = $username;
                $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $saltFront = env('SALT_FRONT');
                $saltBack = env('SALT_BACK');
                $rawPassword = $cleanPasswordPart . '_' . $randomNumber;
                $passwordWithSalt = $saltFront . $rawPassword . $saltBack;
                $encryptedPassword = Hash::make($passwordWithSalt);

                $createUser = User::create([
                    'Username' => $username,
                    'Password' => $encryptedPassword,
                    'Email' => $Karyawan->Email,
                    'Role' => 'user',
                    'Flag_Active' => 'Y',
                    'Kode_Users' => $Karyawan->Kode_Karyawan,
                    'Address' => $Karyawan->Alamat,
                    'Division_Id' => $divisi,
                    'Level_Id' => $level,
                    'Nama' => $Karyawan->Nama,
                    'No_Hp' => $Karyawan->HP
                ]);

                if($createUser){

                    $updateKaryawan = Karyawan::where("Kode_Karyawan", $Kode_Karyawan)
                    ->update(["UserID_Web" => $createUser->Id_Users]);

                    if($updateKaryawan < 1){
                        Log::channel('CreateUserLog')->error("Tidak ada UserID_Web");
                        DB::rollback();
                        return response()->json([
                            'status' =>500,
                            'message' => 'Tidak dapat mendapat id web karyawan!'
                        ], 500);
                    }

                    // Notifikasi WA
                    $pesan = [
                        "messaging_product" => "whatsapp",
                        "to" => $HPKaryawan,
                        "type" => "template",
                        "template" => [
                            "name" => "notif_web_hc_new",
                            "language" => ["code" => "en", "policy" => "deterministic"],
                            "components" => [
                                [
                                    "type" => "body",
                                    "parameters" => [
                                        ["type" => "text", "text" => $username],
                                        ["type" => "text", "text" => $Karyawan->Nama],
                                        ["type" => "text", "text" => $rawPassword],
                                    ]
                                ]
                            ]
                        ]
                    ];
                    $response = Whatsapp::send_message($pesan);
                    Log::channel('WaCreateUserLog')->warning('WA Response', ['pesan' => $response]);


                }

            }

            $UserID_Web = Karyawan::where("Kode_Karyawan", $Kode_Karyawan)->select("UserID_Web")->first()->UserID_Web;

            if(!$UserID_Web){
                Log::channel('CreateUserLog')->error("Tidak ada UserID_Web");
                DB::rollback();
                return response()->json([
                    'status' =>500,
                    'message' => 'Tidak dapat mendapat id web karyawan!'
                ], 500);
            }

            $Pages = $request->Pages;


            $adaUser = DB::table('HRIS_Page_Access')
            ->where("UserID_Web", $UserID_Web)
            ->exists();

            if($adaUser){
                $da =DB::table('HRIS_Page_Access')
                ->where("UserID_Web", $UserID_Web)
                ->delete();
            }

            foreach($Pages as $Page){
                DB::table('HRIS_Page_Access')
                ->insert([
                    'Kode_Perusahaan' => '001',
                    'ID_Level' => $level,
                    'ID_Divisi' => $divisi,
                    'Jenis_page' => $Page,
                    'UserID_Web' => $UserID_Web,
                ]);

            }
            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil menyimpan User'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            Log::channel('CreateUserLog')->error('Input Tidak Valid'. $e->errors());
            return response()->json([
                'message' => 'Input tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollback();
            Log::channel('CreateUserLog')->error('Gagal menyimpan data Karyawan '. $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data',
                // 'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function simpanApproval(Request $request){


        try{
            DB::beginTransaction();
            $requester = collect($request->Requester);
            $approver = $request->Approver;



            $adaApprover = DB::table('HRIS_Approval_Flow')
            ->where("Kode_Karyawan_Requester", $requester['Kode_Karyawan'])
            ->whereNull("Status")
            ->exists();

            if ($adaApprover) {
                $e =  DB::table('HRIS_Approval_Request as a')
                    ->join('HRIS_Approval_Flow as b', 'a.Flow_Id', '=', 'b.id')
                    ->whereNull('a.Flag_Approval')
                    ->where("a.Kode_Karyawan", $requester['Kode_Karyawan'])

                    ->whereNotExists(function ($sub) use($requester) {
                        $sub->select(DB::raw(1))
                            ->from('HRIS_Approval_Request as prev')
                            ->join('HRIS_Approval_Flow as prev_flow', 'prev.Flow_Id', '=', 'prev_flow.id')
                            ->whereColumn('prev.No_Transaksi', 'a.No_Transaksi')
                            ->where("prev.Kode_Karyawan", $requester['Kode_Karyawan'])

                            ->whereRaw('prev_flow.order_flow < b.order_flow')
                            ->where(function ($q)  {
                                $q->whereNull('prev.Flag_Approval')
                                ->orWhere('prev.Flag_Approval', '!=', 'Y');
                            });
                    })
                    ->whereIn('a.id', function ($query)  use($requester) {
                        $query->selectRaw('MIN(t1.id)')
                            ->from('HRIS_Approval_Request as t1')
                            ->where("t1.Kode_Karyawan", $requester['Kode_Karyawan'])
                            ->join('HRIS_Approval_Flow as t2', 't1.Flow_Id', '=', 't2.id')
                            ->whereNull('t1.Flag_Approval')
                            ->groupBy('t1.No_Transaksi');
                    })

                    // Cek apakah di 3 tabel transaksi ada yang statusnya 'Y'
                    ->whereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from(DB::raw("
                                (
                                    SELECT a.No_Transaksi FROM Transaksi_Terlambat_Pulang_Cepat a JOIN Transaksi_Terlambat_Pulang_Cepat_Detail b ON a.No_Transaksi = b.No_Transaksi WHERE (a.Status = 'Y' OR b.Flag_Approval is not null)
                                    UNION ALL
                                    SELECT a.No_Transaksi FROM Transaksi_Sakit_Izin a JOIN Transaksi_Sakit_Izin_Detail b ON a.No_Transaksi = b.No_Transaksi WHERE (a.Status = 'Y' OR b.Flag_Approval is not null)
                                    UNION ALL
                                    SELECT a.No_Transaksi FROM Transaksi_Cuti a JOIN Transaksi_Cuti_Detail b ON a.No_Transaksi = b.No_Transaksi WHERE (a.Status = 'Y' OR b.Flag_Approval is not null)
                                ) as t_check
                            "))
                            ->whereColumn('t_check.No_Transaksi', 'a.No_Transaksi');
                    })

                    ->select('a.*', 'b.order_flow')
                    ->orderBy('a.No_Transaksi')
                    ->get();
                // dd(count($e));

                if(count($e) != 0){
                    DB::rollback();
                    // dd('tidak nol ni');

                    Log::channel('KaryawanTeamLog')->error("Gagal mengupdate approver karyawan karena karyawan masih ada pengajuan yang belum di acc ");
                    return response()->json([
                        'message' => 'Gagal mengupdate approver karyawan karena karyawan masih ada pengajuan yang belum di acc ',
                        // 'error' => $e->getMessage(),
                    ], 500);
                }
                // dd('nol ni');

                DB::table('HRIS_Approval_Flow')
                    ->where("Kode_Karyawan_Requester", $requester['Kode_Karyawan'])
                    ->whereNull("Status") // ini penting kalau cuma mau update yang null
                    ->update(['Status' => 'Y']);
            }

            foreach($approver as $item){
                $approverSelect = Karyawan::where("Kode_Karyawan", $item['Kode_Karyawan'])
                            ->first();
                $divisi = $approverSelect->division->ID_Divisi;
                $level = $approverSelect->level->ID_Level;
                DB::table('HRIS_Approval_Flow')
                ->insert([
                    'Kode_Karyawan' => $item['Kode_Karyawan'],
                    'Division_Id' => $divisi,
                    'level_Id' => $level,
                    'order_flow' => $item['order_flow'],
                    'Kode_Karyawan_Requester' => $requester['Kode_Karyawan']
                ]);

            }
            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Berhasil menyimpan approver'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            Log::channel('KaryawanTeamLog')->error('Input Tidak Valid'. $e->errors());
            return response()->json([
                'message' => 'Input tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollback();
            Log::channel('KaryawanTeamLog')->error('Gagal Mengambil data Karyawan Team '. $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data',
                // 'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function simpanTeam(Request $request)
    {
        try {
            DB::beginTransaction();

            $Kode_Karyawan   = $request->Kode_Karyawan;
            $newMembers      = $request->newMembers ?? [];
            $removedMembers  = $request->removedMembers ?? [];

            // ========== HAPUS MEMBER ==========
            if (!empty($removedMembers)) {
                $removeCodes = array_column($removedMembers, 'Kode_Karyawan');

                $deleted = DB::table('HRIS_Karyawan_Team')
                    ->where('Kode_Karyawan_Team', $Kode_Karyawan)
                    ->whereIn('Kode_Karyawan', $removeCodes)
                    ->delete();

                // Cek kalau jumlah delete tidak sama dengan jumlah yg diminta
                if ($deleted < count($removeCodes)) {
                    $notDeleted = array_filter($removedMembers, function ($emp) use ($Kode_Karyawan) {
                        return DB::table('HRIS_Karyawan_Team')
                            ->where('Kode_Karyawan_Team', $Kode_Karyawan)
                            ->where('Kode_Karyawan', $emp['Kode_Karyawan'])
                            ->exists();
                    });

                    foreach ($notDeleted as $emp) {
                        Log::channel('addTeam')->error("Terjadi error saat menghapus {$emp['Nama']} dari team {$Kode_Karyawan}");
                    }
                }
            }

            // ========== TAMBAH MEMBER ==========
            if (!empty($newMembers)) {
                // Ambil kode yang sudah ada untuk menghindari duplikat
                $existingCodes = DB::table('HRIS_Karyawan_Team')
                    ->where('Kode_Karyawan_Team', $Kode_Karyawan)
                    ->pluck('Kode_Karyawan')
                    ->toArray();

                $insertData = [];
                foreach ($newMembers as $emp) {
                    if (!in_array($emp['Kode_Karyawan'], $existingCodes)) {
                        $insertData[] = [
                            'Kode_Karyawan_Team' => $Kode_Karyawan,
                            'Kode_Karyawan'      => $emp['Kode_Karyawan']
                        ];
                    }
                }

                if (!empty($insertData)) {
                    $inserted = DB::table('HRIS_Karyawan_Team')->insert($insertData);

                    if (!$inserted) {
                        foreach ($insertData as $emp) {
                            Log::channel('addTeam')->error("Terjadi error saat menambahkan {$emp['Kode_Karyawan']} ke team {$Kode_Karyawan}");
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Berhasil menyimpan teams'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::channel('addTeam')->error('Input Tidak Valid: ' . json_encode($e->errors()));
            return response()->json([
                'message' => 'Input tidak valid',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('addTeam')->error('Gagal menyimpan data Karyawan Team: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data'
            ], 500);
        }
    }


    public function kirimUlangUser(Request $request){
        // dd($request->all());
         try {
            DB::beginTransaction();

            $Kode_Karyawan   = $request->Kode_Karyawan;
            $karyawan = Karyawan::where("Kode_Karyawan", $Kode_Karyawan)->first();

            $user = User::where("Kode_Users", $Kode_Karyawan)->first();
            if(!$user){
                Log::channel('CreateUserLog')->error("Tidak ada user {$Kode_Karyawan} untuk diwa ulang");
                DB::rollback();
                return response()->json([
                    'status' =>500,
                    'message' => "Tidak ada user {$Kode_Karyawan} untuk diwa ulang"
                ], 500);
            }

            $cleanPasswordPart = $user->Username;
            $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $saltFront = env('SALT_FRONT');
            $saltBack = env('SALT_BACK');
            $rawPassword = $cleanPasswordPart . '_' . $randomNumber;
            $passwordWithSalt = $saltFront . $rawPassword . $saltBack;
            $encryptedPassword = Hash::make($passwordWithSalt);

            $updateUser = User::where("Kode_Users", $Kode_Karyawan)
                        ->update([
                            'Password' => $encryptedPassword
                        ]);




                    if($updateUser < 1){
                        Log::channel('CreateUserLog')->error("Tidak ada update user");
                        DB::rollback();
                        return response()->json([
                            'status' =>500,
                            'message' => 'Tidak ada update user'
                        ], 500);
                    }

                    // Notifikasi WA
                    $pesan = [
                        "messaging_product" => "whatsapp",
                        "to" => $karyawan->HP,
                        "type" => "template",
                        "template" => [
                            "name" => "notif_web_hc_new",
                            "language" => ["code" => "en", "policy" => "deterministic"],
                            "components" => [
                                [
                                    "type" => "body",
                                    "parameters" => [
                                        ["type" => "text", "text" => $cleanPasswordPart],
                                        ["type" => "text", "text" => $karyawan->Nama],
                                        ["type" => "text", "text" => $rawPassword],
                                    ]
                                ]
                            ]
                        ]
                    ];
                    $response = Whatsapp::send_message($pesan);
                    Log::channel('WaCreateUserLog')->warning('WA Response', ['pesan' => $response]);




            DB::commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Berhasil menyimpan teams'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::channel('CreateUserLog')->error('Input Tidak Valid: ' . json_encode($e->errors()));
            return response()->json([
                'message' => 'Input tidak valid',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('CreateUserLog')->error('Gagal menyimpan data Karyawan Team: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data'
            ], 500);
        }
    }

}
