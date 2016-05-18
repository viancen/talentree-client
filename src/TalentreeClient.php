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

    //What part of Talentree is used for filters
    public $filter_settings = [];

    //Root url of API
    public $root;

    //not used yet
    public $debug = false;

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
        if (!$root) throw new Talentree_Error('You must provide a Talentree Root path');

        $this->apiKey = $apikey;
        $this->root = $root;

        if (!empty($options)) {
            if (!empty($options['settings'])) {
                $this->settings = $options['settings'];
            }
            if (!empty($options['filter_settings'])) {
                $this->filter_settings = $options['filter_settings'];
            }
        }

        $this->client = new Client();

        $this->root = rtrim($this->root, '/') . '/';

    }

    public function getSettings()
    {
        return $this->settings;
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

        $response = $client->request('GET', $this->apiUri . 'children/' . $id, [
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
    function sortResults($data)
    {
        $defResults = [];

        foreach ($this->settings as $node) {
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
                    if (!in_array($part['id'], $data['talentree']['idList'])) continue;
                    if (!array_key_exists($part['id'], $data['talentree']['results'])) continue;

                    $data['talentree']['results'][$part['id']]['description'] = $part['description'];
                    $subResultArray['items'][] = $data['talentree']['results'][$part['id']];
                    // $data['talentree']['results'][$part['id']]['count']
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

                    unset($talent['description']);
                    $defResults[$node['name']][$subResultArray['subNode']['name']][] = [
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

        $response = $client->request('GET', $this->root . 'tree/' . $id, [
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
     * GEt all lists
     * @return Generator
     */
    function getSettingLists()
    {
        $return = [];
        foreach ($this->settings as $oneList => $label) {
            $return[$label] = $this->makeList($oneList);
        }
        return $return;
    }

    /**
     * GEt all lists
     * @return Generator
     */
    function getFiltersLists()
    {
        $return = [];
        foreach ($this->settings as $oneList => $label) {
            $return[$label] = $this->makeList($oneList);
        }

        return $return;
    }

}