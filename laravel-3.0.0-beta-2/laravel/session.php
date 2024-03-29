<?php namespace Laravel;

class Session {

	/**
	 * The session singleton instance for the request.
	 *
	 * @var Payload
	 */
	public static $instance;

	/**
	 * The string name of the CSRF token stored in the session.
	 *
	 * @var string
	 */
	const csrf_token = 'csrf_token';

	/**
	 * Create the session payload instance and load the session for the request.
	 *
	 * @param  string  $driver
	 * @return void
	 */
	public static function start($driver)
	{
		if (Config::get('application.key') === '')
		{
			throw new \Exception("An application key is required to use sessions.");
		}

		static::$instance = new Session\Payload(static::factory($driver));
	}

	/**
	 * Create a new session driver instance.
	 *
	 * @param  string  $driver
	 * @return Driver
	 */
	public static function factory($driver)
	{
		switch ($driver)
		{
			case 'apc':
				return new Session\Drivers\APC(Cache::driver('apc'));

			case 'cookie':
				return new Session\Drivers\Cookie;

			case 'database':
				return new Session\Drivers\Database(Database::connection());

			case 'file':
				return new Session\Drivers\File(SESSION_PATH);

			case 'memcached':
				return new Session\Drivers\Memcached(Cache::driver('memcached'));

			case 'redis':
				return new Session\Drivers\Redis(Cache::driver('redis'));

			default:
				throw new \Exception("Session driver [$driver] is not supported.");
		}
	}

	/**
	 * Retrieve the active session payload instance for the request.
	 *
	 * <code>
	 *		// Retrieve the session instance and get an item
	 *		Session::instance()->get('name');
	 *
	 *		// Retrieve the session instance and place an item in the session
	 *		Session::instance()->put('name', 'Taylor');
	 * </code>
	 *
	 * @return Payload
	 */
	public static function instance()
	{
		if (static::started()) return static::$instance;

		throw new \Exception("A driver must be set before using the session.");
	}

	/**
	 * Determine if session handling has been started for the request.
	 *
	 * @return bool
	 */
	public static function started()
	{
		return ! is_null(static::$instance);
	}

	/**
	 * Magic Method for calling the methods on the session singleton instance.
	 *
	 * <code>
	 *		// Retrieve a value from the session
	 *		$value = Session::get('name');
	 *
	 *		// Write a value to the session storage
	 *		$value = Session::put('name', 'Taylor');
	 *
	 *		// Equivalent statement using the "instance" method
	 *		$value = Session::instance()->put('name', 'Taylor');
	 * </code>
	 */
	public static function __callStatic($method, $parameters)
	{
		return call_user_func_array(array(static::instance(), $method), $parameters);
	}

}