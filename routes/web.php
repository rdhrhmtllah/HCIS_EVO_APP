<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\karyawanTeamController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\CrossDivisionReviewController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\parseResume;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GetFotoController;
use App\Http\Controllers\MasterPeriodeController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\uDashController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\userController;
use Illuminate\Support\Facades\Artisan;
// use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request;
use Carbon\Carbon;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('login');
})->middleware('guest');

Route::get('/reset-user', function () {

    $user = Auth::user();
    if (!$user) {
        return redirect('/login')->with('status', 'Silakan login terlebih dahulu.');
    }

    $userId = $user->Id_Users;

    // --- Hapus session user ---
    if (config('session.driver') === 'database') {
        \DB::table('sessions')->where('user_id', $userId)->delete();
    }
    Session::flush();

    // --- Logout user ---
    Auth::logout();

    // --- Hapus semua cookies ---
    foreach (request()->cookies as $key => $value) {
        Cookie::queue(Cookie::forget($key));
    }

    // --- Hapus cache terkait user ---
    Cache::forget('user_'.$userId.'_some_data');

    return redirect('/login')->with('status', 'Status aplikasi Anda berhasil direset.');
});
// Route::get('/absensiSales', function () {
//     return inertia('absensiSales');
// });


Route::get('/getTime', function (Request $request) {
    $now = now()->toDateTimeString();

    // Sekenario tanggal 26 agt 2025 jam 20:29 hari selasa sebagai perwakilan weekday
    // $now = Carbon::parse('2025-08-31 02:30:00', 'Asia/Jakarta')->toDateTimeString();
    return response()->json(['status'=> 200,'time' => $now]);
})->name('getTime');

Route::get('/login', [AuthController::class, 'login'])->name('login')->middleware('guest');
Route::get('/mulai/27738', [AuthController::class, 'loginRe'])->name('loginRe')->middleware('guest');
Route::post('/prosesLogin', [AuthController::class, 'prosesLogin'])->name('prosesLogin');

Route::middleware(['auth'])->group(function () {
    Route::get('/resetPassword', [AuthController::class, 'reset'])->name('password.reset');
    Route::post('/changeDataUser', [AuthController::class, 'changeData'])->name('change.data');
    Route::post('/updatePassword', [AuthController::class, 'update'])->name('password.update');

    Route::get('/tiket', [TicketController::class, 'index']);
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboardSuperUser', [DashboardController::class, 'superUser'])->name('superUser');
    Route::get('/dashboardUser', [DashboardController::class, 'user'])->name('user');

    Route::get('/master-periode', [MasterPeriodeController::class, 'index'])->name('master.periode');
    Route::get('/master-periode/display_tambah', [MasterPeriodeController::class, 'display_tambah_periode'])->name('master.periode.display_tambah');
    Route::post('master-periode/display_tambah', [MasterPeriodeController::class, 'tambah_periode'])->name('master.periode.add.data');

    Route::get('/user-profile', [userController::class, 'index'])->name('profileUser.index');

    // // Cross Division Review
    // route::get('/crossDivisionReview', [CrossDivisionReviewController::class, 'index'])->name('Cross.index');
    // route::get('/getReview/{id}', [CrossDivisionReviewController::class, 'getReviews'])->name('Cross.Reviews');
    // route::get('/getPeriode/{id}', [CrossDivisionReviewController::class, 'getPeriode'])->name('Cross.getPeriode');
    // route::post('/storeCrossReview', [CrossDivisionReviewController::class, 'store'])->name('Cross.store');
    // Route::get('/historyCrossReview', [CrossDivisionReviewController::class, 'history'])->name('cross.history');
    // Route::get('/crossReviewPeriode', [CrossDivisionReviewController::class, 'periode'])->name('cross.periode');
    // Route::get('/addCrossReviewPeriode', [CrossDivisionReviewController::class, 'addPeriode'])->name('cross.add.periode');
    // route::post('/storeCrossReviewPeriode', [CrossDivisionReviewController::class, 'storePeriode'])->name('cross.store.periode');
    // route::post('/destroyCrossPeriode', [CrossDivisionReviewController::class, 'destroyPeriode'])->name('cross.destroy.periode');

    // Cross Division Review
    route::get('/crossDivisionReview', [CrossDivisionReviewController::class, 'index'])->name('Cross.index');
    route::get('/getReview/{Periode}/{User}', [CrossDivisionReviewController::class, 'getReviews'])->name('Cross.Reviews');
    route::get('/getPeriode/{id}', [CrossDivisionReviewController::class, 'getPeriode'])->name('Cross.getPeriode');
    route::get('/getUser/{periode}/{divisi}', [CrossDivisionReviewController::class, 'getUser'])->name('Cross.getUser');
    route::post('/storeCrossReview', [CrossDivisionReviewController::class, 'store'])->name('Cross.store');
    Route::get('/historyCrossReview', [CrossDivisionReviewController::class, 'history'])->name('cross.history');
    Route::get('/crossFeedbackReview', [CrossDivisionReviewController::class, 'feedback'])->name('cross.feedback');
    Route::get('/crossFeedbackDetail/{no_review}', [CrossDivisionReviewController::class, 'feedbackDetail'])->name('cross.feedback.Detail');
    Route::get('/crossReviewPeriode', [CrossDivisionReviewController::class, 'periode'])->name('cross.periode');
    Route::get('/addCrossReviewPeriode', [CrossDivisionReviewController::class, 'addPeriode'])->name('cross.add.periode');
    route::post('/storeCrossReviewPeriode', [CrossDivisionReviewController::class, 'storePeriode'])->name('cross.store.periode');
    route::post('/destroyCrossPeriode', [CrossDivisionReviewController::class, 'destroyPeriode'])->name('cross.destroy.periode');
    Route::get('/crossReviewSummary', [CrossDivisionReviewController::class, 'summary'])->name('cross.summary');
    Route::get('/summary-cross-review-detail/{Id_Periode}/{Id_Division}', [CrossDivisionReviewController::class, 'summaryReview'])->name('summary.cross.review.detail');

    //Division
    Route::get('/divison', [DivisionController::class, 'index'])->name('division.index');
    Route::get('/division/create', [DivisionController::class, 'create'])->name('division.create');
    Route::post('/storeDivision', [DivisionController::class, 'store'])->name('division.store');
    Route::get('/division/edit/{id}', [DivisionController::class, 'edit'])->name('division.edit');
    Route::put('/division/{id}', [DivisionController::class, 'update'])->name('division.update');
    Route::delete('/division/{id}', [DivisionController::class, 'destroy'])->name('division.destroy');


    //Level
    Route::get('/level', [LevelController::class, 'index'])->name('level.index');
    Route::get('/level/create', [LevelController::class, 'create'])->name('level.create');
    Route::post('storeLevel', [LevelController::class, 'store'])->name('level.store');
    Route::get('/level/edit/{id}', [LevelController::class, 'edit'])->name('level.edit');
    Route::put('/level/{id}', [LevelController::class, 'update'])->name('level.update');
    Route::delete('/level/{id}', [LevelController::class, 'destroy'])->name('level.destroy');

    //Employee
    Route::get('/employee', [EmployeeController::class, 'index'])->name('employee.index');
    Route::get('employee/create', [EmployeeController::class, 'create'])->name('employee.create');
    Route::post('/storeEmployee', [EmployeeController::class, 'store'])->name('employee.store');
    Route::get('/employee/edit/{id}', [EmployeeController::class, 'edit'])->name('employee.edit');
    Route::put('/employee/{id}', [EmployeeController::class, 'update'])->name('employee.update');
    Route::delete('/employee/{id}', [EmployeeController::class, 'destroy'])->name('employee.destroy');

    // Case
    Route::get('/case', [CaseController::class, 'index'])->name('case.index');
    Route::get('/case/create', [CaseController::class, 'create'])->name('case.create');
    Route::post('/storeCase', [CaseController::class, 'store'])->name('case.store');
    Route::post('/case/edit', [CaseController::class, 'edit'])->name('case.edit');
    Route::put('/case/{id}', [CaseController::class, 'update'])->name('case.update');
    Route::post('/case/delete', [CaseController::class, 'destroy'])->name('case.destroy');

    // Ticket
    Route::get('/ticket', [TicketController::class, 'index'])->name('ticket.index');
    Route::get('/displayTicket', [TicketController::class, 'displayTicket'])->name('displayTicket');
    Route::get('/takeTicket', [TicketController::class, 'takeTicket'])->name('takeTicket');
    Route::post('/responseTicket', [TicketController::class, 'responseTicket'])->name('responseTicket');
    Route::get('/finishTicket', [TicketController::class, 'finishTicket'])->name('finishTicket');
    Route::get('/TicketReview/{id_ticket}', [TicketController::class, 'display_review_ticket'])->name('ticket.review');
    Route::get('/DetailTicket/{id_ticket}/{from}', [TicketController::class, 'display_detail_ticket'])->name('ticket.detail');

    Route::post('/simpan_review_ticket', [TicketController::class, 'simpan_review_ticket'])->name('simpan.review.ticket');
    Route::post('/finishTicket', [TicketController::class, 'storeTicket'])->name('storeTicket');
    Route::post('approveFinishTicket/{id}', [TicketController::class, 'approveFinishTicket'])->name('approveFinishTicket');
    Route::post('revisionFinishTicket/{id}', [TicketController::class, 'revisionFinishTicket'])->name('revisionFinishTicket');

    Route::get('getPICTicket/{id}', [TicketController::class, 'getPICTicket']);
    Route::post('/simpanTicket', [TicketController::class, 'simpanTicket']);

    Route::get('/secure-file/{filename}', [GetFotoController::class, 'show'])->name('show.file');



    // Global Review
    Route::get('/globalReview', [ReviewController::class, 'index'])->name('globalReview.index');

    Route::get('/getPeriode-review/{id}', [ReviewController::class, 'getPeriode']);
    Route::get('/getReview-user/{id}', [ReviewController::class, 'getReviewUser']);

    Route::get('/globalReview2', [ReviewController::class, 'index2'])->name('globalReview2.index');
    Route::get('/summary-review-detail/{no_review}/{id}', [ReviewController::class, 'summaryReview'])->name('summary.review.detail');
    Route::get('/summary-review', [ReviewController::class, 'summaryReviewIndex'])->name('summary.review');
    Route::post('/assestmentGlobalReview', [ReviewController::class, 'store'])->name('kpi-global-review.store');


    // Evaluation
    Route::get('/evaluation', [EvaluationController::class, 'index'])->name('evaluation.index');
    Route::get('/evaluation/get', [EvaluationController::class, 'get'])->name('evaluation.get');
    Route::get('/evaluation/search', [EvaluationController::class, 'search'])->name('evaluation.search');
    Route::get('/evaluation/create', [EvaluationController::class, 'create'])->name('evaluation.create');
    Route::post('/storeEvaluation', [EvaluationController::class, 'store'])->name('evaluation.store');
    Route::post('/evaluation/edit', [EvaluationController::class, 'edit'])->name('evaluation.edit');
    Route::put('/evaluation/{id}', [EvaluationController::class, 'update'])->name('evaluation.update');
    Route::post('/evaluation/delete', [EvaluationController::class, 'destroy'])->name('evaluation.destroy');

    // task
    Route::get('/task', [TaskController::class, 'index'])->name('task.index');
    Route::get('/task/create', [TaskController::class, 'create'])->name('task.create');
    Route::get('/task/getDivision', [TaskController::class, 'getUser'])->name('task.Division');
    Route::post('/storetask', [TaskController::class, 'store'])->name('task.store');
    Route::post('/task/edit', [TaskController::class, 'edit'])->name('task.edit');
    Route::put('/task/{id}', [TaskController::class, 'update'])->name('task.update');
    Route::post('/task/delete', [TaskController::class, 'destroy'])->name('task.destroy');
    Route::post('/task/updateProgress', [TaskController::class, 'progressUpdate'])->name('task.progress');

    // Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    // Route::post('/news/store', [NewsController::class, 'store'])->name('news.store');

    // project
    Route::get('/project', [ProjectController::class, 'index'])->name('project.index');
    Route::get('/project/create', [ProjectController::class, 'create'])->name('project.create');
    Route::post('/storeproject', [ProjectController::class, 'store'])->name('project.store');
    Route::post('/project/edit', [ProjectController::class, 'edit'])->name('project.edit');
    Route::put('/project/{id}', [ProjectController::class, 'update'])->name('project.update');
    Route::post('/project/delete', [ProjectController::class, 'destroy'])->name('project.destroy');

    // Absensi
        // Route::middleware(['auth', 'check.jabatan:IzinPage, IzinPageApprover'])->group(function(){

        //     Route::get('/lembur',[AbsensiController::class, 'indexLembur'])->name('absensi.lembur');
        //     Route::middleware(['auth','check.jabatan:IzinPage'])->group(function(){
        //         Route::get('/izin',[AbsensiController::class, 'indexIzin'])->name('absensi.izin');
        //         Route::get('/izin/getDataIzin',[AbsensiController::class, 'getDataIzin'])->name('absensi.izin.getData');
        //         Route::get('/izin/getKerja',[AbsensiController::class, 'getJamKerja'])->name('absensi.izin.getJamKerja');
        //         Route::post('/izin/submit',[AbsensiController::class, 'submitIzin'])->name('absensi.izin.submit');
        //         Route::post('/izin/getAttachmentUrl', [AbsensiController::class, 'getAttachmentUrl']);
        //         Route::post('/izin/destroy', [AbsensiController::class, 'destroyIzin']);

        //     Route::middleware(['check.jabatan:IzinPageApprover'])->group(function(){

        //         Route::post('/izin/confirmIzin',[AbsensiController::class, 'confirmIzin'])->name('absensi.izin.confirm');
        //         Route::get('/izin/getDataIzinDH',[AbsensiController::class, 'getDataIzinDH'])->name('absensi.izin.getDataDH');
        //         Route::get('/izinLevelUp',[AbsensiController::class, 'indexIzinDH'])->name('absensi.izinDH');
        //     });
        //     });
        // });
    // Absensi


    Route::middleware(['auth', 'check.jabatan:IzinPage,IzinPageApprover, IzinPageAdmin'])->group(function() {
        // Route umum untuk kedua jabatan
        Route::get('/lembur', [AbsensiController::class, 'indexLembur'])->name('absensi.lembur');
        Route::post('/izin/getAttachmentUrl', [AbsensiController::class, 'getAttachmentUrl']);

        // Route khusus IzinPage
        Route::middleware(['check.jabatan:IzinPage'])->group(function() {
            Route::get('/izin', [AbsensiController::class, 'indexIzin'])->name('absensi.izin');
            Route::get('/izin/getDataIzin', [AbsensiController::class, 'getDataIzin'])->name('absensi.izin.getData');
            Route::get('/izin/getKerja', [AbsensiController::class, 'getJamKerja'])->name('absensi.izin.getJamKerja');
            Route::post('/izin/submit', [AbsensiController::class, 'submitIzin'])->name('absensi.izin.submit');
            Route::post('/izin/destroy', [AbsensiController::class, 'destroyIzin']);
            Route::get('/izin/getExport', [AbsensiController::class, 'getExport'])->name('absensi.izin.export');
        });

        Route::middleware(['check.jabatan:IzinPageAdmin'])->group(function() {
            Route::get('/izinAdmin', [AbsensiController::class, 'indexIzinAdmin'])->name('absensi.izinAdmin');
            Route::get('/izin/getDataIzinAdmin', [AbsensiController::class, 'getDataIzinAdmin'])->name('absensi.izin.getDataAdmin');
            Route::get('/izin/getExportAdmin', [AbsensiController::class, 'getExportAdmin'])->name('absensi.izin.exportAdmin');

        });

        // Route khusus IzinPageApprover
        Route::middleware(['check.jabatan:IzinPageApprover'])->group(function() {
            Route::post('/izin/confirmIzin', [AbsensiController::class, 'confirmIzin'])->name('absensi.izin.confirm');
            Route::get('/izin/getDataIzinDH', [AbsensiController::class, 'getDataIzinDH'])->name('absensi.izin.getDataDH');
            Route::get('/izinLevelUp', [AbsensiController::class, 'indexIzinDH'])->name('absensi.izinDH');
            Route::get('/izin/getExportDH', [AbsensiController::class, 'getExportDH'])->name('absensi.izin.exportDH');
        });
    });



    Route::middleware(['auth','check.jabatan:absensiPage'])->group(function(){
        Route::get('/absensiSales',[AbsensiController::class, 'indexAbsensi'])->name('absensi.absensi');
        Route::get('/absensi/getUserShift',[AbsensiController::class, 'getUserShift'])->name('absensi.shift');
        Route::get('/absensi/getDataToday',[AbsensiController::class, 'getDataToday'])->name('absensi.today');
        Route::post('/absensi/submitAbsen',[AbsensiController::class, 'submitAbsen'])->name('absensi.submit');
    });
    Route::post('/addLembur',[AbsensiController::class, 'addLembur'])->name('absensi.lembur.add');

    Route::get('/parseResume',[parseResume::class, 'index'])->name('parseResume');
    Route::post('/parseResume/preview',[parseResume::class, 'preview'])->name('parseResume.preview');
    Route::post('/parseResume/submit',[parseResume::class, 'submit'])->name('parseResume.submi');

    Route::middleware(['auth','check.jabatan:addKaryawanTeam'])->group(function(){

    Route::get('/add-karyawan-team',[karyawanTeamController::class, 'index'])->name('karyawanTeam.index');
    Route::get('/add-karyawan-team/getData',[karyawanTeamController::class, 'getData'])->name('karyawanTeam.getData');
    Route::get('/add-karyawan-team/getAllData',[karyawanTeamController::class, 'getDataAll'])->name('karyawanTeam.getDataAll');
    Route::post('/add-karyawan-team/simpanApproval',[karyawanTeamController::class, 'simpanApproval'])->name('karyawanTeam.simpanApproval');
    Route::post('/add-karyawan-team/simpanTeam',[karyawanTeamController::class, 'simpanTeam'])->name('karyawanTeam.simpanTeam');
    Route::post('/add-karyawan-team/simpanUser',[karyawanTeamController::class, 'simpanUser'])->name('karyawanTeam.simpanUser');
    Route::post('/add-karyawan-team/kirimUlangUser',[karyawanTeamController::class, 'kirimUlangUser'])->name('karyawanTeam.kirimUlangUser');
    });

    // user dashboard
    Route::get('/uDash',[uDashController::class, 'index'])->name('Udash.index');
    Route::get('/uDash/getDateAbsen',[uDashController::class, 'getDateAbsen'])->name('Udash.dateAbsen');
    Route::get('/uDash/getDataShift',[uDashController::class, 'getDataShift'])->name('Udash.dataShift');
    Route::get('/uDash/getChartData',[uDashController::class, 'getChartData'])->name('Udash.chartData');
    Route::get('/uDash/getAllLembur',[uDashController::class, 'getAllLembur'])->name('Udash.allLembur');
    Route::get('/uDash/getAllIzin',[uDashController::class, 'getAllIzin'])->name('Udash.allIzin');
    Route::get('/uDash/getRiwayatAbsen',[uDashController::class, 'getRiwayatAbsen'])->name('Udash.riwayatAbsen');
    Route::get('/uDash/getAbsenDetail',[uDashController::class, 'getAbsenDetail'])->name('Udash.absenDetail');
    Route::get('/uDash/getChartLembur',[uDashController::class, 'getChartLembur'])->name('Udash.chartLembur');
    Route::get('/uDash/getRingkasanTeam',[uDashController::class, 'getRingkasanTeam'])->name('Udash.getRingkasanTeam');

    // //ROUTE GROUP AKSES Shift Management
    // Route::middleware(['auth','check.jabatan:ShiftManagement'])->group(function(){
    //     //Shift
    //     Route::get('/userShift',[ShiftController::class, 'index'])->name('shift.index');
    //     Route::get('/getWeek',[ShiftController::class, 'getWeek'])->name('shift.getWeek');
    //     Route::post('/userShift/submit',[ShiftController::class, 'submit'])->name('shift.submit');
    //     Route::post('/userShift/update',[ShiftController::class, 'update'])->name('shift.update');
    //     Route::get('/shift/getExport',[ShiftController::class, 'getExport'])->name('shift.export');
    //     Route::get('/shift/getShift',[ShiftController::class, 'getShift'])->name('shift.getShift');
    //     Route::get('/shift/isLembur',[ShiftController::class, 'isLembur'])->name('shift.isLembur');

    // });

    // Route::middleware(['auth','check.jabatan:ShiftManagementAdmin'])->group(function(){
    //     Route::get('/userShiftAdmin',[ShiftController::class, 'indexAdmin'])->name('shift.indexAdmin');
    //     Route::get('/getWeekAdmin',[ShiftController::class, 'getWeekAdmin'])->name('shift.getWeekAdmin');
    //     Route::post('/userShift/submit',[ShiftController::class, 'submit'])->name('shift.submit');
    //     Route::post('/userShift/update',[ShiftController::class, 'update'])->name('shift.update');
    //     Route::get('/shift/getExport',[ShiftController::class, 'getExport'])->name('shift.export');
    //     Route::get('/shift/getShift',[ShiftController::class, 'getShift'])->name('shift.getShift');
    //     Route::get('/shift/isLembur',[ShiftController::class, 'isLembur'])->name('shift.isLembur');
    // });

    Route::middleware(['auth', 'check.jabatan:ShiftManagement,ShiftManagementAdmin'])->group(function(){
        // Route yang bisa diakses oleh kedua jabatan
        Route::post('/swapShift/submit', [ShiftController::class, 'submit'])->name('shift.submit');
        Route::post('/swapShift/update', [ShiftController::class, 'update'])->name('shift.update');
        Route::get('/swapShift/getExport', [ShiftController::class, 'getExport'])->name('shift.export');
        Route::get('/swapShift/getShift', [ShiftController::class, 'getShift'])->name('shift.getShift');
        Route::get('/swapShift/isLembur', [ShiftController::class, 'isLembur'])->name('shift.isLembur');

        // Route khusus ShiftManagement
        Route::middleware(['check.jabatan:ShiftManagement'])->group(function(){
            Route::get('/swapShift', [ShiftController::class, 'index'])->name('shift.index');
            Route::get('/swapShift/getWeek', [ShiftController::class, 'getWeek'])->name('shift.getWeek');
        });

        // Route khusus ShiftManagementAdmin
        Route::middleware(['check.jabatan:ShiftManagementAdmin'])->group(function(){
            Route::post('/swapShift/submitAdmin', [ShiftController::class, 'submitAdmin'])->name('shift.submitAdmin');
            Route::post('/swapShift/updateAdmin', [ShiftController::class, 'updateAdmin'])->name('shift.updateAdmin');
            Route::get('/swapShiftAdmin', [ShiftController::class, 'indexAdmin'])->name('shift.indexAdmin');
            Route::get('/swapShift/getWeekAdmin', [ShiftController::class, 'getWeekAdmin'])->name('shift.getWeekAdmin');
        });
    });




    // Route::middleware(['auth','check.jabatan:OvertimeManagementAdmin'])->group(function(){
    //     Route::get('/getUserActiveAll',[OvertimeController::class, 'userActiveAll'])->name('overtime.userActiveAdmin');
    //     Route::get('/overtimeAdmin',[OvertimeController::class, 'indexAdmin'])->name('overtime.indexAdmin');
    //     Route::get('/getWaktuShift',[OvertimeController::class,'getWaktuShift'])->name('overtime`.getWaktuShift');
    //     Route::post('/overtime/submit',[OvertimeController::class, 'submit'])->name('overtime.submit');
    //     Route::post('/destroyOvertime', [OvertimeController::class, 'destroy'])->name('overtime.destroy');
    //     Route::post('/destroyUserOvertime', [OvertimeController::class, 'destroyUser'])->name('overtime.destroyUser');
    //     Route::get('/getOffset',[OvertimeController::class, 'getOffset'])->name('overtime.offset');
    //     Route::get('/getDetails',[OvertimeController::class, 'getDetails'])->name('overtime.Details');
    //     Route::get('/getExportAdmin',[OvertimeController::class, 'getExportAdmin'])->name('overtime.exportAdmin');
    //     Route::get('/overtime/getShift',[OvertimeController::class, 'getShift'])->name('overtime.getShift');
    // });

    // Route::middleware(['auth','check.jabatan:OvertimeManagement'])->group(function(){
    //     // OVERTIME
    //     Route::get('/overtime',[OvertimeController::class, 'index'])->name('overtime.index');
    //     Route::get('/getUserActive',[OvertimeController::class, 'userActive'])->name('overtime`.userActive');
    //     Route::get('/getWaktuShift',[OvertimeController::class,'getWaktuShift'])->name('overtime`.getWaktuShift');
    //     Route::post('/overtime/submit',[OvertimeController::class, 'submit'])->name('overtime.submit');
    //     Route::post('/destroyOvertime', [OvertimeController::class, 'destroy'])->name('overtime.destroy');
    //     Route::post('/destroyUserOvertime', [OvertimeController::class, 'destroyUser'])->name('overtime.destroyUser');
    //     Route::get('/getOffset',[OvertimeController::class, 'getOffset'])->name('overtime.offset');
    //     Route::get('/getDetails',[OvertimeController::class, 'getDetails'])->name('overtime.Details');
    //     Route::get('/getExport',[OvertimeController::class, 'getExport'])->name('overtime.export');
    //     Route::get('/overtime/getShift',[OvertimeController::class, 'getShift'])->name('overtime.getShift');

    // });

Route::middleware(['auth', 'check.jabatan:OvertimeManagement,OvertimeManagementAdmin'])->group(function() {
    // Route yang bisa diakses oleh kedua jabatan
    Route::get('/overtime/getWaktuShift', [OvertimeController::class, 'getWaktuShift'])->name('overtime.getWaktuShift');
    Route::post('/overtime/submit', [OvertimeController::class, 'submit'])->name('overtime.submit');
    Route::post('/overtime/destroyOvertime', [OvertimeController::class, 'destroy'])->name('overtime.destroy');
    Route::post('/overtime/destroyUserOvertime', [OvertimeController::class, 'destroyUser'])->name('overtime.destroyUser');
    Route::get('/getOffset', [OvertimeController::class, 'getOffset'])->name('overtime.offset');
    Route::get('/overtime/getDetails', [OvertimeController::class, 'getDetails'])->name('overtime.Details');
    Route::get('/overtime/getShift', [OvertimeController::class, 'getShift'])->name('overtime.getShift');

    // Route khusus OvertimeManagementAdmin
    Route::middleware(['check.jabatan:OvertimeManagementAdmin'])->group(function() {
        Route::post('/overtime/submitAdmin', [OvertimeController::class, 'submitAdmin'])->name('overtime.submitAdmin');
        Route::get('/overtime/getUserActiveAll', [OvertimeController::class, 'userActiveAll'])->name('overtime.userActiveAdmin');
        Route::get('/overtimeAdmin', [OvertimeController::class, 'indexAdmin'])->name('overtime.indexAdmin');
        Route::get('/overtime/getExportAdmin', [OvertimeController::class, 'getExportAdmin'])->name('overtime.exportAdmin');
    });

    // Route khusus OvertimeManagement
    Route::middleware(['check.jabatan:OvertimeManagement'])->group(function() {
        Route::get('/overtime', [OvertimeController::class, 'index'])->name('overtime.index');
        Route::get('/overtime/getUserActive', [OvertimeController::class, 'userActive'])->name('overtime.userActive');
        Route::get('/overtime/getExport', [OvertimeController::class, 'getExport'])->name('overtime.export');
    });
});




});


Route::post('/scheduler-T4sk-RuNn3r-sEcr3t', function (Request $request) {
    // 1. Verifikasi token menggunakan helper config()
    if ($request->bearerToken() !== env('SCHEDULER_BEARER_TOKEN')) {
        Log::warning('Unauthorized scheduler execution attempt.');
        abort(403, 'Unauthorized');
    }

    // 2. Jalankan scheduler dalam blok try-catch untuk error handling
    try {
        Artisan::call('schedule:run');
        Log::info('Scheduler executed successfully.');
        return response('Scheduler executed successfully.', 200);

    } catch (\Exception $e) {
        Log::error('Scheduler execution failed.', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response('Scheduler execution failed.', 500);
    }
});

Route::get('/cek-time', function () {
    return [
        'now' => now()->format('Y-m-d H:i:s'),
        'timezone' => config('app.timezone'),
        'php_timezone' => date_default_timezone_get(),
        'server' => gethostname(),
    ];
});



Route::get('/test-db', function () {
try {
    // Coba paksa koneksi ke database default
    DB::connection()->getPdo();

    // Jika baris di atas tidak error, koneksi berhasil
    return '<h1 style="color:green;">✅ SUKSES! Koneksi ke database berjalan dengan baik.</h1>';

} catch (\Exception $e) {
    // Jika ada error saat koneksi, tampilkan pesannya
    return '<h1 style="color:red;">❌ GAGAL! Tidak bisa terhubung ke database.</h1><p>Error: ' . $e->getMessage() . '</p>';
}
});
