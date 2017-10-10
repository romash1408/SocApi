<?php
namespace SocApi;

use SocialError;

class Instagram implements Social
{
    private $sapi;

    public function __construct(SocApi $sapi)
    {
        $this->sapi = $sapi;
    }

    public static function getType():string
    {
        return "inst";
    }

    public function search($tags):array
    {
        if (!is_array($tags)) {
            $tags = [$tags];
        }

        $ret = [];

        foreach ($tags as $tag) {
            $request = "https://www.instagram.com/explore/tags/$tag/?__a=1";
            $instContent = file_get_contents($request);

            if (!$instContent) {
                throw new SocialError("Error when calling instagram ($request)");
            }

            if (!($inst = json_decode($instContent))) {
                throw new SocialError("Error in instagram answer: $instContent");
            }

            if (!($posts = $inst->tag->media->nodes)) {
                throw new SocialError("Instagram content not found: <pre>" . print_r($inst, true) . "</pre>");
            }
            
            foreach ($posts as $post) {
                if ($post->is_video) {
                    continue;
                }

                if ($this->sapi->getPost(["id" => $post->code, "type" => static::getType()])) {
                    continue;
                }
                
                $request = "https://www.instagram.com/p/{$post->code}/?tagged=$tag&__a=1";

                if (!($infoContent = @file_get_contents($request))) {
                    throw new SocialError("Error when calling instagram ($request)");
                }
                if (!($info = json_decode($infoContent))) {
                    throw new SocialError("Error in instagram answer: '.$infoContent.'");
                }
                if (!($postInfo = $info->graphql->shortcode_media)) {
                    throw new SocialError("Instagram content not found: <pre>" . print_r($info, true) . "</pre>");
                }
                
                $ret[] = [
                    "id" => $post->code,
                    "type" => static::getType(),
                    "image" => $post->thumbnail_src,
                    "info" => $post->caption,
                    "username" => $postInfo->owner->username,
                    "date" => [
                        "type" => "processed",
                        "value" => "= FROM_UNIXTIME({$postInfo->taken_at_timestamp})"
                    ],
                    "author_url" => "https://www.instagram.com/" . $postInfo->owner->username, "technical" => ""
                ];
            }
        }

        return $ret;
    }
}
