<?php

namespace Config;

use CodeIgniter\Config\Services as CoreServices;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends CoreServices
{
	// public static function example($getShared = true)
	// {
	//     if ($getShared)
	//     {
	//         return static::getSharedInstance('example');
	//     }
	//
	//     return new \CodeIgniter\Example();
	// }

	/**
	 * Shared file storage service.
	 *
	 * Resolves the active storage driver (local|s3) from Config\FileStorage
	 * and exposes it to callers via service('fileStorage').
	 */
	public static function fileStorage(bool $getShared = true)
	{
		if ($getShared) {
			return static::getSharedInstance('fileStorage');
		}

		return new \App\Libraries\Storage\FileStorageService(config('FileStorage'));
	}

	/**
	 * Shared secrets service.
	 *
	 * Resolves the active secret provider (aws|env) from Config\Secrets and
	 * exposes it to callers via service('secrets'). Returning a shared instance
	 * ensures the provider and the AWS SDK client are built at most once per
	 * request, and the per-request Secret_Cache is reused across callers.
	 */
	public static function secrets(bool $getShared = true)
	{
		if ($getShared) {
			return static::getSharedInstance('secrets');
		}

		return new \App\Libraries\Secrets\SecretsManagerService(config('Secrets'));
	}
}
