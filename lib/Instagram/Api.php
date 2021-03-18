<?php

namespace Sovit\Instagram;

if (!\class_exists('\Sovit\Instagram\Api')) {
    class Api
    {
        const API_BASE = "https://www.instagram.com/";

        private $_config = [];

        private $cache = false;

        private $cacheEnabled = false;
        private $query_hash = [
            "user" => "003056d32c2554def87228bc3fd9668a",
            "tag" => "9b498c08113f1e09617a1703c22b2f32"
        ];

        private $defaults = [
            "sessionid" => false,
            "user-agent"     => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.75 Safari/537.36',
            "proxy-host"     => false,
            "proxy-port"     => false,
            "proxy-username" => false,
            "proxy-password" => false,
            "cache-timeout"  => 3600, // in seconds
        ];
        public function __construct($config = array(), $cacheEngine = false)
        {
            $this->_config = array_merge(['cookie_file' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'instagram.txt'], $this->defaults, $config);
            if ($cacheEngine) {
                $this->cacheEnabled = true;
                $this->cache        = $cacheEngine;
            }
            if (file_exists($this->_config['cookie_file'])) {
                @touch($this->_config['cookie_file']);
            }
        }

        public function getTag($tag = "")
        {
            if (empty($tag)) {
                throw new \Exception("Invalid Tag");
            }
            $tag = urlencode($tag);
            $result = $this->remote_call(self::API_BASE . "explore/tags/{$tag}?__a=1", 'tag-' . $tag);
            if (isset($result->graphql->hashtag)) {
                return $result;
            }
            return false;
        }

        public function getTagFeed($tag = "", $count = 30, $after = '')
        {
            if (empty($tag)) {
                throw new \Exception("Invalid Tag");
            }
            $param = [
                "query_hash"      => $this->query_hash['tag'],
                "variables"    => json_encode([
                    "tag_name" => $tag,
                    "first" => $count,
                    "after" => $after

                ]),
            ];
            $result = $this->remote_call(self::API_BASE . "graphql/query/?" . http_build_query($param), md5('tag-' . $tag . '-' . $count . '-' . $after));
            if (isset($result->data->hashtag)) {
                return $result;
            }
            return false;
        }
        public function getUser($username = "")
        {
            if (empty($username)) {
                throw new \Exception("Invalid Tag");
            }
            $username = urlencode($username);
            $result = $this->remote_call(self::API_BASE . "{$username}?__a=1", 'user-' . $username);
            if (isset($result->graphql->user)) {
                return $result;
            }
            return false;
        }

        public function getUserFeedByID($user_id = "", $count = 30, $after = '')
        {
            if (empty($user_id)) {
                throw new \Exception("Invalid User ID");
            }
            $param = [
                "query_hash"      => $this->query_hash['user'],
                "variables"    => json_encode([
                    "id" => $user_id,
                    "first" => $count,
                    "after" => $after

                ]),
            ];
            $result = $this->remote_call(self::API_BASE . "graphql/query/?" . http_build_query($param), md5('user-' . $user_id . '-' . $count . '-' . $after));
            if (isset($result->data->user)) {
                return $result;
            }
            return false;
        }
        public function getShortcode($shortcode="")
        {
            if (empty($shortcode)) {
                throw new \Exception("Invalid Shortcode");
            }
            $result = $this->remote_call(self::API_BASE . "p/{$shortcode}?__a=1", 'sortcode-' . $shortcode);
            if (isset($result->graphql->shortcode_media)) {
                return $result;
            }
            return false;
        }

        private function remote_call($url = "", $cacheKey = false, $isJson = true)
        {
            if ($this->cacheEnabled) {
                if ($this->cache->get($cacheKey)) {
                    return $this->cache->get($cacheKey);
                }
            }
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
                ],
                CURLOPT_COOKIEJAR      => $this->_config['cookie_file'],
                CURLOPT_COOKIEFILE => $this->_config['cookie_file'],
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
            if ($this->cacheEnabled) {
                $this->cache->set($cacheKey, $data, $this->_config['cache-timeout']);
            }
            return $data;
        }
    }
}
