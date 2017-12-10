<?php
class DBOperations
{    
    /*

    @author: YeapCool
    @date: 09.12.2017

    @style recommendations:
        if (комментарий с функцией в одной строчке) 
            4 отсупа (НЕ ПРОБЕЛА >:) от ';' или любого другого символа
        if (комментарий начинается с новой строчки)
            0 отступов
        Главные функции (register, login and etc) Должны оканчиваться двумя переносами строк

    @Just for you: Happiness, bright day and beautiful code        \(*○*\)        ^_^

    */

    private $host = '127.0.0.1';
    private $user = 'root';
    private $db = 'school_book_db';
    private $pass = '';
    private $conn;

    public function __construct()
    {

        $this->conn = new PDO("mysql:dbname=" . $this->db . ";host=" . $this->host, $this->user, $this->pass);
        $this->conn->query("SET NAMES 'utf-8'");
        $this->conn->query("SET CHARACTER SET 'utf8'");

    }


    // Проверка на уже существующего пользователя

    public function checkUserExist($email)
    {

        $sql   = 'SELECT COUNT(*) from users WHERE email =:email';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            'email' => $email
        ));

        if ($query) {

            $row_count = $query->fetchColumn();

            if ($row_count == 0) {

                return false;

            } else {

                return true;

            }
        } else {

            return false;

        }
    }


    // Регистрация

    public function register($user)
    {
        $type = $user ->type;

        // Определение типа пользователя (1 - Ученик // 2 - Учитель // 3 - Родитель)

        if ($type == 1) {

            $classNumber = $user->classStudent->number;
            $classLetter = $user->classStudent->letter;

            // Взятие записи о данном классе(школьном)SELECT * FROM classes WHERE number = 11 AND letter = 'А'

            $sql = 'SELECT id_class, students FROM classes WHERE number = :number AND letter = :letter';
            $query = $this->conn->prepare($sql);
            $query->execute(array(
                ':number'=>$classNumber,
                ':letter'=>$classLetter
            ));
            $data_class = $query->fetchObject();

            if (!empty($data_class)) {

                $id_class = $data_class->id_class;

                $name = $user ->name;
                $surname = $user ->surname;
                $birth = $user ->birthDate;
                $email = $user ->email;
                $encrypted_password = $this->getHash($user->password);

                // Генерация уникальных id и кода восстановления

                $un_id = uniqid('', true);
                $rcu = mt_rand();               // Recovery code user

                while (strlen($rcu) > 128) {
                    $rcu = mt_rand();
                }

                while (!$this->verifyUniquenessUn_id($un_id)) {
                    $un_id = uniqid('', true);
                }

                while (!$this->verifyUniquenessRCU($rcu)) {
                    $rcu = mt_rand();

                    while (strlen($rcu) > 128) {
                        $rcu = mt_rand();
                    }
                }

                // Создание записи о новом пользователе

                $sql = 'INSERT INTO users SET un_id = :un_id, en_p = :en_p, email = :email, name = :name, surname = :surname, type = :type, birthdate = :birth, rcu = :rcu, classStudentId = :classStudentId';

                $query = $this->conn->prepare($sql);
                $query->execute(array(
                    ':un_id' => $un_id,
                    ':en_p' => $encrypted_password,
                    ':email' => $email,
                    ':name' => $name,
                    ':surname' => $surname,
                    ':type' => $type,
                    ':birth' => $birth,
                    ':rcu' => $rcu,
                    ':classStudentId' => $id_class
                ));

                if ($query) {

                    $id_user = $this->conn->lastInsertId();

                    if (!empty($id_user)) {

                        $students_old = unserialize($data_class->students);             // Преобазование в массив
                        $students = $students_old;

                        if (empty($students)) {
                            $students = array();
                        }

                        array_push($students, $id_user);                // Вставка переменной в массив в конец

                        $students = serialize($students);               // Преобразование массива в строку (представление элементов)

                        // Обновление записи о классе(школьном) - изменение списка учеников

                        $sql = 'UPDATE classes SET students = :students WHERE number = :number AND letter = :letter';
                        $query = $this->conn->prepare($sql);
                        $query->execute(array(
                            ':students' => $students,
                            ':number' => $classNumber,
                            ':letter' => $classLetter
                        ));

                        if ($query) {

                            return true;

                        } else {

                            // Запись о классе(школьном) не обновлена

                            // Удалени записи о созданном пользователе(ученике)

                            $sql = 'DELETE FROM users WHERE id_user = :id_user';
                            $query = $this->conn->prepare($sql);
                            $query->execute(array(
                                ':id_user' => $id_user
                            ));

                            if ($query) {
                                $this->makeEntryToError("register", "Class record isn't updated");
                            } else {
                                $this->makeEntryToError("register", "Class record isn't updated // Record of new user(student) isn't deleted");
                            }

                            return false;

                        }

                    } else {

                        // ID нового пользователя(ученика) пуст

                        $this->makeEntryToError("register", "ID record of new user(student) isn't empty");

                        return false;

                    }

                    // Добавление нового ученика в список учеников класса

                } else {

                    // Запись о новом пользователе(ученике) не создана

                    $this->makeEntryToError("register", "Record of new user(student) isn't made");

                    return false;

                }

            } else {

                // Запись о данном классе(школьном) не найдена

                $this->makeEntryToError("register", "Class(".$classNumber.$classLetter.") record isn't found");

                return false;

            }

            
        } else if ($type == 2) {

            $speciality = serialize($user ->speciality);

            $name = $user ->name;
            $surname = $user ->surname;
            $birth = $user ->birthDate;
            $email = $user ->email;
            $encrypted_password = $this->getHash($user->password);

            // Генерация уникальных id и кода восстановления

            $un_id = uniqid('', true);
            $rcu = mt_rand();               // Recovery code user

            while (strlen($rcu) > 128) {
                $rcu = mt_rand();
            }

            while (!$this->verifyUniquenessUn_id($un_id)) {
                $un_id = uniqid('', true);
            }

            while (!$this->verifyUniquenessRCU($rcu)) {
                $rcu = mt_rand();

                while (strlen($rcu) > 128) {
                    $rcu = mt_rand();
                }
            }

            // Создание записи о новом пользователе

            $sql = 'INSERT INTO users SET un_id = :un_id, en_p = :en_p, email = :email, name = :name, surname = :surname, type = :type, birthdate = :birth, rcu = :rcu, speciality = :speciality';

            $query = $this->conn->prepare($sql);
            $query->execute(array(
                ':un_id' => $un_id,
                ':en_p' => $encrypted_password,
                ':email' => $email,
                ':name' => $name,
                ':surname' => $surname,
                ':type' => $type,
                ':birth' => $birth,
                ':rcu' => $rcu,
                ':speciality' => $speciality
            ));

            if ($query) {
                
                return true;

            } else {

                // Запись о новом пользователе(учителе) не создана

                $this->makeEntryToError("register", "Record of new user(teacher) isn't made");

                return false;

            }



        } else if ($type == 3) {

        }
    }


    // Авторизация

    public function login($email, $password)
    {

        // Взятие данного пользователя

        $sql   = 'SELECT * FROM users WHERE email = :email';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':email' => $email
        ));
        $data                  = $query->fetchObject();
        $db_encrypted_password = $data->en_p;

        if ($this->verifyHash($password."621317", $db_encrypted_password)) {

            $id_user = $data->id_user;
            $type = $data->type;

            // Определение типа пользователя (1 - Ученик // 2 - Учитель // 3 - Родитель)

            if ($type == 1) {

                $classStudent["id_class"] = $data->classStudentId;
                $user["classStudent"] = $classStudent;

            } else if($type == 2) {

                $speciality = unserialize($data->speciality);
                $user["speciality"] = $speciality;

            } else if ($type == 3) {

            } else {

                // Запись о данном пользователе не взята

                $this->makeEntryToError("login", "User record isn't found");

                return false;
            }


        /* if (file_exists("image_person/$id.png")) {
            $image_person = 'http://192.168.1.60' . "/shuttlecar/" . "image_person/$id.png";
        } else {
            $image_person = null;
            
        }if (file_exists("image_car/$id.png")) {
            $image_car = 'http://192.168.1.60' . "/shuttlecar/" . "image_car/$id.png";
        } else {
            $image_car = null;
        } */
        
        $user["id_user"]   = $id_user;
        $user["un_id"]   = $data->un_id;
        $user["email"]   = $data->email;
        $user["name"]    = $data->name;
        $user["surname"]    = $data->surname;
        $user["type"] = $type;
        $user["birthDate"] = $data->birthdate;
        $user["rcu"]     = $data->rcu;

        return $user;

    } else {

        return false;

    }
}












public function getHash($password)
{

    $salt      = sha1(mt_rand());
    $salt      = substr($salt, 0, 10);
    $encrypted = password_hash($password . "621317", PASSWORD_BCRYPT, ['cost' => 14]);
    $hash      = $encrypted;        
    return $hash;
    
}

public function verifyHash($password, $hash)
{

    return password_verify($password, $hash);
}

function verifyUniquenessRCU($rcu) {

    $sql = 'SELECT COUNT(*) FROM users WHERE rcu = :rcu';
    $query = $this ->conn -> prepare($sql);
    $query->execute(array(
        ":rcu" => $rcu
    ));
    $count = $query->fetchColumn();

    if (empty($count)) {

        return true;

    } else {

        return false;

    }
}

function verifyUniquenessUn_id($un_id) {

    $sql = 'SELECT COUNT(*) FROM users WHERE un_id = :un_id';
    $query = $this ->conn -> prepare($sql);
    $query->execute(array(
        ":un_id" => $un_id
    ));
    $count = $query->fetchColumn();

    if (empty($count)) {

        return true;

    } else {

        return false;

    }

}


function makeEntryToError($method, $error) {
    $message = "[ ".date(DATE_RSS)." ]: Method ".$method."() in DBOperations.php , Error: \" ".$error." \"; \r\n";
    error_log($message, 3, "school_book_errors.log");
}















public function checkLoginUnID($email, $un_id)
{
    $sql   = 'SELECT COUNT(*) FROM users WHERE email = :email AND un_id = :un_id';
    $query = $this->conn->prepare($sql);
    $query->execute(array(
        ':email' => $email,
        ':un_id' => $un_id
    ));
    $count = $query->fetchColumn();
    
    if ($count == 1) {
        return true;
    } else {
        return false;            
    }
    
}

public function getID($email, $un_id)
{
    $sql   = 'SELECT id_user FROM users WHERE email = :email AND un_id = :un_id';
    $query = $this->conn->prepare($sql);
    $query->execute(array(
        ':email' => $email,
        ':un_id' => $un_id
    ));
    $data = $query->fetchObject();
    
    $sql_count   = 'SELECT COUNT(*) FROM users WHERE  email = :email AND un_id = :un_id';
    $query_count = $this->conn->prepare($sql_count);
    $query_count->execute(array(
        ':email' => $email,
        ':un_id' => $un_id
    ));
    $count = $query_count->fetchColumn();
    
    if ($count == 1) {
        return $data->id_user;
    } else {
        return false;
        
    }
}

public function findUserTel($tel)
{
    $sql   = 'SELECT name, id_user, tel FROM users WHERE tel = :tel';
    $query = $this->conn->prepare($sql);
    $query->execute(array(
        ':tel' => $tel
    ));
    $data = $query->fetchObject();
    
    $sql_count   = 'SELECT COUNT(*) FROM users WHERE  tel = :tel';
    $query_count = $this->conn->prepare($sql_count);
    $query_count->execute(array(
        ':tel' => $tel
    ));
    $count = $query_count->fetchColumn();
    
    if ($count == 1 && !empty($data->tel)) {
        $id = $data->id_user;            
        
        if (file_exists("image_person/$id.png")) {
            $image = 'http://192.168.1.60' . "/shuttlecar/" . "image_person/$id.png";
        } else {
            $image = null;
        }
        
        $user["name"]    = $data->name;
        $user["image_person"]     = $image;
        return $user;
    } else {
        return false;
        
    }
}

private function getRat_choose($email, $un_id)
{
    $sql   = 'SELECT rat_choose FROM users WHERE email = :email AND un_id = :un_id';
    $query = $this->conn->prepare($sql);
    $query->execute(array(
        ':email' => $email,
        ':un_id' => $un_id
    ));
    $data = $query->fetchObject();
    
    if ($query) {

        $rating_choose = unserialize($data->rat_choose);
        if (is_array($rating_choose)) {
            return $rating_choose;
        } else if (empty($rating_choose)) {
            return $rating_choose = array();
        }
        
    } else {
        return false;
        
    }
}

public function checkOrder($pdis, $pdel, $time, $date, $id)
{
    $sql   = 'SELECT COUNT(*) FROM orders WHERE pdis = :pdis AND pdel = :pdel AND time = :time AND date = :date AND driver = :id';
    $query = $this->conn->prepare($sql);
    $query->execute(array(
        ':pdis' => $pdis,
        ':pdel' => $pdel,
        ':time' => $time,
        ':date' => $date,
        ':id' => $id
    ));
    $count = $query->fetchColumn();
    
    if (empty($count)) {
        return true;
    } else {
        return false;
        
    }
}

public function lugg_sizeToID($lugg)
{
    $sql   = 'SELECT id FROM lugg_size WHERE lugg = :lugg';
    $query = $this->conn->prepare($sql);
    $query->execute(array(
        ':lugg' => $lugg
    ));
    $data = $query->fetchObject();
    
    return $data->id;
}

public function idToLugg_size($id)
{
    $sql   = 'SELECT lugg FROM lugg_size WHERE id = :id';
    $query = $this->conn->prepare($sql);
    $query->execute(array(
        ':id' => $id
    ));
    $data = $query->fetchObject();
    
    return $data->lugg;
}

public function placeToID($place)
{
    if($place != null) {

        $data = null;
        $count = null;
        
        $sql   = 'SELECT id FROM place WHERE place = :place';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':place' => $place
        ));
        $data = $query->fetchObject();

        $sql_count   = 'SELECT COUNT(*) FROM place WHERE place = :place';
        $query_count = $this->conn->prepare($sql_count);
        $query_count->execute(array(
            ':place' => $place
        ));
        $count = $query_count->fetchColumn();

        if ($count == 0) {   
            return false;        
        } else {
            return $data->id; 
        }
    }
}

public function idToPlace($id)
{
    if(!empty($id)) {
        $sql   = 'SELECT place FROM place WHERE id = :id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':id' => $id
        ));
        $data = $query->fetchObject();

        return $data->place;
    }
}

public function toTime($time)
{
    $form_time = DateTime::createFromFormat('H:i:s', $time);
    $time = $form_time->format('H:i');        
    return $time;
}
}
