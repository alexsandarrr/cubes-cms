<?php

class Application_Form_Admin_PhotoAdd extends Zend_Form
{
    public function init () {
        
        $title = new Zend_Form_Element_Text('title');
        //$firstName->addFilter(new Zend_Filter_StringTrim());
        //$firstName->addValidator(new Zend_Validate_StringLength(array('min => 3, 'max' => 255)));
        $title->addFilter('StringTrim')
                ->addValidator('StringLength', false /*ne prekidaj validaciju ostalih validatora */, array('min' => 3, 'max' => 255))
                ->setRequired(false);
        $this->addElement($title);
        
        $description = new Zend_Form_Element_Textarea('description');
        $description->addFilter('StringTrim')
                ->setRequired(false);
        $this->addElement($description);
        
        $photoUpload = new Zend_Form_Element_File('photo_upload');
        // count validator validira koliko fajlova od jednom moze da se upload-uje
        $photoUpload->addValidator('Count', true, 1)
                ->addValidator('MimeType', true, array('image/jpeg', 'image/gif', 'image/png'))
                ->addValidator('ImageSize', false, array(
                    'minwidth' => 660,
                    'minheight' => 495,
                    'maxwidth' => 5000,
                    'maxheight' => 5000
                ))
                ->addValidator('Size', false, array(
                    'max' => '10MB'
                ))
                // disable move file to destination when calling method getValues()
                ->setValueDisabled(true)
                ->setRequired(true);
        $this->addElement($photoUpload);
    }

}

