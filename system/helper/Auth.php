<?php

class Auth
{

	public static function set($user, $rememberMe = false) {
		unset($user->password);
		if (SESSION_DRIVER == 'file') {
			self::setAuthFile($user, $rememberMe);
		} else if (SESSION_DRIVER == 'session') {
			self::setAuthSession($user, $rememberMe);
		}
	}

	public static function user(){
		$user = null;
		if (SESSION_DRIVER == 'file') {
			$user = self::userAuthFile();
		} else if (SESSION_DRIVER == 'session') {
			$user = self::userAuthSession();
		}
		return $user;
	}

	public static function unset() {
		$userToken = CookieApp::get(SESSION_NAME);
		SessionApp::remove(SESSION_NAME);
		CookieApp::remove(SESSION_NAME);
		try {
			$pathSession = PATH_STORAGE . "/session/$userToken";
			if ( file_exists($pathSession) ) {
				unlink($pathSession);
			}
		} catch (\Exception $e) {}
	}

	private static function userAuthSession() {
		$user = SessionApp::get(SESSION_NAME);
		$userSession = unserialize($user);
		if ($userSession) {
			return $userSession;
		}
		// check remember me
		$user = self::checkRememberMe();
		if ($user) {
			SessionApp::set(SESSION_NAME, serialize($user));
		}
		
		return $user;
	}

	private static function userAuthFile() {
		$userToken = CookieApp::get(SESSION_NAME);
		$pathSession = PATH_STORAGE . "/session/$userToken";
		if ( !$userToken || ($userToken && !file_exists($pathSession)) ) {
			return null;
		}
		$user = unserialize(read_file($pathSession));
		if ($user->expire >= current_time()) {
			return $user;
		}
		try {
			CookieApp::remove(SESSION_NAME);
			unlink($pathSession);
		} catch (\Exception $e) {}
		return null;
	}

	private static function setAuthSession($user, $rememberMe) {
		SessionApp::set(SESSION_NAME, serialize($user));
		if ($rememberMe) {
			include_once PATH_SYSTEM . '/core/crud/Model.php';
			include_once PATH_SITE . '/models/Account.php';
			$account = new Account;
			$token = gen_token();
			$account->update([
				'remember_token' => $token,
				'expire' => add_currenttime('30d')
			], [
				'id' => $user->id
			]);
			CookieApp::set(SESSION_NAME, $token, '30d');
		}
	}

	private static function setAuthFile($user, $rememberMe) {
		$token = gen_token();
		$user->expire = add_currenttime( $rememberMe ? '30d' : '1h' );
		write_file(serialize($user), PATH_STORAGE . "/session/$token");
		CookieApp::set(SESSION_NAME, $token, '30d');
	}

	private static function checkRememberMe() {
		$userToken = CookieApp::get(SESSION_NAME);
		if ($userToken) {
			include_once PATH_SYSTEM . '/core/crud/Model.php';
			include_once PATH_SITE . '/models/Account.php';
			$account = new Account;
			$user = $account->select(['id'])
				->where([
					'remember_token' => $userToken,
					'expire:>=' => current_time()
				])->first();
			if ( $user ) {
				return $user ;
			}
		}

		return null;
	}
}