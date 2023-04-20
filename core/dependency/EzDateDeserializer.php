<?php

class EzDateDeserializer extends Deserializer
{
    public function deserialize($data)
    {
        if (is_numeric($data)) {
            return EzDate::new($data);
        } else {
            if (is_string($data)) {
                return EzDate::newFromString($data);
            } else {
                return null;
            }
        }
    }
}
