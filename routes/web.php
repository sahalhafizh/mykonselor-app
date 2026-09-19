<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReferralRequestController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RuleManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\Auth\AdminSecurityController;
use App\Http\Controllers\Auth\AdminSessionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\RequiredPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResearchParticipationController;
use App\Http\Middleware\ProtectAssessmentAdminAccess;
use App\Http\Middleware\RequirePublishedPrivacyPolicy;
use App\Models\Article;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $latestArticles = Article::published()->latest('published_at')->limit(3)->get();

    return view('welcome', compact('latestArticles'));
})->name('welcome');

Route::get('/privasi', fn () => view('legal.privacy'))->name('legal.privacy');

Route::get('articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('articles/{article}', [ArticleController::class, 'show'])->name('articles.show');

Route::middleware('guest')->group(function () {
    Route::get('admin/login', [AdminSessionController::class, 'create'])->name('admin.login');
    Route::post('admin/login', [AdminSessionController::class, 'store'])->middleware('throttle:10,1');
    Route::get('admin/challenge', [AdminSecurityController::class, 'challenge'])->name('admin.challenge');
    Route::post('admin/challenge', [AdminSecurityController::class, 'verify'])->middleware('throttle:10,1')->name('admin.challenge.verify');
    Route::get('register', [RegisteredUserController::class, 'create'])->middleware(RequirePublishedPrivacyPolicy::class)->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware(['throttle:6,1', RequirePublishedPrivacyPolicy::class]);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:30,1');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('account/password', [RequiredPasswordController::class, 'edit'])->name('account.password.edit');
    Route::put('account/password', [RequiredPasswordController::class, 'update'])->middleware('throttle:5,1')->name('account.password.update');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('verification', fn () => view('auth.verification-notice'))->name('verification.notice');
    Route::get('research/participation', [ResearchParticipationController::class, 'show'])->name('research.participation');
    Route::post('research/participation', [ResearchParticipationController::class, 'store'])->middleware('throttle:6,1')->name('research.participation.store');

    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('throttle:15,1');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update')->middleware('throttle:5,1');

    Route::get('assessment', fn () => view('assessment.create'))->name('assessment.create')->middleware('student.verified');

    Route::get('assessment/{assessment}/result', [AssessmentController::class, 'result'])->middleware(ProtectAssessmentAdminAccess::class)->name('assessment.result');
    Route::get('assessment/{assessment}/referral', [AssessmentController::class, 'referral'])->middleware(ProtectAssessmentAdminAccess::class)->name('assessment.referral');
    Route::post('assessment/{assessment}/referral', [AssessmentController::class, 'storeReferralRequest'])->name('assessment.referral.store')->middleware('throttle:10,1');
    Route::get('history', [AssessmentController::class, 'history'])->name('assessment.history');

});

Route::middleware(['auth', 'active', 'admin'])->group(function () {
    Route::get('admin/security', [AdminSecurityController::class, 'show'])->name('admin.security');
    Route::post('admin/security', [AdminSecurityController::class, 'confirm'])->middleware('throttle:5,1')->name('admin.security.confirm');
});

Route::middleware(['auth', 'active', 'admin', 'admin.mfa'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('rules', [RuleManagementController::class, 'index'])->name('rules.index');
    Route::get('rules/{disease}', [RuleManagementController::class, 'edit'])->name('rules.edit');
    Route::put('rules/{disease}', [RuleManagementController::class, 'update'])->name('rules.update');
    Route::get('rules/{disease}/audit', [RuleManagementController::class, 'auditLog'])->name('rules.audit');

    Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::patch('users/{user}/verify', [UserManagementController::class, 'verifyIdentity'])->middleware('throttle:10,1')->name('users.verify');
    Route::patch('users/{user}/research/revoke', [UserManagementController::class, 'revokeResearch'])->middleware('throttle:10,1')->name('users.research.revoke');
    Route::patch('users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::patch('users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password')->middleware('throttle:5,1');
    Route::delete('users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy')->middleware('throttle:5,1');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel')->middleware('throttle:5,1');
    Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf')->middleware('throttle:5,1');

    Route::get('referrals', [ReferralRequestController::class, 'index'])->name('referrals.index');
    Route::get('referrals/{referralRequest}', [ReferralRequestController::class, 'show'])->name('referrals.show');
    Route::patch('referrals/{referralRequest}', [ReferralRequestController::class, 'update'])->name('referrals.update');

    Route::resource('articles', AdminArticleController::class)->except(['show']);
});
