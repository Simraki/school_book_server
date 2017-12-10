<?php

require_once 'DBOperations.php';

class Functions
{
    
    private $db;
    
    public function __construct()
    {
        
        $this->db = new DBOperations();
        
    }
    
    
    public function registerUser($user)
    {
        
        $db = $this->db;
        
        if (!empty($user->name)
            && !empty($user->surname)
            && !empty($user->type)
            && !empty($user->birthDate)
            && !empty($user->email)
            && !empty($user->password)) {
            
            if ($db->checkUserExist($user->email)) {
                
                $response["result"]  = "fail";
                $response["message"] = "Такой пользователь уже есть";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
                
            } else {
                if ($user->type == 1 || $user->type == 2  || $user->type == 3) {
                    $result = $db->register($user);
                } else {
                    $result = false;
                }
                
                if ($result) {
                    
                    $response["result"] = "success";
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                    
                } else {
                    
                    $response["result"]  = "fail";
                    $response["message"] = "Ошибка при регистрации";
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                    
                }
            }
        } else {
            return $this->getMsgParamNotEmpty();
        }
    }
    
    public function loginUser($email, $password)
    {
        $db = $this->db;
        
        if (!empty($email) && !empty($password)) {
            
            if ($db->checkUserExist($email)) {
                
                $result = $db->login($email, $password);
                
                if (!$result) {
                    
                    $response["result"]  = "fail";
                    $response["message"] = "Неверный логин или пароль";
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                    
                } else {
                    
                    $response["result"] = "success";
                    $response["user"]   = $result;
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                    
                }
                
            } else {
                
                $response["result"]  = "fail";
                $response["message"] = "Неверный логин или пароль";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
                
            }
        } else {
            
            return $this->getMsgParamNotEmpty();
        }
        
    }
    




























    public function isEmailValid($email)
    {        
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public function getMsgParamNotEmpty()
    {
        $response["result"]  = "fail";
        $response["message"] = "Заполните все поля";
        return json_encode($response, JSON_UNESCAPED_UNICODE);
        
    }
    
    public function getMsgInvalidParam()
    {
        
        $response["result"]  = "fail";
        $response["message"] = "Неверные параметры";
        return json_encode($response, JSON_UNESCAPED_UNICODE);
        
    }
    
    public function getMsgInvalidEmail()
    {
        
        $response["result"]  = "fail";
        $response["message"] = "Неверный Email";
        return json_encode($response, JSON_UNESCAPED_UNICODE);
        
    }    
    
    private function getImage($image, $file)
    {
        $ifp = fopen($file, "wb");
        
        $data = explode(',', $image);
        
        fwrite($ifp, base64_decode($data[0]));
        fclose($ifp);
        
        return $file;
    }
    
    function imageResize($src, $dst, $width, $height, $crop=0) {
 
    if(!($info = @getimagesize($src)))
        return false;
 
    $w = $info[0];
    $h = $info[1];
    $type = substr($info['mime'], 6);
 
    $func = 'imagecreatefrom' . $type;
 
    if(!function_exists($func))
        return false;
    $img = $func($src);
 
        if($w < $width && $h < $height)
            return false; 
        $ratio = min($width/$w, $height/$h);
        $width = $w * $ratio;
        $height = $h * $ratio;
        $x = 0;
    
 
    $new = imagecreatetruecolor($width, $height);
        imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
        imagealphablending($new, false);
        imagesavealpha($new, true);
    imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);
 
    $save = 'image' . $type;
 
    return $save($new, $dst);
}

}
