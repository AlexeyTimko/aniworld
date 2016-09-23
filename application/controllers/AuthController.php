<?php
/**
 * Created by PhpStorm.
 * User: Timko
 * Date: 22.04.14
 * Time: 11:08
 */

class AuthController extends AbstractController{
    public function indexAction(){
        $code = $this->getRequest()->getParam('code');
        if(is_null($code)){
            return;
        }
        $result = Zend_Json::decode(file_get_contents("https://oauth.vk.com/access_token?client_id=4321104&client_secret=KYa9z4TKcEVZshd8WifL&code=$code&redirect_uri=http://ani-world.ru/auth"));
        $userTable = new Application_Model_User_Table();
        $user = $userTable->fetchRow(array('VkId = ?' => $result['user_id']));
        $userData = Application_Model_User::getVkData($result['access_token'], $result['user_id'], 'photo_50');
        $data = array(
            'VkId' => $result['user_id'],
            'Name' => $userData['first_name'].' '.$userData['last_name'],
            'Photo' => $userData['photo_50'],
        );
        if(is_null($user)){
            $user = $userTable->createRow($data);
        }else{
            $user->setFromArray($data);
        }
        $user->save();
        $authAdapter = new Zend_Auth_Adapter_DbTable ( Zend_Registry::get ( 'db' ), 'User', 'VkId', 'VkId', '?' );

        $authAdapter->setIdentity ( $user->VkId )->setCredential ( $user->VkId );
        $auth = Zend_Auth::getInstance();
        if($auth->authenticate($authAdapter)){
            $params = new stdClass();
            $params->User = $user;
            $params->access_token = $result['access_token'];
            $auth->getStorage()->write($params);
        }
    }
    public function logoutAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector('index', 'index');
    }
} 