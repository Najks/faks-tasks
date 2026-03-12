use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskStatusController;
use Illuminate\Support\Facades\Route;

Route::prefix("auth")->name("auth.")->group(function () {

    // authentication
    Route::post("login", [AuthController::class, "login"])->middleware("throttle:5,1");
    Route::post("register", [AuthController::class, "register"])->middleware("throttle:5,1");

    // password reset
    Route::post('password/request', [AuthController::class, 'requestPasswordReset'])->middleware("throttle:5,1");
    Route::post('password/reset', [AuthController::class, 'resetPassword']);
    Route::post('password/reset/simple', [AuthController::class, 'resetPasswordSimple']);

});

Route::middleware('auth:sanctum')->group(function () {
    // user tasks
    Route::get('/tasks/user/{userId}', [TaskController::class, 'allTasksFromUser']);


    // user info
    Route::prefix("auth")->group(function () {
        Route::post("logout", [AuthController::class, "logout"]);
        Route::get("me", [AuthController::class, "me"]);
        Route::patch("me", [AuthController::class, "update"]);
    });

    // tasks
    Route::apiResource("tasks", TaskController::class);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);

    // subjects
    Route::apiResource("subjects", SubjectController::class);
    Route::get('/subjects/mine', [SubjectController::class, 'mine']);

    // task status
    Route::apiResource("status", TaskStatusController::class);

});
