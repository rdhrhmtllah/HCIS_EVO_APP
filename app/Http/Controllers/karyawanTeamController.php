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
use App\Models\User;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;


class karyawanTeamController extends Controller
{
    public function index(){
        $namaUser = Auth::user()->karyawan->Nama;

        return inertia("addKaryawanTeam", [
            'namaKaryawan' => $namaUser
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
                    select
                        top 1 yk.Nama
                    from
                        HRIS_Approval_Flow y,
                        Karyawan yk
                    where
                        a.Kode_Karyawan = y.Kode_Karyawan_Requester
                        and y.order_flow = 1
                        and y.Kode_Karyawan = yk.Kode_Karyawan
                    ),
                    'TIDAK ADA'
                ) as Approver1,
                ISNULL(
                    (
                    select
                        top 1 yk.Nama
                    from
                        HRIS_Approval_Flow y,
                        Karyawan yk
                    where
                        a.Kode_Karyawan = y.Kode_Karyawan_Requester
                        and y.order_flow = 2
                        and yk.Kode_Karyawan = y.Kode_Karyawan
                    ),
                    'TIDAK ADA'
                ) as Approver2,
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
}
