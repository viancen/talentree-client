<?php
use GuzzleHttp\Client;

require_once 'TalentreeClient/Exceptions.php';

class TalentreeClient
{
    private $client;

    public $apiKey;

    public $root;

    public $debug = false;

    public static $error_map = [
        "Invalid_Key" => "Talentree_Invalid_Key",
    ];

    public function __construct($apikey = null, $root = null)
    {
        if (!$apikey) throw new Talentree_Error('You must provide a Talentree API key');
        if (!$root) throw new Talentree_Error('You must provide a Talentree Root path');
        $this->apiKey = $apikey;
        $this->root = $root;

        $this->client = new Client();

        $this->root = rtrim($this->root, '/') . '/';

    }

    /**
     * Post request to Talentree.io
     *
     * @param $endpoint
     * @param array $params
     * @return mixed
     */
    public function post($endpoint, $params = [])
    {

        try {
            $response = $this->client->request('POST', $this->root . $endpoint, [
                'headers' => [
                    'X-Authorization' => $this->apiKey,
                    'X-response-type' => 'json',
                    'Content-Type' => 'application/json',
                ],
                'decode_content' => true,
                'verify' => false,
                'body' => json_encode($params)
            ]);

        } catch (Talentree_HttpError $error) {
            return [
                'code' => $error->getCode(),
                'message' => $error->getMessage()
            ];
        }

        $body = json_decode($response->getBody(), true);
        return $body;
    }

    /**
     * Post request to Talentree.io
     *
     * @param $endpoint
     * @return mixed
     */
    public function get($endpoint)
    {
        print $this->apiKey;
        try {
            $response = $this->client->request('GET', $this->root . $endpoint, [
                'headers' => [
                    'X-Authorization' => $this->apiKey,
                    'X-response-type' => 'json',
                    'Content-Type' => 'application/json',
                ],
                'decode_content' => true,
                'verify' => false
            ]);

        } catch (Talentree_HttpError $error) {
            return [
                'code' => $error->getCode(),
                'message' => $error->getMessage()
            ];
        }

        $body = json_decode($response->getBody(), true);
        return $body;
    }

}