<?php
class JsonObject extends StdClass implements JsonSerializable {
    public function jsonSerialize () {
        $reflect = new ReflectionClass($this);
        $props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);

        $JSON = [];

        foreach ($props as $prop) {
            $JSON[$prop->getName()] = $this->{$prop->getName()};
        }
        return $JSON;
    }

}
?>
