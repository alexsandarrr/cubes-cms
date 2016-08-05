<?php

class Application_Model_Filter_UrlSlug implements Zend_Filter_Interface
{
    public function filter($value) {
        
        //p{L} zamena za sva slova, p{N} zamena za sve brojeve (cak i japanske itd.)
        $value = preg_replace('/[^\p{L}\p{N}]/u', '-', $value);
        $value = preg_replace('/(\s+)/', '-', $value);
        $value = preg_replace('/(\-+)/', '-', $value);
        $value = trim($value, '-');
        
        return $value;
    }

}

