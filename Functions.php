<?php

require_once 'DBOperations.php';

class Functions
{    
    private $db;
    
    public function __construct() {
        $this->db = new DBOperations();
    }
    
    
    public function registerUser($user) {    
        $db = $this->db;
        
        if (!empty($user->name)
            && !empty($user->surname)
            && !empty($user->type)
            && !empty($user->birthDate)
            && !empty($user->email)
            && !empty($user->password)) {
            
            if ($db->checkUserExist($user->email)) {
                
                $response["result"] = "fail";
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
                    
                    $response["result"] = "fail";
                    $response["message"] = "Ошибка при регистрации";
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                    
                }
            }
        } else {
            return $this->getMsgInvalidParam();
        }
    }
    
    public function loginUser($email, $password) {
        $db = $this->db;
        
        if (!empty($email) && !empty($password)) {
            
            if ($db->checkUserExist($email)) {
                
                $result = $db->login($email, $password);
                
                if (!$result) {
                    
                    $response["result"] = "fail";
                    $response["message"] = "Неверный логин или пароль";
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                    
                } else {
                    
                    $response["result"] = "success";
                    $response["user"] = $result;
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                    
                }
                
            } else {
                
                $response["result"] = "fail";
                $response["message"] = "Неверный логин или пароль";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
                
            }
        } else {
            
            return $this->getMsgInvalidParam();
        }
    }
    
   /* public function getProfile($un_id) {
        $db = $this->db;

        if (!empty($un_id)) {

            $result = $db->getProfile($un_id);

            if ($result) {

                $response["result"]  = "success";
                $response["user"]   = $result;
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"] = "fail";
                $response["message"]  = "Не получена информация о вас :(";
                return json_encode($response, JSON_UNESCAPED_UNICODE); 
            }
        } else {
                return $this->getMsgInvalidParam();
        }
    }*/
    
    public function getNews($getterNews) {
        $db = $this->db;

        if (!empty($getterNews)) {

            $result = $db->getNews($getterNews);

            if ($result) {

                $response["result"]  = "success";
                $response["getterNews"]   = $result['getterNews'];
                $response["news"]   = $result['news'];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"] = "fail";
                $response["message"]  = "Нет новостей";
                return json_encode($response, JSON_UNESCAPED_UNICODE); 
            }
        } else {
                return $this->getMsgInvalidParam();
        }
    }
    
    public function setMark($subject, $student, $value, $desc) {
        $db = $this->db;

        if (!empty($subject)
            && !empty($student)
            && !empty($value) && $value <= 5 && $value > 1) {

            $result = $db->setMark($subject, $student, $value, $desc);

            if ($result) {

                $response["result"]  = "success";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"] = "fail";
                $response["message"]  = "Ошибка при выставлении оценки";
                return json_encode($response, JSON_UNESCAPED_UNICODE); 
            }
        } else {
                return $this->getMsgInvalidParam();
        }
    }

    
    public function getMarks($id_user, $getterMarks) {
        $db = $this->db;

        if (!empty($id_user)
            && !empty($getterMarks)) {

            $result = $db->getMarks($id_user, $getterMarks);

            if ($result) {
                $response["result"]  = "success";
                //$response["getterMarks"]   = $result['getterMarks'];
                $response["marks"]   = $result['marks'];
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"] = "fail";
                $response["message"]  = "Нет оценок по этим параметрам";
                return json_encode($response, JSON_UNESCAPED_UNICODE); 
            }
        } else {
                return $this->getMsgInvalidParam();
        }
    }

    
    public function getMarksVisitTeacher($id_user, $subject) {
        $db = $this->db;

        if (!empty($id_user)) {

            $result = $db->getMarksVisitTeacher($id_user, $subject);

            if ($result) {
                $response["result"]  = "success";
                $response["marks"] = $result;
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"] = "fail";
                $response["message"]  = "Нет оценок по этим параметрам";
                return json_encode($response, JSON_UNESCAPED_UNICODE); 
            }
        } else {
                return $this->getMsgInvalidParam();
        }
    }

    
    public function getSchedule($id_user, $schedule) {
        $db = $this->db;

        if (!empty($id_user)
            && !empty($schedule)) {

            $result = $db->getSchedule($id_user, $schedule);

            if ($result) {
                $response["result"]  = "success";
                $response["schedule"]   = $result;
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"] = "fail";
                $response["message"]  = "Нет расписания";
                return json_encode($response, JSON_UNESCAPED_UNICODE); 
            }
        } else {
                return $this->getMsgInvalidParam();
        }
    }

    public function createHomework($id_class, $date, $subject, $homework) {
        $db = $this->db;

        if (!empty($id_class)
            && !empty($date)
            && !empty($subject)
            && !empty($homework)
            && strlen($homework) <= 510) {

            $result = $db->createHomework($id_class, $date, $subject, $homework);

            if ($result) {
                $response["result"] = "success";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"]  = "fail";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    
    public function getHomework($id_user, $date) {
        $db = $this->db;

        if (!empty($id_user)
            && !empty($date)) {

            $result = $db->getHomework($id_user, $date);

            if ($result) {
                $response["result"]  = "success";
                $response["homework"]   = $result;
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"] = "fail";
                $response["message"]  = "Нет домашнего задания";
                return json_encode($response, JSON_UNESCAPED_UNICODE); 
            }
        } else {
                return $this->getMsgInvalidParam();
        }
    }

    
    public function setVisit($id_student, $subject) {
        $db = $this->db;

        if (!empty($id_student)
            && !empty($subject)) {

            $result = $db->setVisit($id_student, $subject);

            if ($result) {
                $response["result"]  = "success";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"] = "fail";
                $response["message"] = "Прогул уже стоит";
                return json_encode($response, JSON_UNESCAPED_UNICODE); 
            }
        } else {
                return $this->getMsgInvalidParam();
        }
    }

    
    public function getClassesTeacher($id_user) {
        $db = $this->db;

        if (!empty($id_user)) {

            $result = $db->getClassesTeacher($id_user);

            if ($result) {
                $response["result"]  = "success";
                $response["links"]  = $result;
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"] = "fail";
                return json_encode($response, JSON_UNESCAPED_UNICODE); 
            }
        } else {
                return $this->getMsgInvalidParam();
        }
    }

    
    public function getStudents($id_class, $getMarksVisit, $subject) {
        $db = $this->db;

        if (!empty($id_class)) {

            $result = $db->getStudents($id_class, $getMarksVisit, $subject);

            if ($result) {
                $response["result"]  = "success";
                $response["users"]  = $result;
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"] = "fail";
                $response["message"] = "Нет обучающихся";
                return json_encode($response, JSON_UNESCAPED_UNICODE); 
            }
        } else {
                return $this->getMsgInvalidParam();
        }
    }

    
    public function getClassmates($id_user) {
        $db = $this->db;

        if (!empty($id_user)) {

            $result = $db->getClassmates($id_user);

            if ($result) {
                $response["result"]  = "success";
                $response["users"]  = $result;
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"] = "fail";
                $response["message"] = "Нет одноклассников";
                return json_encode($response, JSON_UNESCAPED_UNICODE); 
            }
        } else {
                return $this->getMsgInvalidParam();
        }
    }


























// Независимое управление

    public function createNews($title, $content) {
        $db = $this->db;

        if (!empty($title) && !empty($content) && strlen($title) <= 90) {

            $result = $db->createNews($title, $content);

            if ($result) {
                $response["result"] = "success";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"]  = "fail";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public function createSchedule($schedule) {
        $db = $this->db;

        if (!empty($schedule)
                    && !empty($schedule->date)
                    && !empty($schedule->times)
                    && !empty($schedule->subjects)
                    && !(empty($optSchedule->id_class) == empty($optSchedule->id_student))) {

            $result = $db->createSchedule($schedule);

            if ($result) {
                $response["result"] = "success";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"]  = "fail";
                $response["message"] = "Расписание не было создано, либо было создано ранее";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public function linkTeacher($id_class, $id_teacher, $subject) {
        $db = $this->db;

        if (!empty($id_class)
                    && !empty($id_teacher)
                    && !empty($subject)) {

            $result = $db->linkTeacher($id_class, $id_teacher, $subject);

            if ($result) {
                $response["result"] = "success";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"]  = "fail";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        }
    }














    public function createOptSchedule($optSchedule) {
        $db = $this->db;

        if (!empty($optSchedule)
                    && !empty($optSchedule->date)
                    && !empty($optSchedule->time)
                    && !empty($optSchedule->subjects)
                    && !(empty($optSchedule->id_class) == empty($optSchedule->id_student))) {

            $result = $db->createOptSchedule($optSchedule);

            if ($result) {
                $response["result"] = "success";
                $response["schedule"] = $result;
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            } else {
                $response["result"]  = "fail";
                $response["message"] = "Расписание не было создано, либо было создано ранее";
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
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
