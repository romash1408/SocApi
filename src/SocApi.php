<?php

namespace SocApi;

use SocApi\Post;
use SocApi\Social;

class SocApi
{
    const
        STATUS_WAITING = 0,
        STATUS_CONFIRMED = 1,
        STATUS_UNCONFIRMED = 2;

    private $db;
    private $socials = [];
    
    public function __construct(\mysqli $db)
    {
        $this->db = $db;
    }
    
    public function getPost($options, $limit = 1)
    {
        return Post::get($this->db, $options, $limit);
    }

    private function addPost($data)
    {
        return Post::add($this->db, $data);
    }
    
    public function getManyPosts($limit = null, $status = null, $from = null, &$last = null, $userKey = null)
    {
        return Post::getMany($this->db, $limit, $status, $from, $last, $userKey);
    }
    
    public function addSocial(Social $add)
    {
        if (!in_array($add, $this->socials)) {
            $this->socials[] = $add;
        }
    }

    public function search($tags)
    {
        $ret = [];
        foreach ($this->socials as $social) {
            foreach ($social->search($tags) as $post) {
                $ret[] = $this->addPost($post);
            }
        }
        return $ret;
    }
    
    public static function arrayToSql($sql, $array, $escape_func, $filter = null)
    {
        if (is_array($filter)) {
            $tmp = [];
            foreach ($filter as $key) {
                if (\array_key_exists($key, $array)) {
                    $tmp[$key] = $array[$key];
                }
            }
            $array = $tmp;
        }

        if (count($array) < 1) {
            return "";
        }

        $def = [
            "sql" => "WHERE",
            "operand" => "=",
            "concater" => "AND",
        ];

        if (is_array($sql)) {
            $def = array_merge($def, $sql);
            $sql = $def["sql"];
        }
        
        
        $concater = "";
        foreach ($array as $field => $value) {
            $operand = $def["operand"];
            
            if (!is_array($value)) {
                $sql .= " $concater `$field` $operand '" . $escape_func($value) . "'";
                $concater = $def["concater"];
                continue;
            }
            
            if (isset($value["operand"])) {
                $operand = $value["operand"];
            }
            if (isset($value["concater"])) {
                $concater = $value["concater"];
            }
            switch ($value["type"]) {
                case "processed":
                    $sql .= " $concater `$field` $value[value]";
                    break;
                default:
            }
            $concater = $def["concater"];
        }
        
        return $sql;
    }
}
