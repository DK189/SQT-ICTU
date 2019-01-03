<?php
class JsonObject extends StdClass implements JsonSerializable {
    public function jsonSerialize () {
        $reflect = new ReflectionObject($this);
        $props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);

        $JSON = [];

        foreach ($props as $prop) {
            $JSON[$prop->getName()] = $prop->getValue($this);
        }
        return $JSON;
    }

}
?>
