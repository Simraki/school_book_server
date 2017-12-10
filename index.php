<?php
require_once 'Functions.php';

$functions = new Functions();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    if (isset($data->operation)) {
        $operation = $data->operation;
        if (!empty($operation)) {

            if ($operation == 'register') {
                if (isset($data->user) && !empty($data->user) && isset($data->user->name) && isset($data->user->surname) && isset($data->user->type) && isset($data->user->birthDate)
                	&& isset($data->user->email) && isset($data->user->password)) {
                    
                    $user     = $data->user;

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
                if (isset($data->user) && !empty($data->user) && isset($data->user->email) && isset($data->user->password)) {
                    $user     = $data->user;
                    $email    = $user->email;
                    $password = $user->password;
                    echo $functions->loginUser($email, $password);
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