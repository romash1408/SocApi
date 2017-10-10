<?php
namespace SocApi;

use SocApi\SocApi;

class Post
{
    private static $fields = [
        "ai" => ["type" => "int"],
        "id" => ["type" => "str"],
        "type" => ["type" => "str"],
        "image" => ["type" => "str"],
        "info" => ["type" => "str"],
        "username" => ["type" => "str"],
        "date" => ["type" => "date"],
        "author_url" => ["type" => "str"],
        "status" => ["type" => "int"],
    ];

    private $db;

    private function __construct(\mysqli $db, $data)
    {
        foreach (self::$fields as $key => $opts) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $this->{$key} = $data[$key];
            $this->db = $db;
        }
    }
    
    public function update($data)
    {
        foreach ($data as $key => $value) {
            if (!isset($this->{$key}) || $this->{$key} == $value) {
                continue;
            }
            $this->db->query("
                UPDATE `socapi_post`
                SET `$key` = '" . $this->db->real_escape_string($value)."'
                WHERE `ai` = {$this->ai}
            ");
            if (!$this->db->error) {
                $this->{$key} = $value;
            }
        }
    }
    
    public static function get(\mysqli $db, $options, $limit = 1)
    {
        if ($limit <= 0) {
            $limit = "";
        } else {
            $limit = "LIMIT $limit";
        }

        $where = SocApi::arrayToSql("WHERE", $options, function($str) use($db) {
            return $db->real_escape_string($str);
        }, [
            "ai", "id", "type", "date", "username", "status"
        ]);
        
        $data = $db->query("
            SELECT *
            FROM `socapi_post`
            $where
            $limit
        ");

        if ($limit == "LIMIT 1") {
            return (($data = $data->fetch_assoc()) ?
                new static($db, $data) :
                null
            );
        } else {
            $ret = [];
            while ($next = $data->fetch_assoc()) {
                $ret[] = new static($db, $next);
            }
            return $ret;
        }
    }
    
    public static function getMany(\mysqli $db, $limit = null, $status = null, $from = null, &$last = null, $userKey = null)
    {
        $ret = [];
        $where = "WHERE 1";
        $limitStr = "";
        if ($limit != null) {
            $limitStr = "LIMIT " . (1 * $limit + 1);
        }
        if ($from != null) {
            $where .= " AND CONCAT(`date`, `ai`) < '" . $db->real_escape_string($from) . "'";
        }
        if ($status != null) {
            $where .= " AND `status` = " . ($status * 1);
        }

        $data = $db->query("
            SELECT *
            FROM `socapi_post`
            $where
            ORDER BY `date` DESC, `ai` $limitStr
        ");
        while ($next = $data->fetch_assoc()) {
            if ($limit && count($ret) >= $limit) {
                $last = $ret[$limit - 1]->date . $ret[$limit - 1]->ai;
                return $ret;
            }
            $next = $ret[] = new static($db, $next);
            $next->likes = $next->like($db);
            if ($userKey !== null) {
                $next->liked = $next->like($db, "get", $userKey);
            }
        }
        $last = "end";
        return $ret;
    }
    
    public static function add(\mysqli $db, $data)
    {
        $sql = "
            INSERT INTO `socapi_post`
            " . SocApi::arrayToSql(["sql" => "SET", "concater" => ","], $data, function($str) use($db) {
                return $db->real_escape_string($str);
            }, [
                "id", "type", "image", "info", "username", "date", "author_url", "status"
            ]) . "
        ";
        $db->query($sql);
        return static::get($db, ["ai" => $db->insert_id]);
    }

    public function like($method = "get", $userKey = null)
    {
        if ($userKey === null) {
            switch ($method) {
                case "get":
                    return $this->db->query("
                        SELECT * FROM `socapi_like`
                        WHERE `post_ai` = {$this->ai} AND `liked`
                    ")->num_rows;
                break;
                case "empty":
                    $this->db->query("
                        UPDATE `socapi_like`
                        SET `liked` = 0
                        WHERE `post_ai` = {$this->ai}
                    ");
            }
        } else {
            $userKey = $db->real_escape_string($userKey);
            if (!$this->db->query("
                SELECT *
                FROM `socapi_like`
                WHERE `post_ai` = {$this->ai} AND `user` = '$userKey'
            ")->num_rows) {
                $this->db->query("
                    INSERT INTO `socapi_like`
                    SET `post_ai` = {$this->ai}, `user` = '$userKey'
                ");
            }
            
            switch ($method) {
                case "get":
                    return $this->db->query("
                        SELECT `liked`
                        FROM `socapi_like`
                        WHERE `post_ai` = {$this->ai} AND `user` = '$userKey'
                    ")->fetch_assoc()["liked"] && true;
                break;
                case "toggle":
                    $this->db->query("
                        UPDATE `socapi_like`
                        SET `liked` = NOT(`liked`)
                        WHERE `post_ai` = {$this->ai} AND `user` = '$userKey'
                    ");
                    break;
                case "on":
                    $this->db->query("
                        UPDATE `socapi_like`
                        SET `liked` = 1
                        WHERE `post_ai` = {$this->ai} AND `user` = '$userKey'
                    ");
                    break;
                case "off":
                    $this->db->query("
                        UPDATE `socapi_like`
                        SET `liked` = 0
                        WHERE `post_ai` = {$this->ai} AND `user` = '$userKey'
                    ");
                    break;
            }
        }
    }
}
