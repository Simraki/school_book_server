<?php
require_once 'Functions.php';

$functions = new Functions();

$array_ad_c = array('621317');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    if (isset($data->operation)) {
        $operation = $data->operation;
        if (!empty($operation)) {

            if ($operation == 'register') {
                if (isset($data->user,
                    $data->user->name,
                    $data->user->surname,
                    $data->user->type,
                    $data->user->birthDate,
                    $data->user->email,
                    $data->user->password)
                    && !empty($data->user)
                    && !empty($data->user->name)
                    && !empty($data->user->surname)
                    && !empty($data->user->type)
                    && !empty($data->user->birthDate)
                    && !empty($data->user->email)
                    && !empty($data->user->password)) {
                    
                    $user = $data->user;

                    if ($functions->isEmailValid($user->email)) {
                        echo $functions->registerUser($user);
                    } else {
                        echo $functions->getMsgInvalidEmail();
                    }
                    
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            } 

            else if ($operation == 'login') {
                if (isset($data->user,
                    $data->user->email,
                    $data->user->password)
                    && !empty($data->user)
                    && !empty($data->user->email)
                    && !empty($data->user->password)) {

                    $user = $data->user;
                    $email = $user->email;
                    $password = $user->password;
                    echo $functions->loginUser($email, $password);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }

          /*  else if ($operation == 'getProfile') {
                if (isset($data->user,
                    $data->user->un_id)
                    && !empty($data->user->un_id)) {

                    $un_id = $user->un_id;
                    echo $functions->getProfile($un_id);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }*/

            else if ($operation == 'getNews') {
                if (isset($data->getterNews,
                    $data->getterNews->offset)
                    && !empty($data->getterNews)
                    && is_bool($data->getterNews->offset)) {

                    $getterNews = $data->getterNews;

                    echo $functions->getNews($getterNews);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }

            else if ($operation == 'setMark') {
                if (isset($data->mark,
                    $data->mark->subject,
                    $data->mark->student,
                    $data->mark->value)
                    && !empty($data->mark)
                    && !empty($data->mark->subject)
                    && !empty($data->mark->student)
                    && !empty($data->mark->value)) {

                    $subject = $data->mark->subject;
                    $student = $data->mark->student;
                    $value = $data->mark->value;

                    $desc = null;
                    if (isset($data->mark->description) && !empty(trim($data->mark->description))) {
                        $desc = trim($data->mark->description);
                    }

                    echo $functions->setMark($subject, $student, $value, $desc);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }

            else if ($operation == 'getMarks') {
                if (isset($data->getterMarks,
                    $data->getterMarks->count,
                    $data->user->id_user)
                    && !empty($data->getterMarks)
                    && !empty($data->user->id_user)
                    && is_int($data->getterMarks->count)) {

                    $getterMarks = $data->getterMarks;
                    $id_user = $data->user->id_user;

                    echo $functions->getMarks($id_user, $getterMarks);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }

            else if ($operation == 'getMarksVisitTeacher') {
                if (isset($data->user->id_user,
                    $data->getterMarks->subject)
                    && !empty($data->user->id_user)
                    && !empty($data->getterMarks->subject)) {

                    $id_user = $data->user->id_user;
                    $subject = $data->getterMarks->subject;

                    echo $functions->getMarksVisitTeacher($id_user, $subject);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }

            else if ($operation == 'getSchedule') {
                if (isset($data->schedule,
                    $data->schedule->date,
                    $data->user->id_user)
                    && !empty($data->schedule)
                    && !empty($data->schedule->date)
                    && !empty($data->user->id_user)) {

                    $schedule = $data->schedule;
                    $id_user = $data->user->id_user;

                    if (!isset($schedule->highClass)) {
                        $schedule->highClass = false;
                    }
                    
                    echo $functions->getSchedule($id_user, $schedule);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }

            else if ($operation == 'createHomework') {
                if (isset($data->homework,
                    $data->homework->id_class,
                    $data->homework->date,
                    $data->homework->subject,
                    $data->homework->homework)
                    && !empty($data->homework)
                    && !empty($data->homework->id_class)
                    && !empty($data->homework->date)
                    && !empty($data->homework->subject)
                    && !empty($data->homework->homework)) {

                    $homework = $data->homework;

                    $id_class = $homework->id_class;
                    $date = $homework->date;
                    $subject = $homework->subject;
                    $homework = $homework->homework;

                    echo $functions->createHomework($id_class, $date, $subject, $homework);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }

            else if ($operation == 'getHomework') {
                if (isset($data->homework,
                    $data->user->id_user,
                    $data->homework->date)
                    && !empty($data->homework)
                    && !empty($data->user->id_user)
                    && !empty($data->homework->date)) {

                    $id_user = $data->user->id_user;
                    $date = $data->homework->date;

                    echo $functions->getHomework($id_user, $date);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }

            else if ($operation == 'setVisit') {
                if (isset($data->visit,
                    $data->visit->id_student,
                    $data->visit->subject)
                    && !empty($data->visit)
                    && !empty($data->visit->id_student)
                    && !empty($data->visit->subject)) {

                    $id_student = $data->visit->id_student;
                    $subject = $data->visit->subject;

                    echo $functions->setVisit($id_student, $subject);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }

            else if ($operation == 'getClassesTeacher') {
                if (isset($data->user,
                    $data->user->id_user)
                    && !empty($data->user)
                    && !empty($data->user->id_user)) {

                    $id_user = $data->user->id_user;

                    echo $functions->getClassesTeacher($id_user);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }

            else if ($operation == 'getStudents') {
                if (isset($data->getterStudents,
                    $data->getterStudents->id_class)
                    && !empty($data->getterStudents)
                    && !empty($data->getterStudents->id_class)) {

                    $id_class = $data->getterStudents->id_class;
                    if (isset($data->getterStudents->getMarksVisit)) {
                        $getMarksVisit = $data->getterStudents->getMarksVisit;
                        $subject = $data->getterStudents->subject;
                    } else {
                        $getMarksVisit = false;
                        $subject = null;
                    }
                    echo $functions->getStudents($id_class, $getMarksVisit, $subject);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }

            else if ($operation == 'getClassmates') {
                if (isset($data->user,
                    $data->user->id_user)
                    && !empty($data->user)
                    && !empty($data->user->id_user)) {

                    $id_user = $data->user->id_user;

                    echo $functions->getClassmates($id_user);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }



// Независимое управление

            else if ($operation == 'createNews') {
                if (isset($data->ad_c,
                    $data->news,
                    $data->news->title,
                    $data->news->content)
                    && !empty($data->ad_c)
                    && !empty($data->news)
                    && !empty($data->news->title)
                    && !empty($data->news->content)
                    && in_array($data->ad_c, $array_ad_c, true)) {

                    $news = $data->news;
                    $title = $news->title;
                    $content = $news->content;

                    echo $functions->createNews($title, $content);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }

            else if ($operation == 'createSchedule') {
                if (isset($data->ad_c,
                    $data->schedule,
                    $data->schedule->date,
                    $data->schedule->times,
                    $data->schedule->subjects)
                    && (isset($data->schedule->id_class) || isset($data->schedule->id_student))
                    && !(isset($data->schedule->id_class) && isset($data->schedule->id_student))
                    && !empty($data->ad_c)
                    && !empty($data->schedule)
                    && !empty($data->schedule->date)
                    && !empty($data->schedule->times)
                    && !empty($data->schedule->subjects)
                    && is_array($data->schedule->times)
                    && is_array($data->schedule->subjects)
                    && in_array($data->ad_c, $array_ad_c, true)) {

                    $schedule = $data->schedule;

                    echo $functions->createSchedule($schedule);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }

            else if ($operation == 'linkTeacher') {
                if (isset($data->ad_c,
                    $data->linkTeacher,
                    $data->linkTeacher->id_class,
                    $data->linkTeacher->id_teacher,
                    $data->linkTeacher->subject)
                    && !empty($data->ad_c)
                    && !empty($data->linkTeacher)
                    && !empty($data->linkTeacher->id_class)
                    && !empty($data->linkTeacher->id_teacher)
                    && !empty($data->linkTeacher->subject)
                    && in_array($data->ad_c, $array_ad_c, true)) {

                    $id_class = $data->linkTeacher->id_class;
                    $id_teacher = $data->linkTeacher->id_teacher;
                    $subject = $data->linkTeacher->subject;

                    echo $functions->linkTeacher($id_class, $id_teacher, $subject);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }


            // ДОРАБОТАТЬ

            else if ($operation == 'createOptSchedule') {
                if (isset($data->ad_c,
                    $data->optSchedule,
                    $data->optSchedule->date,
                    $data->optSchedule->time,
                    $data->optSchedule->subjects)
                    && (isset($data->optSchedule->id_class) || isset($data->optSchedule->id_student))
                    && !(isset($data->optSchedule->id_class) && isset($data->optSchedule->id_student))
                    && !empty($data->ad_c)
                    && !empty($data->optSchedule)
                    && !empty($data->optSchedule->date)
                    && !empty($data->optSchedule->time)
                    && !empty($data->optSchedule->subjects)
                    && in_array($data->ad_c, $array_ad_c, true)) {

                    $optSchedule = $data->optSchedule;

                    echo $functions->createOptSchedule($optSchedule);
                } else {
                    echo $functions->getMsgInvalidParam();
                }
            }














            
        } else {
            echo $functions->getMsgParamNotEmpty();
        }
    } else {
        echo $functions->getMsgInvalidParam();
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    echo "\"School Book\" Server Ready ^_^";
}