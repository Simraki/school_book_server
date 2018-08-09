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

            $classNumber = trim($user->class->number);
            $classLetter = trim($user->class->letter);

            // Взятие записи о данном классе(школьном)

            $sql = 'SELECT id_class FROM classes WHERE number = :number AND letter = :letter';
            $query = $this->conn->prepare($sql);
            $query->execute(array(
                ':number'=>$classNumber,
                ':letter'=>$classLetter
            ));
            $data_class = $query->fetchObject();

            if (!empty($data_class)) {

                $id_class = $data_class->id_class;

                $name =trim( $user ->name);
                $surname = trim($user ->surname);
                $birth = trim($user ->birthDate);
                $email = trim($user ->email);
                $encrypted_password = trim($this->getHash($user->password));

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

                $sql = 'INSERT INTO users SET un_id = :un_id, en_p = :en_p, email = :email, name = :name, surname = :surname, type = :type, birthdate = :birth, rcu = :rcu, classStudent = :classStudent';

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
                    ':classStudent' => $id_class
                ));

                if ($query) {

                    $id_user = $this->conn->lastInsertId();

                    if (!empty($id_user)) {

                            return true;


                    } else {

                        // ID нового пользователя(ученика) пуст

                        $this->makeEntryToError("register", "ID record of new user(student) is empty");

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

            $subjects = $user->speciality;

            $temp = array();

            if (is_array($subjects) && !empty($subjects)) {
                foreach ($subjects as $key => $value) {
                    $value = $this->specialityToID($value);
                    if (!empty($value)) {
                        array_push($temp, $value);
                    }
                }
            }

            $subjects = serialize($temp);

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

            $sql = 'INSERT INTO users SET un_id = :un_id, en_p = :en_p, email = :email, name = :name, surname = :surname, type = :type, birthdate = :birth, rcu = :rcu, speciality = :subjects';

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
                ':subjects' => $subjects
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
        $data = $query->fetchObject();
        $db_encrypted_password = $data->en_p;

        if ($this->verifyHash($password."621317", $db_encrypted_password)) {

            $id_user = $data->id_user;
            $type = $data->type;

            // Определение типа пользователя (1 - Ученик // 2 - Учитель // 3 - Родитель)

            if ($type == 1) {

                $classStudent["id_class"] = $data->classStudent;
                $classStudent["number"] = -1;
                $classStudent["letter"] = "";

                $sql = 'SELECT * FROM classes WHERE id_class = :id_class';
                $query = $this->conn->prepare($sql);
                $query->execute(array(
                    ":id_class" => $data->classStudent
                ));
                $data_class = $query->fetchObject();

                if ($query) {
                    $classStudent["number"] = $data_class->number;
                    $classStudent["letter"] = $data_class->letter;
                }



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


    // Получение новостей

    public function getNews($getterNews) {

        $count = 10;                // Количество подгружаемых новостей

        $offset = $getterNews->offset;
        $last_id = -1;

        // Проверка на ранее получение новостей

        if ($getterNews->offset) {

            // Ранее были получены новости

            $last_id = $getterNews->last_id;

        } else {

            // Ещё не было получено новостей

            // Получение максимального ID в БД новостей

            $sql = 'SELECT MAX(id_news) as id_news FROM news';
            $query = $this->conn->prepare($sql);
            $query->execute();
            $last_id = $query->fetchObject()->id_news;

            if (!is_numeric($last_id) || $last_id <= 0) {

                return "Новостей ещё нет";
            }
        }

        if ($last_id != 0) {
            $new_last_id = $last_id - $count;
            if ($new_last_id < 0) {
                $new_last_id = 0;
            }

            $sql = 'SELECT * FROM news WHERE id_news > :new_last_id AND id_news <= :last_id ORDER BY id_news DESC';
            $query = $this->conn->prepare($sql);
            $query->execute(array(
                ':new_last_id' => $new_last_id,
                ':last_id' => $last_id
            ));

            if ($query) {
                $i = 0;
                $array_news = array();

                while ($data = $query->fetch()) {

                    if (!empty($data['title']) && !empty($data['content']) && !empty($data['date'])) {

                        $news['title'] = $data['title'];
                        $news['content'] = $data['content'];
                        $news['date'] = $data['date'];

                        $array_news[$i] = $news;
                        $i++;

                    } else {

                        // Title, Content или Date оказались пусты

                        $this->makeEntryToError("getNews", "Title, Content or Date is empty");
                    }

                }

                if (!empty($array_news)) {
                    $new_getterNews['last_id'] = $new_last_id;
                    $response['getterNews'] = $new_getterNews;
                    $response['news'] = $array_news;

                    return $response;

                } else {

                    // Новости не были получены || Массив оказался пустым

                    $this->makeEntryToError("getNews", "News not received // Array is empty");

                    return false;
                }
            } else {

                // Новости не были получены || Запрос не был выполнен

                $this->makeEntryToError("getNews", "News not received // Request failed");

                return false;
            }
        }
    }


    // Выставление оценки

    public function setMark($subject, $student, $value, $desc) {

        $desc = trim($desc);
        $date = date('d.m');

        if (empty($desc)) {
            $desc = null;
        }

        $subject = $this->specialityToID($subject);

        if(!empty($subject)) {

            $sql = 'INSERT INTO marks SET subject = :subject, student = :student, value = :value, date = :date, description = :desc';
            $query = $this->conn->prepare($sql);
            $query->execute(array(
                ':subject' => $subject,
                ':student' => $student,
                ':value' => $value,
                ':date' => $date,
                ':desc' => $desc
            ));

            if ($query) {

                return true;

            } else {

                // Запрос не был выполнен

                $this->makeEntryToError("setMark", "Request failed");

                return false;

            }

        } else {
            return false;
        }
    }


    // Получение оценок

    public function getMarks($id_user, $getterMarks) {

        $count_row = 10;             // Количество подгружаемых оценок

        $count = $count_row * $getterMarks->count;

        //$offset = $getterMarks->offset;
        //$last_id = -1;

        // Проверка на ранее получение оценок

        /*if ($getterMarks->offset) {

            // Ранее были получены оценки

            $last_id = $getterMarks->last_id;

        } else {

            // Ещё не было получено оценок

            // Получение максимального ID в БД оценок

            $sql = 'SELECT MAX(id_mark) as id_mark FROM marks';
            $query = $this->conn->prepare($sql);
            $query->execute();
            $last_id = $query->fetchObject()->id_mark;

            if (!is_numeric($last_id) || $last_id <= 0) {

                return "Оценок ещё нет";
            }
        }*/

            /*$new_last_id = $last_id - $count;

            if ($new_last_id < 0) {
                $new_last_id = 0;
            }*/

        $subject = -1;
        $final = false;

        $type = 0;

        if (!empty($id_user)) {

            if (isset($getterMarks->subject) && !empty($getterMarks->subject) && strcasecmp($getterMarks->subject, "Все") != 0) {
                $subject = $this->specialityToID($getterMarks->subject);
                $type = 1;
            }

            if (isset($getterMarks->final) && $getterMarks->final) {
                $final = true;
                $type = 2;
            }

            if ($type == 2 && isset($getterMarks->subject) && !empty($getterMarks->subject) && strcasecmp($getterMarks->subject, "Все") != 0) {
                $type = 3;
            }

            $c = 0;

            switch ($type) {
                case 1:
                        $sql = 'SELECT * FROM marks WHERE student = :student AND subject = :subject ORDER BY id_mark DESC LIMIT '.$count.', 10';
                        $array = array(
                            ':student' => $id_user,
                            ':subject' => $subject
                        );
                    break;
                case 2:
                        $sql = 'SELECT * FROM marks WHERE student = :student AND final = :final ORDER BY id_mark DESC LIMIT '.$count.', 10';
                        $array = array(
                            ':student' => $id_user,
                            ':final' => $final
                        );
                    break;
                case 3:
                        $sql = 'SELECT * FROM marks WHERE student = :student AND subject = :subject AND final = :final ORDER BY id_mark DESC LIMIT '.$count.', 10';
                        $array = array(
                            ':student' => $id_user,
                            ':subject' => $subject,
                            ':final' => $final
                        );
                    break;
                default:
                        $sql = 'SELECT * FROM marks WHERE student = :student ORDER BY id_mark DESC LIMIT '.$count.', 10 ';
                        $array = array(
                            ':student' => $id_user
                        );
                    break;
            }

            $query = $this->conn->prepare($sql);
            $query->execute($array);

            if ($query) {

                $i = 0;
                $array_marks = array();

                while ($data = $query->fetch()) {

                    if (!empty($data['subject']) && !empty($data['value']) && !empty($data['date'])) {


                        $mark['subject'] = $this->idToSpeciality($data['subject']);
                        $mark['value'] = $data['value'];
                        $mark['final'] = $data['final'] == "1";
                        $mark['date'] = $data['date'];
                        $mark['description'] = $data['description'];

                        $array_marks[$i] = $mark;
                        $i++;

                    } else {

                        // Subject, Value или Date оказались пусты

                        $this->makeEntryToError("getMarks", "Subject, Value or Date is empty");
                    }

                }

                if (!empty($array_marks)) {
                    //$new_getterMarks['last_id'] = $new_last_id;
                    //$response['getterMarks'] = $new_getterMarks;
                    $response['marks'] = $array_marks;

                    return $response;

                } else {

                    return false;
                }

            } else {

                // Оценки не получены

                $this->makeEntryToError("getMarks", "Marks not received");

                return false;
            }
        } else {

            // ID ученика не получен

            $this->makeEntryToError("getMarks", "ID not received");

            return false;
        }
        
    }

    public function getMarksVisitTeacher($id_user, $subject) {

        $date = date("d.m");

        $array = array(
            ':id_user' => $id_user,
            ':date' => $date,
            ':subject' => $subject
        );

        $sql = 'SELECT * FROM marks WHERE student = :id_user AND subject = :subject AND date = :date AND final = 0 ORDER BY id_mark DESC LIMIT 3';
        $query = $this->conn->prepare($sql);
        $d = $query->execute($array);

        if ($query) {

            $content = array();

            while($data = $query->fetchObject()) {
                if (!empty($data->value)) {
                    array_push($content, $data->value);
                }
            }

            $sql = 'SELECT COUNT(*) FROM visits WHERE id_student = :id_user AND date = :date AND subject = :subject';
            $query = $this->conn->prepare($sql);
            $query->execute($array);

            if ($query) {
                $data = $query->fetchColumn();
            }

            if (!empty($data)) {
                array_push($content, 'Н');
            }

            if (!empty($content)) {
                return $content;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }


    // Получение расписания для ученика

    public function getSchedule($id, $schedule) {

        $date = $schedule->date;
        $type = $this->getType($id);

        if (empty($id)) {

            $this->makeEntryToError("getSchedule", "ID user (TYPE => ".$type." empty");

            return false;

        } else if (!empty($type) && $type == 1) {

            if (!$schedule->highClass) {
                $id = $this->getClass($id);
            }

            if ($schedule->highClass) {
                $sql = 'SELECT * FROM schedule WHERE id_student = :id AND date = :date';
            } else {
                $sql = 'SELECT * FROM schedule WHERE id_class = :id AND date = :date';
            }

            $query = $this->conn->prepare($sql);
            $query->execute(array(
                ':id' => $id,
                ':date' => $date
            ));
            if ($query) {
                $data = $query->fetchObject();  
            }

            if (!empty($data)) {

                $subjects = unserialize($data->subjects);
                foreach ($subjects as $key => $value) {
                    $value = $this->idToSpeciality($value);
                    $subjects[$key] = $value;
                }

                unset($value);

                $schedule = array();

                $schedule['times'] = unserialize($data->times);
                $schedule['content'] = $subjects;

                return $schedule;

            } else {

                // Запрос не выполнен

                $this->makeEntryToError("getSchedule", "Request failed");

                return false;

            }

        } else if (!empty($type) && $type == 2) {

            $sql = 'SELECT id_class, subject FROM teachers WHERE id_teacher = :id';
            $query = $this->conn->prepare($sql);
            $query->execute(array(
                ':id' => $id
            ));

            if($query) {

                $classes = array();
                $subjects = array();

                while ($data = $query->fetchObject()) {
                    if (!empty($data->id_class) && !empty($data->subject)) {
                        array_push($classes, $data->id_class);
                        array_push($subjects, $data->subject);
                    }
                }

                if (!empty($classes) && !empty($subjects)) {
                    
                    $sql = 'SELECT * FROM schedule WHERE date = :date AND id_class IN ('.implode(',', $classes).')';
                    $query = $this->conn->prepare($sql);
                    $query->execute(array(
                        ':date' => $date
                    ));

                    if ($query) {
                        
                        $times = array();
                        $content = array();

                        while ($data = $query->fetchObject()) {
                            if (!empty($data->id_class) && !empty($data->times) && !empty($data->subjects)) {
                                $temp_times = unserialize($data->times);
                                $temp_subjects = unserialize($data->subjects);

                                foreach ($temp_subjects as $key => $value) {
                                    if (in_array($value, $subjects)) {
                                        array_push($times, $temp_times[$key]);
                                        $temp_class = $this->idToClassNumberAndLetter($data->id_class);
                                        $temp_content = $temp_class->number." '".$temp_class->letter."' ".'('.$this->idToSpeciality($value).')';
                                        array_push($content, $temp_content);
                                    }
                                }
                            }
                        }

                        if (!empty($times) && !empty($content)) {
                            $schedule = array();
                            $schedule['times'] = $times;
                            $schedule['content'] = $content;

                            return $schedule;


                        } else {
                            return false;
                        }

                    } else {

                        return false;

                    }


                } else {

                    return false;

                }

            } else {

                return false;

            }

        } else {
            return false;
        }           
    }


    // Создание домашнего задания

    public function createHomework($id_class, $date, $subject, $homework) {

        $subject = $this->specialityToID($subject);

        $sql = 'INSERT INTO homework SET id_class = :id_class, date = :date, subject = :subject, homework = :homework';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':id_class' => $id_class,
            ':date' => $date,
            ':subject' => $subject,
            ':homework' => $homework
        ));

        return $query;
    }


    // Получение домашнего задания

    public function getHomework($id_user, $date) {

        $type = $this->getType($id_user);

        if (empty($id_user)) {

            $this->makeEntryToError("getHomework", "ID user (TYPE => ".$type." empty");

            return false;

        } else if (!empty($type) && $type == 1) {

            $id_class = $this->getClass($id_user);

            if (!empty($id_class)) {
                $sql = 'SELECT * FROM homework WHERE id_class = :id_class AND date = :date';
                $query = $this->conn->prepare($sql);
                $query->execute(array(
                    ':id_class' => $id_class,
                    ':date' => $date
                ));

                if ($query) {

                    $homework = array();
                    $array_homework = array();

                    while ($data = $query->fetch()) {

                        if (!empty($data['subject']) && !empty($data['homework'])) {


                            $homework['title'] = $this->idToSpeciality($data['subject']);
                            $homework['content'] = $data['homework'];

                            array_push($array_homework, $homework);

                        } else {

                            // Subject или Homework оказались пусты

                            $this->makeEntryToError("getHomework", "Subject or Homework is empty");
                        }

                    }

                    if (!empty($array_homework)) {

                        return $array_homework;

                    } else {

                        return false;
                    }
                }
            }

        } else if (!empty($type) && $type == 2) {

            // Получение классов и предметов // Получение заданных д/з

            $links = $this->getClassesTeacher($id_user);

            if ($links) {

                $id_classes = array();

                foreach ($links as $key => $value) {
                    if(!in_array($value, $id_classes)) {
                        array_push($id_classes, $value['class']->id_class);
                    }
                }

                $array_subjects = $this->getSubjects($id_user);

                $sql = 'SELECT * FROM homework WHERE date = :date AND id_class IN ('.implode(',', $id_classes).') AND subject IN ('.implode(',', $array_subjects).')';
                $query = $this->conn->prepare($sql);
                $query->execute(array(
                    ':date' => $date
                ));      

                if($query) {

                    $array_homework = array();

                    while ($data = $query->fetchObject()) {

                        $class = $this->idToClassNumberAndLetter($data->id_class);
                        $homework['title'] = $class->number." '".$class->letter."' - ".$this->idToSpeciality($data->subject);
                        $homework['content'] = $data->homework;

                        array_push($array_homework, $homework);
                    } 

                    if(!empty($array_homework)) {
                        return $array_homework;
                    } else {
                        return false;
                    }

                } else {
                    
                    return false;

                }

            } else {

                return false;
            }



        } else {
            return false;
        }
    }


    // Создать посещение

    public function setVisit($id_student, $subject) {

        $date = date('d.m');
        $subject = $this->specialityToID($subject);

        if (!empty($subject)) {
            
            $array = array(
                ':id_student' => $id_student,
                ':subject' => $subject,
                ':date' => $date
            );


            $sql = 'SELECT COUNT(*) as count FROM visits WHERE id_student = :id_student AND subject = :subject AND date = :date';
            $query = $this->conn->prepare($sql);
            $query->execute($array);

            if ($query) {
                $data = $query->fetchColumn();
            }

            if ($data == 0) {


                $sql = 'INSERT INTO visits SET id_student = :id_student, subject = :subject, date = :date';
                $query = $this->conn->prepare($sql);
                $query->execute(array(
                    ':id_student' => $id_student,
                    ':subject' => $subject,
                    ':date' => $date
                ));

                return $query;
                
            } else {

                // Прогул уже стоит

                return false;
            }

        } else {
            return false;
        }
    }

    // Получение специальностей учителя

    public function getSubjects($id_user) {

        $sql = 'SELECT speciality FROM users WHERE id_user = :id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':id' => $id_user
        ));

        if ($query) {
            $data = $query -> fetchObject();
        }

        if ($data) {
            return unserialize($data->speciality);
        } else {
            return null;
        }

    }


    // Получение обучаемых классов

    public function getClassesTeacher($id_user) {

        $sql = 'SELECT id_class, subject FROM teachers WHERE id_teacher = :id_user';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':id_user' => $id_user
        ));

        if ($query) {

            $links = array();

            while ($data = $query->fetchObject()) {
                if (!empty($data->id_class) && !empty($data->subject)) {
                    $class = $this->idToClassNumberAndLetter($data->id_class);
                    $subject = $this->idToSpeciality($data->subject);
                    if (!empty($class) && !empty($subject)) {
                        $class->id_class = $data->id_class;
                        $link['class'] = $class;
                        $link['subject'] = $subject;
                        array_push($links, $link);
                    }
                }
            }

            if (!empty($links)) {

                return $links;

            } else {

                return false;
            }

        } else {

            return false;
        }
    }


    // Получение обучаемых классов

    public function getStudents($id_class, $getMarksVisit, $subject) {

        $sql = 'SELECT id_user, surname, name FROM users WHERE classStudent = :id_class ORDER BY surname';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':id_class' => $id_class
        ));

        if ($query) {
            $students = array();
            $subject = $this->specialityToID($subject);

            while ($data = $query->fetchObject()) {

                if (!empty($data->id_user) && !empty($data->surname) && !empty($data->name)) {
                    
                    $student['id_user'] = $data->id_user;
                    $student['surname'] = $data->surname;
                    $student['name'] = $data->name;

                    if ($getMarksVisit) {
                        $marksVisit = $this->getMarksVisitTeacher($data->id_user, $subject);
                        if (empty($marksVisit)) {
                            $marksVisit = null;
                        }
                        $student['marksVisit'] = $marksVisit;
                    }
                    
                    array_push($students, $student);
                }
            }

            if (!empty($students)) {
                return $students;
            } else {
                return false;
            }

        } else {

            return false;
        }
    }


    // Получение одноклассников

    public function getClassmates($id_user) {

        $id_class = $this->getClass($id_user);

        $sql = 'SELECT id_user, surname, name FROM users WHERE classStudent = :id_class ORDER BY surname';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':id_class' => $id_class
        ));

        if ($query) {
            $students = array();

            while ($data = $query->fetchObject()) {
                
                $user['name'] = $data->name;
                $user['surname'] = $data->surname;
                array_push($students, $user);
            }

            if (!empty($students)) {
                return $students;
            } else {
                return false;
            }

        } else {

            return false;
        }
    }




































































// Независимое управление

    // Создание новости

    public function createNews($title, $content) {

        $date = date('d.m.y');
        $title = trim($title);
        $content = trim($content);

        $sql = 'INSERT INTO news SET title = :title, content = :content, date = :date';
 
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':title' => $title,
            ':content' => $content,
            ':date' => $date
        ));

        if ($query) {
            return true;
        } else {
            return false;
        }
    }


    // Создание расписания

    public function createSchedule($schedule) {

        $id = -1;

        $date = $schedule->date;
        $subjects = $schedule->subjects;
        $times = $schedule->times;


        if (count($subjects) == count($times)) {

            $times = serialize($times);

            foreach ($subjects as $key => $value) {
                $value = $this->specialityToID($value);
                $subjects[$key] = $value;
            }

            unset($value);

            if (!in_array(null, $subjects)) {
                $subjects = serialize($subjects);
            }

            $highClass = false;

            if(isset($schedule->id_class) && !empty($schedule->id_class)) {
                $id = $schedule->id_class;
            }
            if(isset($schedule->id_student) && !empty($schedule->id_student)) {
                $id = $schedule->id_student;
                $highClass = true;
            }

            if ($this->checkExistingSchedule($id, $date, $highClass) && !is_array($subjects) ) {

                if ($id > 0) {
                    
                    if ($highClass) {

                        $id_class = $this->getClass($id);

                        $sql = 'INSERT INTO schedule SET id_class = :id_class, id_student = :id_student, date = :date, times = :times, subjects = :subjects';

                        $array = array(
                        ':id_class' => $id_class,
                        ':id_student' => $id,
                        ':date' => $date,
                        ':times' => $times,
                        ':subjects' => $subjects
                        );
                    } else {                
                        $sql = 'INSERT INTO schedule SET id_class = :id_class, date = :date, times = :times, subjects = :subjects';

                        $array = array(
                        ':id_class' => $id,
                        ':date' => $date,
                        ':times' => $times,
                        ':subjects' => $subjects
                        );
                    }
                    $query = $this->conn->prepare($sql);
                    $query->execute($array);

                    if ($query) {

                        return true;
                        
                    } else {

                        // Расписание не создано

                        $this->makeEntryToError("createSchedule", "Request failed for ".$id);

                        return false;

                    }

                } else {

                    // Не инициализированы ни id_class, ни id_student

                    return false;
                }

            } else {

                // Расписание на данную дату уже создано

                return false;

            }

        } else {

            // Массивы со временем и предметами не схожи по размеру

            return false;

        }
    }


    // Привязка учителя к классу

    public function linkTeacher($id_class, $id_teacher, $subject) {

        $subject = $this->specialityToID($subject);

        if (!empty($subject)) {

            $sql = 'SELECT COUNT(*) FROM teachers WHERE id_class = :id_class AND subject = :subject';
            $query = $this->conn->prepare($sql);
            $query->execute(array(
                ':id_class' => $id_class,
                ':subject' => $subject
            ));
            $count = $query->fetchColumn();

            if (empty($count)) {

                $sql = 'INSERT INTO teachers SET id_teacher = :id_teacher, id_class = :id_class, subject = :subject';
                $query = $this->conn->prepare($sql);
                $query -> execute(array(
                    ':id_teacher' => $id_teacher,
                    ':id_class' => $id_class,
                    ':subject' => $subject
                ));

            } else {

                $sql = 'UPDATE teachers SET id_teacher = :id_teacher WHERE id_class = :id_class AND subject = :subject';
                $query = $this->conn->prepare($sql);
                $query -> execute(array(
                    ':id_teacher' => $id_teacher,
                    ':id_class' => $id_class,
                    ':subject' => $subject
                ));
            }

            return $query;
            
        } else {

            // Нет соответствия в таблице предметов

            return false;

        }
    }


    // Создание оптимального расписания

    public function createOptSchedule($optSchedule) {

        $date = $optSchedule->date;
        $time = $optSchedule->time;
        $o_subjects = $optSchedule->subjects;

        $highClass = false;

        if(isset($schedule->id_class) && !empty($schedule->id_class)) {
            $id = $schedule->id_class;
        }
        if(isset($schedule->id_student) && !empty($schedule->id_student)) {
            $id = $schedule->id_student;
            $highClass = true;
        }

        $o_subjects = explode(",", $o_subjects);
        $subjects = array();

        foreach ($o_subjects as $key => $value) {
            $count = 1;
            $value = trim($value);
            $o_subjects[$key] = $value;
            $pattern = '/(\d*)[\s]*(\D+)/';
            preg_match_all($pattern, $value, $arr, PREG_SET_ORDER);
            $arr = $arr[0];
            if (!empty($arr[1])) {
                $count = $arr[1];
            }

            for ($i = 0; $i < $count; $i++) { 
                array_push($subjects, $arr[2]/*$this->specialityToID($arr[2])*/);
            }
        }

        unset($value);

        return $subjects;
    }







// Вспомогательные методы

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

    function checkExistingSchedule($id, $date, $highClass) {

        if ($highClass) {
            $sql = 'SELECT COUNT(*) FROM schedule WHERE id_student = :id AND date = :date';
        } else {
            $sql = 'SELECT COUNT(*) FROM schedule WHERE id_class = :id AND date = :date';            
        }

        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':id' => $id,
            ':date' => $date
        ));
        $count = $query->fetchColumn();

        return $count == 0;

    }

    function makeEntryToError($method, $error) {
        $message = "[ ".date(DATE_RSS)." ]: Method ".$method."() in DBOperations.php , Error: \" ".$error." \"; \r\n";
        error_log($message, 3, "school_book_errors.log");
    }

    public function getID($un_id)
    {
        $sql   = 'SELECT id_user FROM users WHERE un_id = :un_id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':un_id' => $un_id
        ));
        $data = $query->fetchObject();
        
        if ($query) {
            return $data->id_user;
        } else {
            return false;
        }
    }

    public function getType($id)
    {
        $sql   = 'SELECT type FROM users WHERE id_user = :id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':id' => $id
        ));
        $data = $query->fetchObject();
        
        if ($query) {
            return $data->type;
        } else {
            return null;
        }
    }

    public function getClass($id)
    {
        $sql   = 'SELECT classStudent FROM users WHERE id_user = :id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':id' => $id
        ));
        $data = $query->fetchObject();
        
        if ($query) {
            return $data->classStudent;
        } else {
            return null;
        }
    }


    public function idToSpeciality($id)
    {
        $sql   = 'SELECT spec FROM speciality WHERE id = :id';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':id' => $id
        ));
        $data = $query->fetchObject();
        
        return $data->spec;
    }

    public function specialityToID($spec)
    {
        $sql   = 'SELECT id FROM speciality WHERE spec = :spec';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':spec' => $spec
        ));
        $data = $query->fetchObject();
        
        if (!empty($data->id)) {
            return $data->id;
        } else {
            return null;
        }
    }

    public function idToClassNumberAndLetter($id_class) {

        $sql = 'SELECT number, letter FROM classes WHERE id_class = :id_class';
        $query = $this->conn->prepare($sql);
        $query->execute(array(
            ':id_class' => $id_class
        ));

        if ($query) {
            $data = $query->fetchObject();
        }

        if (!empty($data) && !empty($data->number) && !empty($data->letter)) {
            
            return $data;
        } else {
            return null;
        }

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
