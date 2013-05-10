<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form;

/**
 * Wraps errors in forms
 *
 * @author Tomi Saarinen <tomi.saarinen@rohea.com>
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FormErrorList implements FormErrorInterface, \ArrayAccess, \RecursiveIterator // , \Countable
{
    /**
     * @var FormInterface
     */
    protected $form = null;

    /**
     * @var array
     */
    protected $items = array();

    /**
     * $var integer
     */
    private $position = 0;

    private $formName = "";

    /**
     * Constructor
     *
     * Any array key in $messageParameters will be used as a placeholder in
     * $messageTemplate.
     *
     * @param FormInterface     $form           Form object
     * @param array             $errors         errors
     *
     * @see \Symfony\Component\Translation\Translator
     */
    public function __construct(FormInterface $form, array $errors = array())
    {
        //$this->form = $form;
        $this->formName = $form->getName();
        $this->items = $errors;
        $this->position = 0;
    }

    /**
     * @param mixed item
     */
    public function addItem(FormErrorInterface $item)
    {
        $this->items[] = $item;
    }

    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }


    public function hasChildren() {
        return (! empty($this->items));

        foreach($this->items as $item) {
            if ($item instanceof FormErrorList) {
                return true;
            }
        }
        return false;
        //return is_array($this->_data[$this->_position]);
    }

    public function getChildren() {
        return $this->items[$this->position];
        //print_r($this->_data[$this->_position]);

    }

    public function rewind() {
        $this->position = 0;
    }

    public function current() {
        return $this->items[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        return isset($this->items[$this->position]);
    }

    /*
    public function serialize() {
        return serialize($this->items);
    }
    public function unserialize($items) {
        $this->items = unserialize($items);
    }
    */

    public function recursiveToString($level = 0)
    {
        //return($this->form->getName()."");
        $str = '';

        if ($level > 0) {
            $str .= str_repeat(' ', $level - 4).$this->formName.":\n";
        }
        foreach ($this->items as $item) {
            if ($item instanceof FormError) {
                $str .= str_repeat(' ', $level).'ERROR: '.$item->getMessage()."\n";
            } elseif ($item instanceof FormErrorList) {
                $str .= $item->recursiveToString($level + 4);
            }
        }
        
        return $str;
    }

    public function __toString()
    {
        return $this->recursiveToString();
    }

}
