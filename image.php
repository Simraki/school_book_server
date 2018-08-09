<?php

$uploaddir = 'news_img/';
$uploadfile = $uploaddir . basename($_FILES['schoolBookImgF']['name']);

if ($_FILES['schoolBookImgF']['size'] >= 5*1024*1024) {
	echo "Файл большой!";
}

if (move_uploaded_file($_FILES['schoolBookImgF']['tmp_name'], $uploadfile)) {
    echo "Файл корректен и был успешно загружен.\n";
} else {
    echo "Возможная атака с помощью файловой загрузки!\n";
}

echo 'Некоторая отладочная информация:';
print_r($_FILES);

?>