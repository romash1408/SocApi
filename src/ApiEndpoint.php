<?php
namespace SocApi;

use SocApi\SocApi;

class ApiEndpoint
{
    private $sapi;
    private $options;

    public function __construct(SocApi $sapi, $options = null)
    {
        $this->sapi = $sapi;
        $this->options = [
            "maxPostsLoad" => 0,
            "user" => $_SERVER['REMOTE_ADDR'],
            "tags" => "helloWorld",
        ];

        if (is_array($options)) {
            $this->options = array_merge($this->options, $options);
        }

        if (!is_array($this->options["tags"]))
        {
            $this->options["tags"] = [ $this->options["tags"] ];
        }
    }

    public function getContent($lastPost = null)
    {
        return [
            "success" => 1,
            "posts" => $this->sapi->getManyPosts($this->options["maxPostsLoad"], Socials::STATUS_CONFIRMED, $lastPost, $lastPostReturn, $this->options["user"]),
            "last" => $lastPostReturn
        ];
    }

    public function like($ai)
    {
        $post = $this->sapi->getPost(["ai" => $ai * 1]);
        if (!$post) {
            return [
                "success" => 0,
                "message" => "Post with ai #$ai not found."
            ];
        }

        $post->like("toggle", $this->options["user"]);

        return [
            "success" => 1,
            "likes" => $post->like(),
            "liked" => $post->like("get", $_SERVER['REMOTE_ADDR'])
        ];
    }

    public function updatePost($ai, $status)
    {
        $post = $this->sapi->getPost(["ai" => $ai * 1]);
        if (!$post) {
            return [
                "success" => 0,
                "message" => "Post with ai #$ai not found."
            ];
        }

        $post->update(["status" => $status]);
        return ["success" => 1];
    }

    public function update()
    {
        $found = $this->sapi->search($this->options["tags"]);
        return [
            "success" => 1,
            "found" => count($found),
        ];
    }

    public function __invoke($data)
    {

        if (!array_key_exists("act", $data)) {
            return [
                "success" => 0,
                "message" => "Parameter \"act\" needed",
            ];
        }

        try {
            $rm = new \ReflectionMethod(self::class, $data["act"]);
            $args = [];
            foreach ($rm->getParameters() as $arg) {
                $name = $arg->getName();
                if (array_key_exists($name, $data)) {
                    $args[$name] = $data[$name];
                } else {
                    $args[$name] = $arg->getDefaultValue();
                }
            }
            return $rm->invokeArgs($this, $args);
        } catch (\ReflectionException $e) {
            return [
                "success" => 0,
                "message" => "Error, when call method \"{$data["act"]}\": " . $e->getMessage(),
            ];
        }
    }
}
