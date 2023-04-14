<?php

class AuthController extends BaseController 
{
	public function __construct() {
		parent::__construct();
		$this->account = new Account; // khởi tạo model
	}

	public function getLogin()
    {
        $this->view->render('auth.login');
    }
    
	public function doLogin( $request ) {
		$error = [];
		if (empty($request->email)) {
			$error['email'] = 'Chưa nhập email!';
			redirect_back(['error' => $error]);
		}
		if (empty($request->password)) {
			$error['password'] = 'Chưa nhập password!';
			redirect_back(['error' => $error]);
		}

		$user = $this->account->select(['id','email','password'])
			->where([
				'email' => $request->email
			])->first();

		if ( $user ) {
			if ( password_verify($request->password, $user->password) ) {
				$user->password = '';
				Auth::set($user);
				redirect_route('home');
			}
		} 
		redirect_back(['error' => ['Đăng nhập không thành công']]);
	}

	public function getRegistry($request)
    {
		// $data = $this->account->createBulk([
		// 	[
		// 		'name' => 'asd',
		// 		"email" => "asdsdff@gmail.com",
		// 		'password_display' => '12312312',
		// 		'password' => '12312312',
		// 		'is_super' => 2
		// 	],
		// 	[
		// 		'name' => 'sd',
		// 		"email" => "dfdf@gmail.com",
		// 		'password_display' => '12312312',
		// 		'password' => '12312312',
		// 		'is_super' => 2
		// 	]
		// ]);

        $this->view->render('pages.signup');
    }

	public function doRegistry($request) {
		$error = [];
		if (empty($request->email)) {
			$error['email'] = 'Chưa nhập email!';
		}
		if (empty($request->password)) {
			$error['password'] = 'Chưa nhập password!';
		}
		
		redirect_back(['error' => $error]);
	}

	public function doLogout() {
		Auth::unset();
		return redirect_route('getLogin');
	}

	public static function isLoginAmin() {
		return SessionApp::has('user') ? true : false;
	}

	// hàm để sinh một mật khẩu
	public function genPass($pass) {
		$hash_pass = hashPass($pass);
		error_log($pass. ' : ' .$hash_pass . PHP_EOL, 3, 'hash_pass');
	}

}