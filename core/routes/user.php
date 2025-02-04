<?php

use Illuminate\Support\Facades\Route;

Route::namespace('User\Auth')->name('user.')->group(function () {

    Route::controller('LoginController')->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login');
        Route::get('logout', 'logout')->middleware('auth')->name('logout');
    });

    Route::controller('RegisterController')->group(function () {
        Route::get('register/{reference?}', 'showRegistrationForm')->name('register');
        Route::post('register', 'register')->middleware('registration.status');
        Route::post('check-mail', 'checkUser')->name('checkUser');
    });

    Route::controller('ForgotPasswordController')->prefix('password')->name('password.')->group(function () {
        Route::get('reset', 'showLinkRequestForm')->name('request');
        Route::post('email', 'sendResetCodeEmail')->name('email');
        Route::get('code-verify', 'codeVerify')->name('code.verify');
        Route::post('verify-code', 'verifyCode')->name('verify.code');
    });

    Route::controller('ResetPasswordController')->group(function () {
        Route::post('password/reset', 'reset')->name('password.update');
        Route::get('password/reset/{token}', 'showResetForm')->name('password.reset');
    });
});

Route::middleware('auth')->name('user.')->group(function () {
    //authorization
    Route::namespace('User')->controller('AuthorizationController')->group(function () {
        Route::get('authorization', 'authorizeForm')->name('authorization');
        Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
        Route::post('verify-email', 'emailVerification')->name('verify.email');
        Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
        Route::post('verify-g2fa', 'g2faVerification')->name('go2fa.verify');
    });

    Route::middleware(['check.status'])->group(function () {

        Route::get('user-data', 'User\UserController@userData')->name('data');
        Route::post('user-data-submit', 'User\UserController@userDataSubmit')->name('data.submit');

        Route::middleware('registration.complete')->namespace('User')->group(function () {

            Route::controller('UserController')->group(function () {
                Route::get('dashboard', 'home')->name('home');

                //2FA
                Route::get('two-factor', 'show2faForm')->name('twofactor');
                Route::post('two-factor/enable', 'create2fa')->name('twofactor.enable');
                Route::post('two-factor/disable', 'disable2fa')->name('twofactor.disable');

                //KYC
                Route::get('kyc-form', 'kycForm')->name('kyc.form');
                Route::get('kyc-data', 'kycData')->name('kyc.data');
                Route::post('kyc-submit', 'kycSubmit')->name('kyc.submit');

                //Report
                Route::any('deposit/history', 'depositHistory')->name('deposit.history');

                // Transactions
                Route::get('transactions', 'transactionIndex')->name('transaction.index');

                // Referral
                Route::get('referral/commissions', 'referralCommissions')->name('referral.commissions.trade');
                Route::get('referred/users', 'myRef')->name('referral.users');

                // Chat file download
                Route::get('attachment-download/{file_hash}', 'attachmentDownload')->name('attachment.download');
            });

            //Profile setting
            Route::controller('ProfileController')->group(function () {
                Route::get('profile-setting', 'profile')->name('profile.setting');
                Route::post('profile-setting', 'submitProfile');
                Route::get('change-password', 'changePassword')->name('change.password');
                Route::post('change-password', 'submitPassword');
            });

            Route::middleware('kyc')->group(function () {

                // Advertisement
                Route::controller('AdvertisementController')->prefix('advertisement')->name('advertisement.')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('add-new', 'create')->name('new');
                    Route::post('store/{id?}', 'store')->name('store');
                    Route::get('edit/{id}', 'edit')->name('edit');
                    Route::post('status/{id}', 'updateStatus')->name('status');
                    Route::get('reviews/{id}', 'reviews')->name('reviews');
                    Route::post('delete/{id}', 'delete')->name('delete');
                });

                // Trade Request
                Route::controller('TradeController')->name('trade.request.')->group(function () {

                    Route::prefix('trades')->group(function () {
                        Route::get('running', 'running')->name('running');
                        Route::get('completed', 'completed')->name('completed');
                        Route::get('details/{id}', 'details')->name('details');
                        // Trade Request Operation
                        Route::post('cancel/{id}', 'cancel')->name('cancel');
                        Route::post('paid/{id}', 'paid')->name('paid');
                        Route::post('dispute/{id}', 'dispute')->name('dispute');
                        Route::post('release/{id}', 'release')->name('release');
                    });

                    Route::get('new-trade-request/{id}', 'newTrade')->name('new');
                    Route::post('send-trade-request/{id}', 'sendTradeRequest')->name('store');
                });

                // Trade Chat
                Route::controller('ChatController')->prefix('trade-chat')->name('chat.')->group(function () {
                    Route::post('store/{id}', 'store')->name('store');
                    Route::get('download/{tradeId}/{id}', 'download')->name('download');
                });

                // Trade Review
                Route::controller('ReviewController')->prefix('trade-review')->name('review.')->group(function () {
                    Route::post('store/{uid}', 'store')->name('store');
                });
            });

            // Withdraw
            Route::controller('WithdrawController')->prefix('withdraw')->name('withdraw')->group(function () {
                Route::get('history', 'log')->name('.history');
                Route::middleware('kyc')->group(function () {
                    Route::get('/{crypto}', 'withdrawMoney');
                    Route::post('/', 'store')->name('.store');
                });
            });
        });

        // Wallets
        Route::middleware(['registration.complete', 'kyc'])->controller('Gateway\PaymentController')->group(function () {
            Route::get('/wallets', 'Gateway\PaymentController@wallets')->name('wallets');
            Route::get('/single-wallet/{id}/{code}', 'Gateway\PaymentController@singleWallet')->name('wallets.single');
            Route::get('/wallets/generate/{crypto}', 'Gateway\PaymentController@walletGenerate')->name('wallets.generate');
        });
    });
});
