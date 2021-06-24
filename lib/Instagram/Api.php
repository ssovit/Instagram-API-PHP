<?php

namespace Sovit\Instagram;

if (!\class_exists('\Sovit\Instagram\Api')) {
    /**
     * Instagram API Class
     */
    class Api
    {
        /** 
         * API Base url
         * @var String
         */
        const API_BASE = "https://www.instagram.com/";
        /**
         * Congfig
         * @var Array
         */
        private $_config = [];
        /**
         * Cache Engine
         *
         * @var Object
         */
        private $cacheEngine;
        /**
         * If Cached Enabled
         *
         * @var Boolean
         */
        private $cacheEnabled = false;
        /**
         * Query hash ids
         *
         * @var Array
         */
        private $query_hash = [
            "postCommentsQueryId" => "bc3296d1ce80a24b1b6e40b1e72903f5",
            "hashtagPostsQueryId" => "bd33792e9f52a56ae8fa0985521d141d",
            "profilePostsQueryId" => "02e14f6a7812a876f7d133c9555b1151",
            "postQueryId" => "3eb224d64759a46f7083d3322a2458bd",
        ];
        /**
         * Default Config
         *
         * @var Array
         */
        private $defaults = [
            "sessionid" => false,
            "user-agent"     => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36',
            "proxy-host"     => false,
            "proxy-port"     => false,
            "proxy-username" => false,
            "proxy-password" => false,
            "cache-timeout"  => 3600, // in seconds
        ];
        /**
         * Constructor
         *
         * @param array $config
         * @param boolean $cacheEngine
         */
        public function __construct($config = array(), $cacheEngine = false)
        {
            $this->_config = array_merge(['cookie-file' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'instagram.txt'], $this->defaults, $config);
            if ($cacheEngine) {
                $this->cacheEnabled = true;
                $this->cacheEngine        = $cacheEngine;
            }
        }
        /**
         * Get Tag Details
         *
         * @param string $tag
         * @return Object
         */
        public function getTag($tag = "")
        {
            if (empty($tag)) {
                throw new \Exception("Invalid Tag");
            }
            $cacheKey = 'tag-' . $tag;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $tag = urlencode($tag);
            $result = $this->remote_call(self::API_BASE . "explore/tags/{$tag}/?__a=1");
            if (isset($result->data)) {
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache-timeout']);
                }
                return $result;
            }
            return $this->failure();
        }
        /**
         * Get Tag feed
         *
         * @param string $tag
         * @param integer $count
         * @param string $after
         * @return Object
         */
        public function getTagFeed($tag = "", $count = 30, $after = '')
        {
            if (empty($tag)) {
                throw new \Exception("Invalid Tag");
            }
            $cacheKey = md5('tag-feed-' . $tag . '-' . $count . '-' . $after);
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $param = [
                "query_hash"      => $this->query_hash['hashtagPostsQueryId'],
                "variables"    => json_encode([
                    "tag_name" => $tag,
                    "first" => $count,
                    "after" => $after
                ]),
            ];
            $result = $this->remote_call(self::API_BASE . "graphql/query/?" . http_build_query($param));
            if (isset($result->data->hashtag)) {
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache-timeout']);
                }
                return $result;
            }
            return $this->failure();
        }
        /**
         * Get User
         *
         * @param string $username
         * @return Object
         */
        public function getUser($username = "")
        {
            if (empty($username)) {
                throw new \Exception("Invalid Tag");
            }
            $cacheKey = 'user-' . $username;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $username = urlencode($username);
            $result = $this->remote_call(self::API_BASE . "{$username}?__a=1");
            if (isset($result->graphql->user)) {
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache-timeout']);
                }
                return $result;
            }
            return $this->failure();
        }
        /**
         * Get User Feed by ID
         *
         * @param string $user_id
         * @param integer $count
         * @param string $after
         * @return Object
         */
        public function getUserFeedByID($user_id = "", $count = 30, $after = '')
        {
            if (empty($user_id)) {
                throw new \Exception("Invalid User ID");
            }
            $cacheKey = md5('user-' . $user_id . '-' . $count . '-' . $after);
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $param = [
                "query_hash"      => $this->query_hash['profilePostsQueryId'],
                "variables"    => json_encode([
                    "id" => $user_id,
                    "first" => $count,
                    "after" => $after

                ]),
            ];
            $result = $this->remote_call(self::API_BASE . "graphql/query/?" . http_build_query($param));
            if (isset($result->data->user)) {
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache-timeout']);
                }
                return $result;
            }
            return $this->failure();
        }
        /**
         * Get shortcode detail
         *
         * @param string $shortcode
         * @return Object
         */
        public function getShortcode($shortcode = "")
        {
            if (empty($shortcode)) {
                throw new \Exception("Invalid Shortcode");
            }
            $cacheKey = 'sortcode-' . $shortcode;
            if ($this->cacheEnabled) {
                if ($this->cacheEngine->get($cacheKey)) {
                    return $this->cacheEngine->get($cacheKey);
                }
            }
            $result = $this->remote_call(self::API_BASE . "p/{$shortcode}?__a=1");
            if (isset($result->graphql->shortcode_media)) {
                if ($this->cacheEnabled) {
                    $this->cacheEngine->set($cacheKey, $result, $this->_config['cache-timeout']);
                }
                return $result;
            }
            return $this->failure();
        }
        /**
         * Make remote calls
         *
         * @param string $url
         * @param boolean $isJson
         * @return Object/Boolean
         */
        private function remote_call($url = "", $isJson = true)
        {

            $ch      = curl_init();
            $options = [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT      => $this->_config['user-agent'],
                CURLOPT_ENCODING       => "utf-8",
                CURLOPT_AUTOREFERER    => true,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_MAXREDIRS      => 10,
                CURLOPT_HTTPHEADER     => [
                    'Referer: https://www.instagram.com',
                    "access-control-expose-headers: X-IG-Set-WWW-Claim",
                    'accept: */*',
                    'accept-encoding: gzip, deflate, br',
                    'sec-fetch-dest: empty',
                    'sec-fetch-mode: cors',
                    'sec-fetch-site: same-origin',
                    'x-ig-app-id: 936619743392459',
                    'x-ig-www-claim: hmac.AR1-yiYTI0KAovABgcl_mYe5lSWZC3Jtjc8gMfXTp8Z2t6gQ',
                    'x-requested-with: XMLHttpRequest',
                ],
                CURLOPT_COOKIEJAR      => $this->_config['cookie-file'],
                CURLOPT_COOKIEFILE => $this->_config['cookie-file'],
                CURLOPT_COOKIE => "sessionid=" . $this->_config['sessionid'],
            ];

            curl_setopt_array($ch, $options);
            if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            }
            if ($this->_config['proxy-host'] && $this->_config['proxy-port']) {
                curl_setopt($ch, CURLOPT_PROXY, $this->_config['proxy-host'] . ":" . $this->_config['proxy-port']);
                if ($this->_config['proxy-username'] && $this->_config['proxy-password']) {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->_config['proxy-username'] . ":" . $this->_config['proxy-password']);
                }
            }
            $data = curl_exec($ch);
            curl_close($ch);
            if ($isJson) {
                $data = json_decode($data);
            }
            return $data;
        }
        /**
         * Failure
         * Be a man and accept the failure
         *
         * @return void
         */
        private function failure()
        {
            /**
             * Try delete old cookie file to invalidate old cookies
             */
            @unlink($this->_config['cookie-file']);
            return false;
        }
    }
}
