<?php

namespace PHPPdf\Font;

/**
 * @author Piotr Śliwa <peter.pl7@gmail.com>
 */
class Registry
{
    private $fonts = array();

    public function register($name, $font)
    {
        if(is_array($font))
        {
            $font = new Font($font);
        }
        elseif(!$font instanceof Font)
        {
            throw new \InvalidArgumentException('Font should by type of PHPPdf\Font or array');
        }

        $this->fonts[$name] = $font;
    }

    public function get($name)
    {
        if($this->has($name))
        {
            return $this->fonts[$name];
        }

        throw new \PHPPdf\Exception\Exception(sprintf('Font "%s" is not registered.', $name));
    }

    public function has($name)
    {
        return isset($this->fonts[$name]);
    }
}