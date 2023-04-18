<?php
class EzDateSerializer extends Serializer
{

    public function serialize($data):string
    {
        return strval($data);
    }
}
