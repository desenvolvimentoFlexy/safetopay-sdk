<?php

namespace Safe2Pay\Models\Payment;

/**
 * Class Pix
 *
 * @package Safe2Pay\Models
 */
class Pix implements \JsonSerializable
{
    private $Expiration;

    public function getExpiration()
	{
		return $this->Expiration;
	}

	public function setExpiration($Expiration)
	{
		$this->Expiration = $Expiration;
	}

    public function jsonSerialize()
    {
        $data = array();
        if (null !== $this->Expiration) {
            $data['Expiration'] = (int) $this->Expiration;
        }
        // Cast to object so an absent expiration still encodes as {} (not [])
        return (object) $data;
    }

}