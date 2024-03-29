<?php
namespace Chat;

trait ArrayAwareTrait
{
    /**
     * Return list of Entity's parameters
     * @return array
     */
    public function toArray()
    {
        return array_filter(get_object_vars($this), function ($item) {
                if (!is_object($item) && !is_resource($item)) {
                    return $item;
                }
            }, 
        );
    }

    /**
     * Return an Entity 
     * @return false or object
     */
    public function populate($data = array()){
        if(empty($data) || !is_array($data)){
            return $this;
        }

        $has = get_object_vars($this);
        foreach ($has as $name => $currentValue) {
            $this->$name = isset($data[$name]) ? $data[$name] : $currentValue;
        }
        return $this;
    }
}