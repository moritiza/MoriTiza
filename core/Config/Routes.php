<?php

namespace Core\Config;

use Core\Router;

// Add Web Routes Bellow
Router::get('index', function(){
	return 'index';
});

Router::get('signin', function(){
	return 'auth.signin';
});

Router::post('signin', 'Auth\Login@login');

Router::get('signup', function(){
	return 'auth.signup';
});

Router::post('signup', 'Auth\Register@register');
Router::get('signout', 'Auth\Logout@logout');

Router::get('password/forgot', function(){
	return 'auth.password-forgot';
});

Router::post('password/forgot', 'Auth\ForgotPassword@forgotPassword');
Router::get('password/reset', 'Auth\ResetPassword@reset');
Router::post('password/reset', 'Auth\ResetPassword@changePassword');
