<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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

// Route::get('/absensiSales', function () {
//     return inertia('absensiSales');
// });


Route::get('/login', [AuthController::class, 'login'])->name('login')->middleware('guest');
Route::get('/mulai/27738', [AuthController::class, 'loginRe'])->name('loginRe')->middleware('guest');
Route::post('/prosesLogin', [AuthController::class, 'prosesLogin'])->name('prosesLogin');
Route::get('/resetPassword', [AuthController::class, 'reset'])->name('password.reset');
Route::post('/changeDataUser', [AuthController::class, 'changeData'])->name('change.data');
Route::post('/updatePassword', [AuthController::class, 'update'])->name('password.update');

Route::middleware(['auth'])->group(function () {
    Route::get('/tiket', [TicketController::class, 'index']);
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboardSuperUser', [DashboardController::class, 'superUser'])->name('superUser');
    Route::get('/dashboardUser', [DashboardController::class, 'user'])->name('user');

    Route::get('/master-periode', [MasterPeriodeController::class, 'index'])->name('master.periode');
    Route::get('/master-periode/display_tambah', [MasterPeriodeController::class, 'display_tambah_periode'])->name('master.periode.display_tambah');
    Route::post('master-periode/display_tambah', [MasterPeriodeController::class, 'tambah_periode'])->name('master.periode.add.data');


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

    // project
    Route::get('/project', [ProjectController::class, 'index'])->name('project.index');
    Route::get('/project/create', [ProjectController::class, 'create'])->name('project.create');
    Route::post('/storeproject', [ProjectController::class, 'store'])->name('project.store');
    Route::post('/project/edit', [ProjectController::class, 'edit'])->name('project.edit');
    Route::put('/project/{id}', [ProjectController::class, 'update'])->name('project.update');
    Route::post('/project/delete', [ProjectController::class, 'destroy'])->name('project.destroy');

    // Absensi
    Route::get('/lembur',[AbsensiController::class, 'indexLembur'])->name('absensi.lembur');
    Route::middleware(['auth','check.jabatan:absensiPage'])->group(function(){
        Route::get('/absensiSales',[AbsensiController::class, 'indexAbsensi'])->name('absensi.absensi');
        Route::get('/absensi/getUserShift',[AbsensiController::class, 'getUserShift'])->name('absensi.shift');
        Route::get('/absensi/getDataToday',[AbsensiController::class, 'getDataToday'])->name('absensi.today');
        Route::post('/submitAbsen',[AbsensiController::class, 'submitAbsen'])->name('absensi.submit');
    });
    Route::post('/addLembur',[AbsensiController::class, 'addLembur'])->name('absensi.lembur.add');

    Route::get('/parseResume',[parseResume::class, 'index'])->name('parseResume');
    Route::post('/parseResume/preview',[parseResume::class, 'preview'])->name('parseResume.preview');
    Route::post('/parseResume/submit',[parseResume::class, 'submit'])->name('parseResume.submi');


    // user dashboard
    Route::get('/uDash',[uDashController::class, 'index'])->name('Udash.index');

    //ROUTE GROUP AKSES Shift Management
    Route::middleware(['auth','check.jabatan:ShiftManagement'])->group(function(){
        //Shift
        Route::get('/userShift',[ShiftController::class, 'index'])->name('shift.index');
        Route::get('/getWeek',[ShiftController::class, 'getWeek'])->name('shift.getWeek');
        Route::post('/userShift/submit',[ShiftController::class, 'submit'])->name('shift.submit');
        Route::post('/userShift/update',[ShiftController::class, 'update'])->name('shift.update');
        Route::get('/shift/getExport',[ShiftController::class, 'getExport'])->name('shift.export');
        Route::get('/shift/getShift',[ShiftController::class, 'getShift'])->name('shift.getShift');
        Route::get('/shift/isLembur',[ShiftController::class, 'isLembur'])->name('shift.isLembur');

    });
    Route::middleware(['auth','check.jabatan:OvertimeManagement'])->group(function(){
        // OVERTIME
        Route::get('/overtime',[OvertimeController::class, 'index'])->name('overtime.index');
        Route::get('/getUserActive',[OvertimeController::class, 'userActive'])->name('overtime`.userActive');
        Route::get('/getWaktuShift',[OvertimeController::class,'getWaktuShift'])->name('overtime`.getWaktuShift');
        Route::post('/overtime/submit',[OvertimeController::class, 'submit'])->name('overtime.submit');
        Route::post('/destroyOvertime', [OvertimeController::class, 'destroy'])->name('overtime.destroy');
        Route::post('/destroyUserOvertime', [OvertimeController::class, 'destroyUser'])->name('overtime.destroyUser');
        Route::get('/getOffset',[OvertimeController::class, 'getOffset'])->name('overtime.offset');
        Route::get('/getDetails',[OvertimeController::class, 'getDetails'])->name('overtime.Details');
        Route::get('/getExport',[OvertimeController::class, 'getExport'])->name('overtime.export');
        Route::get('/overtime/getShift',[OvertimeController::class, 'getShift'])->name('overtime.getShift');

    });




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
