<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Storage;
use App\Models\KpiUser;
use Illuminate\Support\Facades\Log;
use App\Helpers\Whatsapp;


class Users extends Seeder
{
    public function run()
    {
        $usernames = [
            'MUSLIYA'
        ];

        // Kode Karyawan yang sesuai dengan urutan usernames.
        // Penting: Pastikan urutan dan jumlah elemen di sini cocok dengan $usernames
        $karyawanCodes = [
            'MUSLIYA WIJAYA'
        ];

        // Ambil data karyawan berdasarkan $karyawanCodes dan urutkan hasilnya agar sesuai dengan urutan $karyawanCodes.
        // Ini adalah langkah KRITIS untuk memastikan pencocokan 1-ke-1.
        $karyawanData = Karyawan::where('Tanggal_Resign', null)
                                ->whereIn('Kode_Karyawan', $karyawanCodes)
                                ->get()
                                ->sortBy(function ($karyawan) use ($karyawanCodes) {
                                    return array_search($karyawan->Kode_Karyawan, $karyawanCodes);
                                })
                                ->values(); // Reset keys setelah sort

        // Memastikan jumlah usernames dan karyawanData sesuai
        if (count($usernames) !== count($karyawanData)) {
            $this->command->error("Jumlah username (" . count($usernames) . ") dan data karyawan (" . count($karyawanData) . ") tidak sama. Proses dihentikan.");
            return;
        }

        $saltFront = env('SALT_FRONT');
        $saltBack = env('SALT_BACK');

        $passwordList = [];

        // Loop tunggal untuk mencocokkan berdasarkan indeks
        for ($i = 0; $i < count($usernames); $i++) {
            $username = $usernames[$i];
            $karyawan = $karyawanData[$i];

            $cleanUsername = $this->cleanName($username); // Panggil fungsi cleanName
            $cleanPasswordPart = $this->cleanName($username); // Atau bisa juga dari $karyawan->Nama jika diinginkan

            // Membuat angka random 5 karakter
            $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);

            // Membuat password berdasarkan Kode_Karyawan (yang sudah di-clean) dan angka random
            $rawPassword = $cleanPasswordPart . '_' . $randomNumber;

            // Menambahkan salt
            $passwordWithSalt = $saltFront . $rawPassword . $saltBack;

            // Mengenkripsi password menggunakan bcrypt
            $encryptedPassword = Hash::make($passwordWithSalt);

            $divisionId = $karyawan->division ? $karyawan->division->ID_Divisi : null;
            $levelId = $karyawan->level ? $karyawan->level->ID_Level : null;

            // --- Pengecekan Duplikasi Username yang Sudah di-clean ---
            // Gunakan $cleanUsername atau $karyawan->Kode_Karyawan untuk pengecekan, tergantung kebutuhan
            $usernameExists = DB::table('KPI_Users')
                                ->where('Username', $cleanUsername) // Cek berdasarkan Username yang sudah di-clean
                                ->orWhere('Kode_Users', $karyawan->Kode_Karyawan) // Atau berdasarkan Kode_Karyawan
                                ->exists();

            if ($usernameExists) {
                $this->command->warn("Skipping user {$karyawan->Kode_Karyawan} (Nama: {$karyawan->Nama}, Cleaned Username: {$cleanUsername}) because a similar Username or Kode_Users already exists in KPI_Users table.");
                continue; // Lanjutkan ke iterasi berikutnya
            }
            // --- Pengecekan Duplikasi Selesai ---

            if ($divisionId !== null && $levelId !== null) {
                DB::transaction(function () use ($karyawan, $encryptedPassword, $divisionId, $levelId, $rawPassword, $cleanUsername, &$passwordList) {
                    try {
                        $kpiUserId = DB::table('KPI_Users')->insertGetId([
                            'Username' => $cleanUsername,
                            'Password' => $encryptedPassword,
                            'Email' => $karyawan->Email,
                            'Role' => 'user',
                            'Flag_Active' => 'Y',
                            'Kode_Users' => $karyawan->Kode_Karyawan,
                            'Address' => $karyawan->Alamat,
                            'Division_Id' => $divisionId,
                            'Level_Id' => $levelId,
                            'Nama' => $karyawan->Nama,
                            'No_Hp' => $karyawan->HP,
                        ]);

                        $karyawan->UserID_Web = $kpiUserId;
                        $karyawan->save();

                        // Tambahkan username dan password ke daftar
                        $passwordList[] = [
                            'Username' => $cleanUsername,
                            'Nama' => $karyawan->Nama,
                            'Raw_Password' => $rawPassword,
                        ];

                        DB::table('HRIS_Page_Access')->Insert([
                                'Kode_Perusahaan' => '001',
                                'ID_Level' => $levelId,
                                'ID_Divisi' => $divisionId,
                                'Jenis_Page' => 'OvertimeManagement',
                                'UserID_Web' => $kpiUserId
                            ]);



                        // if($karyawan->ID_Perusahaan == 'PT EVO MANUFACTURING INDONESIA') //<---- If EMI
                        // {
                        //     DB::table('HRIS_Page_Access')->Insert([
                        //         'Kode_Perusahaan' => '001',
                        //         'ID_Level' => $levelId,
                        //         'ID_Divisi' => $divisionId,
                        //         'Jenis_Page' => 'OvertimeManagement',
                        //         'UserID_Web' => $kpiUserId
                        //     ]);
                        //     DB::table('HRIS_Page_Access')->Insert([
                        //         'Kode_Perusahaan' => '001',
                        //         'ID_Level' => $levelId,
                        //         'ID_Divisi' => $divisionId,
                        //         'Jenis_Page' => 'ShiftManagement',
                        //         'UserID_Web' => $kpiUserId
                        //     ]);
                        // }else //<---- If ENB & GMN
                        // {
                        //      DB::table('HRIS_Page_Access')->Insert([
                        //         'Kode_Perusahaan' => '001',
                        //         'ID_Level' => $levelId,
                        //         'ID_Divisi' => $divisionId,
                        //         'Jenis_Page' => 'OvertimeManagement',
                        //         'UserID_Web' => $kpiUserId
                        //     ]);
                        // }

                     $pesan = [ "messaging_product" => "whatsapp",
                                        "to" => $karyawan->HP,
                                        "type" => "template",
                                        "template" => [
                                            "name" => "notif_web_hc_new",
                                            "language" => [
                                                "code" => "en",
                                                "policy" => "deterministic"
                                            ],
                                            "components" => [
                                                [
                                                    "type" => "body",
                                                    "parameters" => [
                                                        [
                                                            "type" => "text",
                                                            "text" => $cleanUsername
                                                        ],
                                                        [
                                                            "type" => "text",
                                                            "text" => $karyawan->Nama
                                                        ],
                                                        [
                                                            "type" => "text",
                                                            "text" => $rawPassword
                                                        ],
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ];

                            $response = Whatsapp::send_message($pesan);

                            // Log the WhatsApp API response for debugging or monitoring
                            Log::channel('whatsapp_error')->warning('Pesan Error', [
                                "pesan" => $response
                            ]);




                        $this->command->info("User {$karyawan->Kode_Karyawan} (Nama: {$karyawan->Nama}, Cleaned Username: {$cleanUsername}) created and linked successfully.");

                    } catch (\Exception $e) {
                        $this->command->error("Failed to create user or link for {$karyawan->Kode_Karyawan} (Nama: {$karyawan->Nama}, Cleaned Username: {$cleanUsername}): " . $e->getMessage());
                    }
                });
            } else {
                $this->command->warn("Skipping user {$karyawan->Kode_Karyawan} (Nama: {$karyawan->Nama}) due to missing Division_Id or Level_Id.");
            }
        }

        // Export daftar password setelah loop selesai
        $this->exportPasswordList($passwordList);

        $this->command->info('Users import process completed!');
    }

    /**
     * Membersihkan dan menormalisasi nama/kode karyawan.
     *
     * @param string $name
     * @return string
     */
    protected function cleanName(string $name): string
    {
        // Trim spasi di awal/akhir
        $name = trim($name);

        // Ganti spasi, titik, koma, dll. dengan string kosong atau karakter tertentu
        // RegEx untuk menghilangkan karakter non-alfanumerik kecuali spasi
        $name = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);

        $name = preg_replace('/\s+/', ' ', $name);
        $name = trim($name);

        // membuat string tanpa spasi dan tanda baca:
        $name = str_replace(' ', '', $name); // Hapus semua spasi

        return $name;
    }

    protected function exportPasswordList(array $list)
    {
        if (empty($list)) {
            $this->command->info('No users generated to export passwords.');
            return;
        }

        $fileName = 'user_passwords_' . now()->format('Ymd_His') . '.csv';
        $headers = array_keys($list[0]);
        $csvContent = implode(',', $headers) . "\n";

        foreach ($list as $row) {
            $csvContent .= implode(',', array_map(function($value) {
                return '"' . str_replace('"', '""', $value) . '"';
            }, $row)) . "\n";
        }

        Storage::disk('local')->put($fileName, $csvContent);

        $this->command->info("Password list exported to: storage/app/{$fileName}");
    }
}
