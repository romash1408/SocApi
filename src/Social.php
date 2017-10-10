<?php
namespace SocApi;

interface Social
{
    public static function getType():string;
    public function search($tags):array;
}
