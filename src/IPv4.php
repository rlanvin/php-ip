<?php

/**
 * Class to manipulate IPv4
 *
 * The address is stored as a **SIGNED** 32bit integer (because PHP doesn't support unsigned type).
 */
class IPv4 extends IP
{
	const IP_VERSION = 4;
	const MAX_INT = 0xFFFFFFFF;

	public function __construct($ip)
	{
		if ( is_int($ip) ) {
			$this->ip = $ip;
		}
		elseif ( is_float($ip) && floor($ip) == $ip ) {
			$this->ip = intval($ip);
		}
		elseif ( is_string($ip) ) {
			if ( ! ctype_print($ip) ) {
				// probably the result of inet_pton
				$this->ip = @ inet_ntop($ip);
				if ( $this->ip === false ) {
					throw new InvalidArgumentException("The binary string is not a valid IPv4 address.");
				}
				$this->ip = ip2long($this->ip);
			}
			elseif ( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
				$this->ip = ip2long($ip);
			}
			elseif ( ctype_digit($ip) ) {
				if ( $ip > 0xFFFFFFFF ) {
					throw new InvalidArgumentException("'$ip' is not a valid decimal IPv4 address.");
				}
				// convert "unsigned long" (string) to signed int (int)
				$this->ip = intval(doubleval($ip));
			}
			else {
				throw new InvalidArgumentException("'$ip' is not a valid IPv4 address.");
			}
		}
		else {
			throw new InvalidArgumentException("Unsupported argument type: '".gettype($ip)."'");
		}
	}

	public function numeric($base = 10)
	{
		if ( $base < 2 || $base > 36 ) {
			throw new InvalidArgumentException("Base must be between 2 and 36 (included).");
		}
		return base_convert(sprintf('%u',$this->ip),10,$base);
	}

	public function int()
	{
		return $this->ip;
	}

	public function humanReadable()
	{
		return long2ip($this->ip);
	}

	/**
	 * Bitwise and
	 */
	public function bit_and($value)
	{
		if ( ! $value instanceof self ) {
			$value = new self($value);
		}

		return new self($this->ip & $value->ip);
	}

	public function bit_or($value)
	{
		if ( ! $value instanceof self ) {
			$value = new self($value);
		}

		return new self($this->ip | $value->ip);
	}

	public function plus($value)
	{
		if ( ! $value instanceof self ) {
			$value = new self($value);
		}

		return new self($this->ip + $value->ip);
	}

	public function minus($value)
	{
		if ( ! $value instanceof self ) {
			$value = new self($value);
		}

		return new self($this->ip - $value->ip);
	}
}