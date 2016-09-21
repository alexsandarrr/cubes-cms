<?php

class Application_Form_Frontend_Contact extends Zend_Form
{
	public function init() {
		$name = new Zend_Form_Element_Text('name');
        $name->addFilter('StringTrim')
				->addFilter('StripTags')
                ->addValidator('StringLength', false /*ne prekidaj validaciju ostalih validatora */, array('min' => 3, 'max' => 255))
                ->setRequired(true);
        $this->addElement($name);
		
		$email = new Zend_Form_Element_Text('email');
        $email->addFilter('StringTrim')
				->addFilter('StripTags')
                ->addValidator('EmailAddress', false, array('domain' => false))
                ->setRequired(true);
        $this->addElement($email);
		
		$mesage = new Zend_Form_Element_Textarea('message');
        $mesage->addFilter('StringTrim')
				->addFilter('StripTags')
                ->setRequired(true);
        $this->addElement($mesage);
		
	}
}

