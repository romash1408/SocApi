<?php
namespace SocApi;

use SocialError;

class Vk implements Social
{
    private $sapi;
    private $accessToken;

    public function __construct(SocApi $sapi, string $accessToken)
    {
        $this->sapi = $sapi;
        $this->accessToken = $accessToken;
    }

    public static function getType():string
    {
        return "vk";
    }

    public function search($tags):array
    {
        if (!is_array($tags)) {
            $tags = [$tags];
        }

        $ret = [];

        foreach ($tags as $tag) {
            $request = "https://api.vk.com/method/newsfeed.search?q=%23$tag&access_token={$this->accessToken}&start_time=1483288146&v=5.65&extended=1";
            $answer = json_decode(file_get_contents($request))->response;

            $profInfo = [];
            foreach ($answer->profiles as $prof) {
                $profInfo[$prof->id] = [
                    "name" => $prof->first_name . " " . $prof_last_name,
                    "nick" => $prof->screen_name,
                    "url" => "https://vk.com/" . $prof->screen_name
                ];
            }
            foreach ($answer->groups as $group) {
                $profInfo[-$group->id] = [
                    "name" => $group->name,
                    "nick" => $group->screen_name,
                    "url" => "https://vk.com/" . $group->screen_name
                ];
            }

            foreach ($answer->items as $post) {
                if (!$post->attachments) {
                    continue;
                }
                if ($this->sapi->getPost(["id" => $post->id, "type" => "vk"])) {
                    continue;
                }
                foreach ($post->attachments as $attach) {
                    if ($attach->type == "album") {
                        $attach->type = "photo";
                        $attach->photo = $attach->thumb;
                    }

                    if ($attach->type != 'photo') {
                        continue;
                    }

                    $ret[] = [
                        "id" => $post->id,
                        "type" => "vk",
                        "image" => $attach->photo->photo_604,
                        "info" => $post->text,
                        "username" => $profInfo[$post->from_id]["name"],
                        "date" => [
                            "type" => "processed",
                            "value" => "= FROM_UNIXTIME({$post->date})"
                        ],
                        "author_url" => $profInfo[$post->from_id]["url"], "technical" => ""
                    ];
                }
            }
        }

        return $ret;
    }
}
