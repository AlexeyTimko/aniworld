<?php
/**
 * Created by PhpStorm.
 * User: AxelDreamer
 * Date: 21.04.14
 * Time: 10:03
 */

class Application_Model_User extends Zend_Db_Table_Row_Abstract{
    protected $_table = 'User';
    protected $_tableClass = 'Application_Model_User_Table';

    /**
     * @param $access_token
     * @param string $user
     * @param string $fields
     * sex, bdate, city, country, photo_50, photo_100, photo_200_orig,
     * photo_200, photo_400_orig, photo_max, photo_max_orig, online,
     * online_mobile, lists, domain, has_mobile, contacts, connections,
     * site, education, universities, schools, can_post, can_see_all_posts,
     * can_see_audio, can_write_private_message, status, last_seen,
     * common_count, relation, relatives, counters,screen_name
     * @return mixed
     */
    public static function getVkData($access_token, $user='', $fields = ''){
        $data = Zend_Json::decode(file_get_contents("https://api.vk.com/method/users.get?access_token=$access_token&user_ids=$user&fields=$fields&name_case=nom"));
        return $data['response'][0];
    }
}