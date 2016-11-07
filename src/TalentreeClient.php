<?php
use GuzzleHttp\Client;

require_once 'TalentreeClient/Exceptions.php';

class TalentreeClient
{
    //private client (Guzzle)
    private $client;

    //API key, get one at eazymatch-online.nl
    public $apiKey;

    //What part of Talentree is used
    public $settings = [];

    //Global talentree setting
    public $global_settings = [];

    //What part of Talentree is used for filters
    public $filter_settings = [];

    //Root url of API
    public $root;

    //Not used
    public $debug = false;

    //Todo: map all errors into comprehensible messages
    public static $error_map = [
        "Invalid_Key" => "Talentree_Invalid_Key",
    ];

    /**
     * TalentreeClient constructor.
     *
     * @param null $apikey
     * @param null $root
     * @param array $options
     */
    public function __construct($apikey = null, $root = null, $options = [])
    {
        if (!$apikey) throw new Talentree_Error('You must provide a Talentree API key');
        if (!$root) {
            $root = 'https://talentree.io/v1/';
        }

        $this->apiKey = $apikey;
        $this->root = $root;

        if (!empty($options)) {
            if (!empty($options['settings'])) {
                $this->settings = $options['settings'];
            }
            if (!empty($options['filter_settings'])) {
                $this->filter_settings = $options['filter_settings'];
            }
            if (!empty($options['global_settings'])) {
                $this->global_settings = $options['global_settings'];
            }
        }

        $this->client = new Client();

        $this->root = rtrim($this->root, '/') . '/';

    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings($data)
    {
        return $this->settings = $data;
    }

    public function getGlobalSettings()
    {
        return $this->global_settings;
    }

    public function setGlobalSettings($data)
    {
        return $this->global_settings = $data;
    }

    public function getFilterSettings()
    {
        return $this->filter_settings;
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

    /**
     * Array with filename and base64source
     *
     * @param $data
     * @return mixed
     */
    function parseResume($data)
    {
        $client = new Client();

        $response = $client->request('POST', $this->root . 'parse-resume', [
            'headers' => [
                'X-Authorization' => $this->apiKey,
                'X-response-type' => 'json',
                'Content-Type' => 'application/json',
            ],
            'decode_content' => true,
            'verify' => false,
            'body' => json_encode($data)
        ]);

        $body = json_decode($response->getBody(), true);

        if (!empty($body['data']) && $body['status'] == 'success') {
            return $body['data'];
        } else {
            return $body;
        }


    }

    /**
     * Array with filename and base64source
     *
     * @param $data
     * @return mixed
     */
    function parseJob($data)
    {
        $client = new Client();

        $response = $client->request('POST', $this->root . 'parse-job', [
            'headers' => [
                'X-Authorization' => $this->apiKey,
                'X-response-type' => 'json',
                'Content-Type' => 'application/json',
            ],
            'decode_content' => true,
            'verify' => false,
            'body' => json_encode($data)
        ]);

        $body = json_decode($response->getBody(), true);

        if (!empty($body['data']) && $body['status'] == 'success') {
            return $body['data'];
        } else {
            return $body;
        }
    }

    /**
     * gets all direct children
     *
     * @param $data
     * @return mixed
     */
    function getChildren($id)
    {

        $client = new Client();

        $response = $client->request('GET', $this->root . 'children/' . $id, [
            'headers' => [
                'X-Authorization' => $this->apiKey,
                'X-response-type' => 'json',
                'Content-Type' => 'application/json',
            ],
            'decode_content' => true,
            'verify' => false
        ]);

        $body = json_decode($response->getBody(), true);

        if (!empty($body['data']) && $body['status'] == 'success') {
            return $body['data'];
        } else {
            return $body;
        }
    }

    /**
     * Sort everything based on recognition
     *
     * @param $data
     */
    function sortResults($data, $global = false)
    {
        $defResults = [];

        if ($global === false) {
            $settings = $this->settings;
            $sList = 'talentree';
        } else {
            $settings = $this->global_settings;
            $sList = 'talentree_global';
        }

        foreach ($settings as $node) {
            $ii = 0;

            if ($node['items'] == 'all-children') {
                $node['items'] = $this->getChildren($node['id']);
            }
            $i = 0;
            foreach ($node['items'] as $subNode) {

                $subTree = $this->makeList($subNode['id']);

                $subResultArray = [
                    'node' => $node,
                    'subNode' => $subNode,
                    'items' => []
                ];

                foreach ($subTree as $part) {

                    if ($part['recognizable'] == false) continue;
                    if (!in_array($part['id'], $data[$sList]['idList'])) continue;
                    if (!array_key_exists($part['id'], $data[$sList]['results'])) continue;

                    $subResultArray['items'][] = $data[$sList]['results'][$part['id']];
                }

                if (empty($subResultArray['items'])) continue;

                uasort(
                    $subResultArray['items'],
                    function ($a, $b) {
                        return $a['count'] > $b['count'] ? -1 : 1;
                    }
                );

                $maxScore = null;
                foreach ($subResultArray['items'] as $talent) {
                    if (is_null($maxScore)) $maxScore = $talent['count'];

                    if ($maxScore > 1) {
                        $score = round((100 * $talent['count']) / $maxScore);
                    } else {
                        $score = 50;
                    }

                    $label = !empty($node['label']) ? $node['label'] : $node['name'];
                    $defResults[$label][$subResultArray['subNode']['name']][] = [
                        'item' => $talent,
                        'score' => $score,
                        'icon' => $node['icon'],
                    ];
                }
            }
        }

        return $defResults;

    }

    /**
     * Create a list
     *
     * @param $data
     * @return mixed
     */
    function makeList($id)
    {

        $client = new Client();

        $response = $client->request('GET', $this->root . 'flat-tree/' . $id, [
            'headers' => [
                'X-Authorization' => $this->apiKey,
                'X-response-type' => 'json',
                'Content-Type' => 'application/json',
            ],
            'decode_content' => true,
            'verify' => false
        ]);

        $body = json_decode($response->getBody(), true);
        if (!empty($body['data']) && $body['status'] == 'success') {
            return $body['data'];
        } else {
            return $body;
        }

    }


    /**
     * gets all direct children
     *
     * @param $data
     * @return mixed
     */
    function getItem($id)
    {

        $client = new Client();

        $response = $client->request('GET', $this->root . 'item/' . $id, [
            'headers' => [
                'X-Authorization' => $this->apiKey,
                'X-response-type' => 'json',
                'Content-Type' => 'application/json',
            ],
            'decode_content' => true,
            'verify' => false
        ]);

        $body = json_decode($response->getBody(), true);
        if (!empty($body['data']) && $body['status'] == 'success') {
            return $body['data'];
        } else {
            return $body;
        }

    }

    /**
     * Get all lists
     * @return Generator
     */
    function getSettingLists()
    {
        $return = [];
        foreach ($this->settings as $oneList => $label) {
            $return[$label['name']] = $this->makeList($oneList);
        }
        return $return;
    }

    /**
     * Get all filter lists
     * @return Generator
     */
    function getFiltersLists()
    {
        $return = [];
        foreach ($this->filter_settings as $oneList => $label) {
            if (!is_array($label)) {
                $return[$label] = $this->makeList($oneList);
            }
        }

        return $return;
    }

}