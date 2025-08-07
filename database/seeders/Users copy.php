<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Storage;
use App\Models\KpiUser; // This model is not directly used but imported, keep if needed elsewhere.
use Illuminate\Support\Facades\Log;
use App\Helpers\Whatsapp;
use Carbon\Carbon;



class Users extends Seeder
{

    public function run()
    {

        Log::channel('CreateUserLog')->error("[" . Carbon::now() . "] Proses pemberian user dan akses dimulai.........");

        $usernames = [
            'abdul', 'abduljabar', 'abdulrachman', 'ade', 'adelia', 'adi', 'adithiaibnu', 'aditia', 'aditiajabar', 'agnes', 'agung', 'agungwidodo', 'agungwijaya', 'agus', 'agusmarwansyah', 'aguswahyu', 'ahmad', 'ahmadismail', 'ahmadmaulana', 'ahmadrafly', 'ahmadrizky', 'ahmadsyakbani', 'ahmadtegar', 'ahmadrizky1', 'akbar', 'akhmad', 'alnapiro', 'alamsyah', 'aldi', 'aldiwarsito', 'muhammad', 'aldrian', 'alexia', 'alfred', 'allan', 'almiswari', 'amanda', 'amir', 'amirullah', 'amrullah', 'andi', 'andilala', 'muhammadando', 'andrean', 'andreas', 'angga', 'muhammadanggi', 'ani', 'ansar', 'anton', 'antonius', 'mapif', 'muhamad', 'muhammadarfan', 'ari', 'ariansyah', 'ary', 'arya', 'asep', 'asnawi', 'ayu', 'mazhari', 'azira', 'azwar', 'badarsyah', 'bambang', 'basumi', 'bayu', 'bayusaputra', 'beni', 'benisyarif', 'benny', 'berly', 'bobi', 'boby', 'candra', 'caren', 'cristiawan', 'christy', 'citra', 'clara', 'claradesky', 'cynthia', 'davi', 'daviriyanto', 'deahany', 'decy', 'della', 'dennis', 'deny', 'mdepo', 'muhammaddeswara', 'dewi', 'vira', 'didik', 'difany', 'muhammaddiffa', 'dila', 'dimas', 'dimasferdiansyah', 'dimaz', 'dinda', 'dufy', 'dwiki', 'muhammadedi', 'edi', 'edison', 'efiyal', 'muhammadeggi', 'eka', 'eko', 'elzalzabilla', 'memirudin', 'enggi', 'erlangga', 'erwin', 'eza', 'fadhal', 'muhammadfadhiil', 'muhammadfajar', 'faozan', 'muhammadfarhan', 'fariz', 'fatimah', 'mfaza', 'febri', 'febrinopriansyah', 'febrian', 'fefen', 'feni', 'fennya', 'ferdi', 'ferry', 'ferrykrispian', 'muhammadfikki', 'firdaus', 'fitra', 'frans', 'galih', 'gilang', 'gracelio', 'gusti', 'gustiputra', 'hadi', 'hadiismail', 'hadranus', 'hapryady', 'hardiyansyah', 'hasani', 'muhammadhasbi', 'helmi', 'hendra', 'hendra1', 'hendrayanto', 'herdiansyah', 'herry', 'heru', 'hifdi', 'husnil', 'igit', 'ihrom', 'ika', 'ilham', 'ilhampahrul', 'muhammadilham', 'ilham1', 'imron', 'imron1', 'indah', 'indra', 'muchamad', 'irfansyah', 'ivansyah', 'jaka', 'jansen', 'jasmel', 'muhammadjodi', 'johanes', 'joko', 'joni', 'muhammadjufri', 'juni', 'jupri', 'mkadapi', 'keken', 'kelvinputra', 'kemas', 'khaerul', 'khaidar', 'khoirul', 'muhammadkohardi', 'kshanti', 'kusnoto', 'kusuma', 'lendri', 'leonardo', 'leo', 'mikhsan', 'mafwan', 'mramdani', 'muhammadridho', 'mslamet', 'msulaiman', 'muhammadagung', 'makmur', 'mario', 'mariodeswan', 'matias', 'merri', 'mevi', 'michelle', 'miftahudin', 'miftahul', 'mirna', 'misja', 'muflih', 'muh', 'muhnur', 'muhammadaris', 'muhammadfajri', 'muhammadilham1', 'muhammadirvan', 'muhammadjefri', 'muhammadarifin', 'muhtar', 'muhtar1', 'almukmin', 'mulkan', 'mmuslim', 'nadia', 'nadiapuspita', 'nasai', 'nawawi', 'nawrah', 'niegle', 'niki', 'noprian', 'nordin', 'novelly', 'novia', 'novita', 'nur', 'nurhalimah', 'nurhasansyah', 'nurwahida', 'nurkholifah', 'nurrizka', 'nursoleh', 'masri', 'nyayu', 'oddie', 'okta', 'oktapianus', 'oxa', 'ozi', 'padli', 'pahri', 'panca', 'patjeri', 'mpebridaryanto', 'peri', 'petrus', 'pingky', 'pipin', 'firdaus1', 'pran', 'prayogo', 'pringgalaya', 'prio', 'prayogi', 'purwanto', 'putri', 'qori', 'rachmat', 'raden', 'muhammadrafik', 'rafli', 'ragil', 'rahayu', 'rahmad', 'rahmat', 'rahmawati', 'raju', 'rakha', 'rama', 'mramadan', 'ramadhan', 'ramita', 'ramlan', 'rani', 'regina', 'remigritas', 'resti', 'reva', 'rexi', 'rian', 'riansaputra', 'rianmar', 'richi', 'ricky', 'rickyaditya', 'rickyoktavianus', 'ridho', 'riffany', 'rina', 'rio', 'risqi', 'riskiapriyansya', 'muhamadrivy', 'rizal', 'rizki', 'rizkiwahyudi', 'rizky', 'rizkychandra', 'rizkydarma', 'mrizky', 'roli', 'rudi', 'rudihandoko', 'ruth', 'ryan', 'sabine', 'sahat', 'sahroni', 'saiful', 'samsul', 'anastasia', 'sani', 'santo', 'sapriyanto', 'sarfuni', 'sarkim', 'sarmono', 'sarnubi', 'saumia', 'selamat', 'sellystia', 'septa', 'septiawan', 'setenli', 'setiyono', 'setyo', 'shintia', 'sigit', 'silvana', 'soni', 'mochamad', 'stepanus', 'stephany', 'suanda', 'suci', 'sudodo', 'sugiyono', 'suhadi', 'sukimin', 'sulaiman', 'suliwa', 'suparman', 'suparno', 'supriyadi', 'surya', 'sutarso', 'syafira', 'syahdan', 'syaikhul', 'syamsuddin', 'syamsyul', 'tahadodo', 'taufiq', 'tedi', 'tegar', 'theodora', 'toto', 'mtriefani', 'trisna', 'tsurayya', 'usama', 'musman', 'valensyah', 'valentinus', 'vanisha', 'vederico', 'vincent', 'virafebrianti', 'vonny', 'wahid', 'wahyu', 'mwahyu', 'wahyudi', 'wahyuromadhon', 'iwan', 'wanda', 'warsito', 'wawan', 'widisyah', 'wilbert', 'mwildan', 'willy', 'wisnu', 'mubaroq', 'wulan', 'heriyanto', 'yebi', 'yen', 'yoga', 'yogi', 'yudha', 'yuli', 'yulia', 'yulinda', 'yunaldi', 'yunia', 'yusuf', 'yusup', 'yuyun', 'zahran', 'ABDULRACHMAN', 'AGUNGSETIYAWAN', 'AGUNG', 'TOMI', 'AKONG', 'ALFAN', 'ALFIAN', 'MUHAMMADALFIANKURNIA', 'ALFIN', 'ALIABAS', 'ALI', 'ALIFLARIK', 'ANNAUFAL', 'ANANDAPUTRIETHELVIST', 'ANGGASURYADANA', 'ARIYANTO', 'ASTRID', 'CHAIRUDDIN', 'CUCU', 'DADI', 'DANI', 'DARA', 'DAWUDPRASETYO', 'NINA', 'ENDRIEF', 'ERICKCANTONA', 'ERIK', 'FADHILAH', 'FAJARKURNIAWAN', 'FANDY', 'FARHAN', 'AHMADFARIDMUMTAZ', 'FARIZAL', 'FAUZANHERLIANSYAH', 'FERDY', 'FRANS', 'GALIH', 'HABIBI', 'HAERUDIN', 'HANSEN', 'HENDRYANTO', 'HERI', 'ILMANADITYAPANGESTU', 'ISMAIL', 'IVAN', 'YONRI', 'TULUS', 'KAMALLUDINSYARIFMAYA', 'LINGGARDWIAGUSTIN', 'LISKA', 'MLUTHFIABIFASHA', 'MIRWANSYAHPUTRA', 'REZA', 'MANSYUR', 'MARDHAN', 'MARDIANSAPUTRA', 'MARLISA', 'MICHELLE', 'MIEKI', 'ARDI', 'MUHAMMADMUNADHILSHOIB', 'MUSLIYA', 'NAFIUR', 'PANDU', 'PRIMA', 'PURWA', 'PUTU', 'RANDOLPH', 'RATNA', 'REKASETIAWAN', 'RIBKA', 'RIDHOR', 'ANDIRIFAATULMAHMUDA', 'RININOORSIAMAH', 'RIZKI', 'ROBY', 'RUDIFIRMANSYAH', 'SANDY', 'SARJIYO', 'SHERLY', 'SIGIT', 'STELLA', 'SYAIFULBAHRI', 'TANHAR', 'TAUFIKSAPUTRA', 'TEGUH', 'VENGINE', 'VERA', 'JATI', 'WELLY', 'WILI', 'WILSON', 'YASMIN', 'YOGAADHITIA', 'YUARIKA', 'YULI', 'YUSAAPRIANA', 'ZAINAL',

        ];
        $karyawanCodes = [
            'ABDUL BASHIR', 'ABDUL JABAR', 'ABDUL RACHMAN', 'ADE MUHAMAMMAD', 'ADELIA VERDIANT', 'ADI GALLY', 'ADITHIA', 'ADITIA CANDRA', 'ADITIA JABAR', 'AGNES', 'AGUNG PANO', 'AGUNG WIDODO', 'AGUNG WIJAYA', 'AGUS KURNIAWAN', 'AGUS MARWANSYAH', 'AGUS WAHYU', 'AHMAD AZIS', 'AHMAD ISMAIL', 'AHMAD MAULANA', 'AHMAD RAFLY', 'AHMAD RIZKY', 'AHMAD SYAKBANI', 'AHMAD TEGAR', 'AHMADRIZKY', 'AKBARR', 'AKHMAD FAISAL', 'AL-NAPIRO', 'ALAMSYAH', 'ALDI FIRMANSYAH', 'ALDI WARSITO', 'ALDIYANTO', 'ALDRIAN', 'ALEXIA C', 'ALFRED LAULACE', 'ALLAN MAULANA', 'ALMISWARI', 'AMANDA', 'AMIR', 'AMIRULLAH', 'AMRULLAH DEZA', 'ANDI SALSABIL', 'ANDILALA', 'ANDO TRIWIBOWO', 'ANDREAN', 'ANDREAS', 'ANGGA SAPUTRA', 'ANGGI MAULANA', 'ANI', 'ANSAR', 'ANTALARIKSYAH', 'ANTONIUS', 'APIF HIDAYAT', 'APRIANSYAH', 'ARFAN', 'ARI LUTFI', 'ARIAN', 'ARY SURYANTO', 'ARYA D', 'ASEP SUHERMAN', 'ASNAWI', 'AYU M', 'AZHARI DIAN', 'AZIRA BERLIANA', 'AZWAR', 'BADARSYAH', 'BAMBANGG', 'BASUMI', 'BAYU KURNIAWAN', 'BAYU SAPUTRA', 'BENI SOFYAN', 'BENI SYARIF', 'BENNY EKO', 'BERLY', 'BOBI ANSRIYEDI', 'BOBY', 'CANDRA DINATA', 'CAREN', 'CHRISTIAWAN', 'CHRISTY', 'CITRA', 'CLARA', 'CLARA DESKY', 'CYNTHIA', 'DAVI PRATAMA', 'DAVI RIYANTO', 'DEA', 'DECY CAHYANI', 'DELLA', 'DENNIS', 'DENY', 'DEPO AROJA', 'DESWARA', 'DEWI SARINA', 'DIAN', 'DIDIK', 'DIFANY', 'DIFFA', 'DILA', 'DIMAS NATA', 'DIMASFERDIANSYA', 'DIMAZ', 'DINDA PUTRI', 'DUFY THAHARY', 'DWIKI AHKAM', 'EDI IRAWAN', 'EDI LEMAN', 'EDISON', 'EFIYAL', 'EGGI', 'EKA ATHIATUR', 'EKO HARDIYANTO', 'ELZA', 'EMIRUDIN', 'ENGGI', 'ERLANGGA', 'ERWIN SETIAWAN', 'EZA PRATAMA', 'FADHAL AKRAM', 'FADHIIL', 'FAJAR HIDAYAT', 'FAOZAN', 'FARHAN HIDAYAT', 'FARIZ SETIADI', 'FATIMAH', 'FAZA', 'FEBRI ISWANTO', 'FEBRI NOPRIAN', 'FEBRIAN SAPUTRA', 'FEFEN EFFENDIE', 'FENI', 'FENNYA', 'FERDI', 'FERRY', 'FERRY KRISPIAN', 'FIKKI RAYHAN', 'FIRDAUS', 'FITRA', 'FRANS BACHTIAR', 'GALIH', 'GILANG RAMADHAN', 'GRACELIO', 'GUSTI', 'GUSTI PUTRA', 'HADI', 'HADI ISMAIL', 'HADRANUS', 'HAPRYADY', 'HARDIYANSYAH', 'HASANI HIDAYAH', 'HASBI', 'HELMI', 'HEND', 'HENDR', 'HENDRAYANTO', 'HERDIANSYAH', 'HERRY SETIAWAN', 'HERU', 'HIDFI', 'HUSNIL', 'IGIT SOLIHIN', 'IHROM MAULANA', 'IKA NUR AINI', 'ILHAM N', 'ILHAM PAHRUL', 'ILHAM PERDANA', 'ILHAMM', 'IMRON', 'IMRONN', 'INDAH KURNIA', 'INDRA WIJAYA', 'IQBAL SAMSUL', 'IRPAN', 'IVANSYAH KRISNA', 'JAKA WARDANA', 'JANSEN', 'JASMEL', 'JODI', 'JOHANES', 'JOKO SETIAWAN', 'JONI', 'JUFRI', 'JUNI ARKA', 'JUPRI', 'KADAPI', 'KEKEN', 'KELVIN PUTRA', 'KEMAS', 'KHAERUL AZMI', 'KHAIDAR', 'KHOIRUL ANAM', 'KOHARDI', 'KSHANTI', 'KUSNOTO', 'KUSUMA H.W', 'LENDRI', 'LEO', 'LEO RAKHMAN', 'M IKHSAN', 'M. AFWAN HISANI', 'M. RAMDANI', 'M. RIDHO', 'M. SLAMET', 'M. SULAIMAN', 'M.AGUNG', 'MAKMUR M', 'MARIO', 'MARIO DESWAN', 'MATIAS', 'MERRI', 'MEVI', 'MICHELLE', 'MIFTAHUDIN', 'MIFTAHUL', 'MIRNA', 'MISJA', 'MUFLIH', 'MUH FAJAR', 'MUH.NUR', 'MUHAMMAD ARIS', 'MUHAMMAD FAJRI', 'MUHAMMAD ILHAM', 'MUHAMMAD IRVAN', 'MUHAMMAD JEFRI', 'MUHAMMADARIFIN', 'MUHTAR', 'MUHTARR', 'MUKMIN', 'MULKAN', 'MUSLIM', 'NADIA PUSPITA', 'NADIAPUSPITA', 'NASA`I', 'NAWAWI', 'NAWRAH', 'NIEGLE', 'NIKI', 'NOPRIAN', 'NORDIN', 'NOVELLY', 'NOVIA', 'NOVITA', 'NUR AINI', 'NUR HALIMAH', 'NUR HASANSYAH', 'NUR WAHIDA', 'NURKHOLIFAH', 'NURRIZ', 'NURSOLEH', 'NUSI', 'NYAYU NAZILA', 'ODDIE', 'OKTA PIANUS', 'OKTAPIANUS', 'OXA MEIDONA', 'OZI FAOZI', 'PADLI UMAR', 'PAHRI', 'PANCA', 'PATJERI', 'PEBRIDARYANTO', 'PERI ANDIKA', 'PETRUS', 'PINGKY', 'PIPIN', 'PIRDAUS', 'PRAN', 'PRAYOGO', 'PRINGGALAYA', 'PRIO', 'PROYOGI MULIA', 'PURWANTO', 'PUTRI DIAR', 'QORI', 'RACHMAT', 'RADEN ROBY', 'RAFIK', 'RAFLI AGUNG', 'RAGIL', 'RAHAYU', 'RAHMAD RAMADON', 'RAHMAT N', 'RAHMAWATI', 'RAJU', 'RAKHA', 'RAMA TRIANSYAH', 'RAMADAN', 'RAMADHAN ADIRYA', 'RAMITA', 'RAMLAN', 'RANI', 'REGINA', 'REMIGRITAS', 'RESTI', 'REVA', 'REXI', 'RIAN KRISNADI', 'RIAN SAPUTRA', 'RIANMAR', 'RICHI', 'RICKY', 'RICKY ADITYA', 'RICKY OKTA', 'RIDHO SATRIO', 'RIFFANY', 'RINA', 'RIO ALRASYID', 'RIQI AJI PALUPI', 'RISKI', 'RIVY', 'RIZAL', 'RIZKI SYAHPUTRA', 'RIZKI WAHYUDI', 'RIZKY ADINDA', 'RIZKY CHANDRA', 'RIZKY DARMA', 'RIZKY ZULPA', 'ROLI AFDIANSYAH', 'RUDI FIRMANSYAH', 'RUDI HANDOKO', 'RUTH', 'RYAN', 'SABINE', 'SAHAT', 'SAHRONI', 'SAIFUL ARIF', 'SAMSUL', 'SANDRA', 'SANI', 'SANTO YOSAFAT', 'SAPRIYANTO', 'SARFUNI', 'SARKIM', 'SARMONO', 'SARNUBI', 'SAUMIA KRISNA', 'SELAMAT LUBIS', 'SELLYSTIA', 'SEPTA', 'SEPTIAN', 'SETENLI', 'SETIYONO', 'SETYO NUGROHO', 'SHINTIA M', 'SIGIT KAMSENO', 'SILVANA', 'SONI SAPUTRA', 'SOPYAN', 'STEPANUS', 'STEPHANY', 'SUANDA', 'SUCI RAMADANI', 'SUDODO', 'SUGIYONO', 'SUHADI', 'SUKIMIN', 'SULAIMAN', 'SULIWA', 'SUPARMAN', 'SUPARNO', 'SUPRIYADI', 'SURYA', 'SUTARSO', 'SYAFIRA', 'SYAHDAN', 'SYAIKHUL', 'SYAMSUDDIN', 'SYAMSUL', 'TAHADODO NDURU', 'TAUFIQ', 'TEDI HARDIYANTO', 'TEGAR WAHYUDI', 'THEODORA', 'TOTO', 'TRIEFANI', 'TRISNA JORDI', 'TSURAYYA', 'USAMA', 'USMAN', 'VALENSYAH', 'VALENTINUS', 'VANISHA', 'VEDERICO', 'VINCENT', 'VIRA FEBRIANTI', 'VONNY', 'WAHID WAHYUDI', 'WAHYU MADI', 'WAHYU ROMADHON', 'WAHYUDI RIO', 'WAHYUROMADHON', 'WAN', 'WANDA SYAKIAH', 'WARSITO', 'WAWA', 'WIDISYAH', 'WILBERT', 'WILDAN PURNAMA', 'WILLY HASTA', 'WISNU GALIH', 'WISNU MUBAROQ', 'WULAN INDAH', 'YANTO', 'YEBI', 'YEN YEN', 'YOGA PLB', 'YOGI PRASTYO', 'YUDHA PRATAMA', 'YULI YANDI', 'YULIA DWIYANTI', 'YULINDA', 'YUNALDI', 'YUNIA', 'YUSUF M', 'YUSUP', 'YUYUN VIDIA', 'ZAHRAN','ABDUL RACHMAN', 'AGUNG SETIYAWAN', 'AGUNGFAHREZA', 'AGUS TOMI', 'AKONG', 'ALFAN', 'ALFIAN EKO', 'ALFIAN KURNIA', 'ALFIN FIRDAUS', 'ALI ABAS', 'ALI SULAWESI', 'ALIFLARIK', 'AN NAUFAL', 'ANANDA PUTRI', 'ANGGA SURYA', 'ARIYANTO', 'ASTRID', 'CHAIRUDDIN', 'CUCU', 'DADI ISWARA', 'DANI RAMDANI', 'DARA', 'DAWUD', 'DRH.NINA', 'ENDRIEF', 'ERICK', 'ERIK ESTRADA', 'FADHILAH', 'FAJAR', 'FANDY', 'FARHAN', 'FARID', 'FARIZAL', 'FAUZAN', 'FERDY IRAWAN', 'FRANS BACHTIAR', 'GALIH', 'HABIBI MOMEA', 'HAERUDIN', 'HANSEN', 'HENDRIYANTO A', 'HERI SUDRAJAT', 'ILHAM ADITYA', 'ISMAIL', 'IVAN', 'JEMMY YONRI', 'JOJON TULUS', 'KAMALLUDIN', 'LINGGAR', 'LISKA', 'M LUTHFI', 'M. IRWANSYAH', 'M.REZA', 'MANSYUR M', 'MARDHAN', 'MARDIAN', 'MARLISA', 'MICHELLE', 'MIEKI', 'MOH.ARDIANSYAH', 'MUNADHIL', 'MUSLIYA WIJAYA', 'NAFIUR', 'PANDU', 'PRIMA', 'PURWA', 'PUTU WIRA', 'RANDOLPH', 'RATNA MAULINA', 'REKA SETIAWAN', 'RIBKA', 'RIDHO RAHMAT', 'RIFAATUL', 'RINI NOOR', 'RIZKI FEBRIAN', 'ROBY R', 'RUDI FIRMANSYAH', 'SANDYSANDIKA', 'SARJIYO', 'SHERLY INDRA', 'SIGIT RIANTO', 'STELLA', 'SYAIFUL', 'TANHAR MAHARSI', 'TAUFIK SAPUTRA', 'TEGUH', 'VENGINE', 'VERA ANISAH', 'WAHYU JATI', 'WELLY', 'WILI NANDA', 'WILSON', 'YASMIN', 'YOGA ADHITIA', 'YUARIKA', 'YULI ACC', 'YUSA', 'ZAINAL'
        ];
        $specialAccess = [
            'GALIH', 'AGUS WAHYU', 'AKONG', 'ALFAN', 'ALFIAN EKO', 'CHAIRUDDIN', 'DIDIK', 'DIMAS', 'DRH.NINA', 'ENDRIEF',
            'ERIK ESTRADA', 'HAERUDIN', 'HENDRIYANTO A', 'HERI SUDRAJAT', 'IVAN', 'JEMMY YONRI', 'KHAERUL AZMI', 'LISKA',
            'LIUS', 'M.REZA', 'MANSYUR M', 'MARDHAN', 'MARIO DESWAN', 'MARLISA', 'MIEKI', 'PANDU', 'PARMANTO', 'PRIMA',
            'PURWA', 'RANI', 'RIDWANSYAH', 'ROBY R', 'RUDI FIRMANSYAH', 'SETYO NUGROHO', 'TANHAR MAHARSI', 'TEGUH',
            'VERA ANISAH', 'WAHYU JATI', 'WILSON', 'YUSTINA'
        ];

        $karyawanData = Karyawan::whereNull('Tanggal_Resign')
            ->whereIn('Kode_Karyawan', $karyawanCodes)
            ->get()
            ->sortBy(function ($karyawan) use ($karyawanCodes) {
                return array_search($karyawan->Kode_Karyawan, $karyawanCodes);
            })
            ->values();

        if (count($usernames) !== count($karyawanData)) {
            $this->command->error("Jumlah username (" . count($usernames) . ") dan data karyawan (" . count($karyawanData) . ") tidak sama. Proses dihentikan.");
            return;
        }

        $saltFront = env('SALT_FRONT');
        $saltBack = env('SALT_BACK');
        $passwordList = [];

        for ($i = 0; $i < count($usernames); $i++) {
            $username = $usernames[$i];
            $karyawan = $karyawanData[$i];
            $cleanUsername = $this->cleanName($username);
            $cleanPasswordPart = $this->cleanName($username);
            $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $rawPassword = $cleanPasswordPart . '_' . $randomNumber;
            $passwordWithSalt = $saltFront . $rawPassword . $saltBack;
            $encryptedPassword = Hash::make($passwordWithSalt);

            $divisionId = $karyawan->division ? $karyawan->division->ID_Divisi : null;
            $levelId = $karyawan->level ? $karyawan->level->ID_Level : null;

            if (!$divisionId || !$levelId) {
                $this->command->warn("Karyawan {$karyawan->Kode_Karyawan} tidak memiliki Division atau Level.");
                continue;
            }

            // Cek apakah user sudah ada
            $existingUser = DB::table('KPI_Users')
                ->where('Kode_Users', $karyawan->Kode_Karyawan)
                ->first();

            $userId = null;

            DB::beginTransaction();
            try {
                if (!$existingUser) {
                    // INSERT user baru
                    $userId = DB::table('KPI_Users')->insertGetId([
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

                    $karyawan->UserID_Web = $userId;
                    $karyawan->save();

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
                                        ["type" => "text", "text" => $cleanUsername],
                                        ["type" => "text", "text" => $karyawan->Nama],
                                        ["type" => "text", "text" => $rawPassword],
                                    ]
                                ]
                            ]
                        ]
                    ];
                    $response = Whatsapp::send_message($pesan);
                    Log::channel('whatsapp_error')->warning('WA Response', ['pesan' => $response]);

                    $passwordList[] = [
                        'Username' => $cleanUsername,
                        'Nama' => $karyawan->Nama,
                        'Raw_Password' => $rawPassword,
                    ];

                    $this->command->info("User {$karyawan->Kode_Karyawan} dibuat.");
                    Log::channel('CreateUserLog')->info("[" . Carbon::now() . "] User {$karyawan->Kode_Karyawan} ({$cleanUsername}) dibuat dan ditambahkan ke KPI_Users.");
                } else {
                    $userId = $existingUser->Id_Users;
                    $this->command->info("User {$karyawan->Kode_Karyawan} sudah ada, akan diberi akses.");
                    Log::channel('CreateUserLog')->info("[" . Carbon::now() . "] User {$karyawan->Kode_Karyawan} sudah ada. Melanjutkan pemberian akses.");

                }

                // Beri akses (umum)
                $izinPageExists = DB::table('HRIS_Page_Access')
                    ->where('UserID_Web', $userId)
                    ->where('Jenis_Page', 'IzinPage')
                    ->exists();

                if (!$izinPageExists) {
                    DB::table('HRIS_Page_Access')->insert([
                        'Kode_Perusahaan' => '001',
                        'ID_Level' => $levelId,
                        'ID_Divisi' => $divisionId,
                        'Jenis_Page' => 'IzinPage',
                        'UserID_Web' => $userId,
                    ]);
                    $this->command->info("Akses IzinPage diberikan ke {$karyawan->Kode_Karyawan}");
                    Log::channel('CreateUserLog')->info("[" . Carbon::now() . "] Akses IzinPage diberikan ke {$karyawan->Kode_Karyawan}.");

                }

                // Beri akses approver jika termasuk special
                if (in_array($karyawan->Kode_Karyawan, $specialAccess)) {
                    $approverPageExists = DB::table('HRIS_Page_Access')
                        ->where('UserID_Web', $userId)
                        ->where('Jenis_Page', 'IzinPageApprover')
                        ->exists();

                    if (!$approverPageExists) {
                        DB::table('HRIS_Page_Access')->insert([
                            'Kode_Perusahaan' => '001',
                            'ID_Level' => $levelId,
                            'ID_Divisi' => $divisionId,
                            'Jenis_Page' => 'IzinPageApprover',
                            'UserID_Web' => $userId,
                        ]);
                        $this->command->info("Akses Approver diberikan ke {$karyawan->Kode_Karyawan}");
                        Log::channel('CreateUserLog')->info("[" . Carbon::now() . "] Akses Approver (IzinPageApprover) diberikan ke {$karyawan->Kode_Karyawan}.");

                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                $this->command->error("Error pada {$karyawan->Kode_Karyawan}: " . $e->getMessage());
                Log::channel('CreateUserLog')->error("[" . Carbon::now() . "] ERROR pada {$karyawan->Kode_Karyawan}: " . $e->getMessage());

            }
        }

        $this->exportPasswordList($passwordList);
        $this->command->info('Proses selesai.');
        Log::channel('CreateUserLog')->error("[" . Carbon::now() . "] Proses selesai");

    }


    protected function cleanName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/[^a-zA-Z0-9\s]/', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        $name = trim($name);
        $name = str_replace(' ', '', $name);
        return $name;
    }

    protected function exportPasswordList($list)
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
