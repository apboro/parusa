<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/services')->group(function () {
    require base_path('routes/services/lifepos.php');
    require base_path('routes/services/lifepay.php');
    require base_path('routes/services/sber.php');
    require base_path('routes/services/youkassa.php');
});
