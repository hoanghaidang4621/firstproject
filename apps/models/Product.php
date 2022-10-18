<?php

namespace testproject\Models;

use InvalidArgumentException;
use Phalcon\Mvc\Model;

class Product extends Model
{
    protected $product_id;

    protected $name;

    protected $price;

    public function getProductId()
    {
        return $this->product_id;
    }
    public function getPrice()
    {
        return $this->price;
    }
    public function getName()
    {
        return $this->name;
    }
    public function setName($name)
    {
        // The name is too short?
        if (strlen($name) < 10) {
            throw new InvalidArgumentException(
                'The name is too short'
            );
        }

        $this->name = $name;
    }
    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Product[]|Product
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Product
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}