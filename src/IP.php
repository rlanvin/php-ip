<?

/**
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 * @link https://github.com/rlanvin/php-ip 
 */

/**
 * Abstract class to manipulate an IP address.
 *
 *
 */
abstract class IP
{
	const IP_VERSION = -1;

	/**
	 * Internal representation of the IP as a numeric format.
	 * For IPv4, this will be an int (32 bits).
	 * For IPv6, this will be a GMP ressource (128 bits big int).
	 * @var mixed
	 */
	protected $ip;

	/**
	 * Return human readable version of the IP (e.g. 127.0.0.1 or ::1)
	 */
	abstract public function humanReadable();

	/**
	 * Return numeric version of the IP
	 */
	abstract public function numeric($base);

	abstract public function bit_and($value);
	abstract public function bit_or($value);
	abstract public function plus($value);
	abstract public function minus($value);

	/**
	 * Display human readable version of the IP
	 */
	public function __toString()
	{
		return $this->humanReadable();
	}

	public function getVersion()
	{
		return static::IP_VERSION;
	}

	public function isContainedIn($block)
	{
		if ( ! $block instanceof IPBlock ) {
			// this is not pretty
			$class = sprintf('IPv%dBlock',static::IP_VERSION);
			$block = new $class($block);
		}

		return $block->contains($this);
	}

	/**
	 * Factory
	 */
	public static function create($ip, $version = null)
	{
		if ( $version == 4 || ( $version == null && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) ) {
			return new IPv4($ip);
		}
		else {
			return new IPv6($ip);
		}
	}
}