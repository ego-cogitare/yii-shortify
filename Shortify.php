<?php
namespace OpusOnline\Shortify;

use \Predis\Client;

/**
 *
 * @author Peeter
 *
 */
class Shortify
{

    /**
     * The charset we'll use by default
     */
    const CHARSET = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    /**
     * This will be prefixed to all keys
     * @var string
     */
    public $prefix = "shortify";

    /**
     * Constructor
     * @param Client $redis
     * @param string $charset
     */
    public function __construct(Client $redis, $charset = self::CHARSET)
    {
        $this->redis = $redis;
        $this->setCharacters($charset);
    }

    /**
     * The redis client we'll be using
     * @var Client
     */
    private $redis;

    /**
     * The valid character map. These characters will be used to shorten a sha1 hash
     * @var array
     */
    private $characters = array();

    /**
     * Defines base are we converting to / from
     * @var int
     */
    private $base = 0;


    /**
     * We append the actual hash to this key to create the Redis key
     * @return String
     */
    private function getHashKey() {
        return $this->prefix . ':url:hash';
    }
    /**
     * We append the actual sha1 hash to this key to create the Redis key
     * @return String
     */
    private function getSha1Key() {
        return $this->prefix . ':url:sha1';
    }

    /**
     * We use this value to store the unique_id
     * @return int
     */
    private function getUniqueKey() {
        return $this->prefix . ':url:unique_id';
    }

    /**
     * Sets the character set we're allowed to use
     * @param string $string
     */
    private function setCharacters($string)
    {

        $this->characters = str_split($string);
        $this->base = count($this->characters);
    }

    /**
     * Encodes a number using the characters array
     * @param int $number
     * @return string
     */
    private function encode($number)
    {

        if ($number == 0) {
            return $this->characters[0];
        }

        $result = array();
        while ($number > 0) {
            $result[] = $this->characters[bcmod($number, $this->base)];
            $number = bcmul(bcdiv($number, $this->base), '1', 0);

        }

        return implode(array_reverse($result));

    }

    /**
     * Decodes a string that has been encoded using our characters array back to a number
     * @param string $string
     * @return int number
     */
    private function decode($string)
    {

        $number = 0;
        foreach (str_split($string) as $character) {

            $number = bcadd(
                bcmul($number, $this->base),
                array_search($character, $this->characters)
            );

        }
        return $number;

    }

    /**
     * Shorten a route into a few letter hash
     * @param string $string
     * @internal param string $sha1
     * @return string
     */
    public function shorten($string)
    {

        $sha1 = sha1($string);

        $key = $this->getSha1Key() . ":$sha1";
        $value = json_decode($this->redis->get($key));

        if (empty($value)) {
            $value = json_decode($this->generateNewHashAndStoreIt($sha1, $string));
        }

        return $value->hash;
    }

    /**
     * Expand a hash to a the route array
     * @param string $hash
     * @throws \Exception
     * @return string
     */
    public function expand($hash)
    {

        $sha1 = $this->redis->get($this->getHashKey() . ":$hash");

        if (empty($sha1)) {
            throw new \Exception("Unknown hash $hash, cannot expand");
        }

        $value = json_decode($this->redis->get($this->getSha1Key() . ":$sha1"), true);

        if (empty($value) || empty($value['route'])) {
            throw new \Exception("Map to turn $hash into to route not found, cannot expand");
        }

        return $value["route"];

    }

    /**
     * Generates a new hash for the route and stores 2 keys (sha1 and hash) in Redis
     * @param string $sha1
     * @param string $route
     * @return mixed|string
     */
    private function generateNewHashAndStoreIt($sha1, $route)
    {

        $id = $this->redis->incr($this->getUniqueKey());
        $hash = $this->encode($id);

        $result = json_encode(
            array(
                'hash' => $hash,
                'route' => $route
            )
        );

        $this->redis->set($this->getSha1Key() . ":$sha1", $result);
        $this->redis->set($this->getHashKey() . ":$hash", $sha1);

        return $result;

    }
}
