<?php
/**
 * Created by PhpStorm.
 * User: AxelDreamer
 * Date: 21.04.14
 * Time: 10:37
 */

class MessageController extends AbstractController{
    public function indexAction(){
        $this->view->form = $this->getForm();
    }
    public function sendAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $form = $this->getForm();
        if($this->getRequest()->isPost() && $form->isValid($data = $this->getRequest()->getParams())){
            $mailTable = new Application_Model_Mail_Table();
            if($this->auth->hasIdentity()){
                $userId = $this->auth->getIdentity()->User->Id;
            }else{
                $userId = null;
            }
            $mailTable->createRow(array(
                'Name' => trim(strip_tags($data['Name'])),
                'Email' => trim(strip_tags($data['Email'])),
                'Message' => trim(strip_tags($data['Message'])),
                'UserId' => $userId,
                'Date' => date('Y-m-d H:i:s'),
            ))->save();
            print json_encode(array('message' => 'Сообщение отправлено'));
        }else{
            $errors = $form->getMessages();
            print json_encode($errors);
//            print json_encode(array('message' => 'Ошибка'));
        }
    }

    /**
     * @return Zend_Form
     */
    public function getForm(){
        $form = new Zend_Form();
        $form->setAction('/message/send')
            ->setAttrib('id', 'message_form')
            ->setMethod('post');
        $name = new Zend_Form_Element_Text('Name', array('required' => true));
        $name->setLabel('Имя');
        $email = new Zend_Form_Element_Text('Email', array('required' => true));
        $email->setLabel('Email')->addValidator('EmailAddress');
        $message = new Zend_Form_Element_Textarea('Message', array('required' => true));
        $message->setLabel('Сообщение');
        $submit = new Zend_Form_Element_Submit('Submit');
        $submit->setLabel('Отправить')->setValue('Отправить');
        $form->addElements(array($name,$email,$message,$submit));
        return $form;
    }
}