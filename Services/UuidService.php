<?php
namespace Services;

class UuidService
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @var int - the length a UUID should be
     */
    const UUID_LENGTH = 32;

    /**
     * @desc creates a class that generates a UUID
     * @return $this
     */
    public function __construct()
    {
        $this->uuid = '';
        return $this;
    }

    /**
     * @desc create a new UUID
     * @return $this
     */
    public function generateUuid()
    {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil(self::UUID_LENGTH / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil(self::UUID_LENGTH / 2));
        } else {
            throw new Exception("no cryptographically secure random function available");
        }

        $uuid = substr(bin2hex($bytes), 0, self::UUID_LENGTH);

        # now format the UUID
        $formattedUuid = '';
        for ($i = 0; $i < 32; $i++) {
            $formattedUuid .= substr($uuid, $i, 1);

            # here's the hard part, we need to add dashes
            if ($i == 7) {
                $formattedUuid .= "-";
            }

            if ($i == 11) {
                $formattedUuid .= "-";
            }

            if ($i == 15) {
                $formattedUuid .= "-";
            }

            if ($i == 19) {
                $formattedUuid .= "-";
            }
        }

        $this->uuid = $formattedUuid;
        return $this;
    }

    /**
     * returns the UUID that this class generated
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
