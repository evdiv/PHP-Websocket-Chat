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
                if (!$item instanceof \PDO) {
                    return $item;
                }
            }, 
        );
    }

}