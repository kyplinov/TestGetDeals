<?php
require 'config.php'; // подключение файла конфигурации(логин и пароль от api)
/* Методы api. leads - сделки, tasks - задачи */
define("URLAPI", array(
    'leads',
    'tasks',
));
define("URLLINK", ".amocrm.ru/api/v2/"); // линк на api
/* метод запроса */
define("TRANSFER", array(
    'GET',
    'POST'
));

getDeals();  // вызов фукции выборки сделок

/* функция установки параметров сеанса */
function SetCurlParam($urlapi, $transfer, $tasks = 0){
    $curl = curl_init(); // сохранение дискриптора сеанса
    // настройки для всего сеанса
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, 'https://' . SUBDOMAINS . URLLINK . $urlapi);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $transfer);
    /* используется для добавления задачи к сделки */
    if ($tasks>0 && $transfer = TRANSFER[1]) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($tasks));
    }
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    $res = curl_exec($curl); // инициализация запроса к api и сохренение ответа в переменную
    curl_close($curl); // завершение сеанса cURL
    return $res; // возвращение результата функции
};

/* функция выборки сделок */
function getDeals(){
    $res = SetCurlParam(URLAPI[0], TRANSFER[0]); // вызов функции установки сеанса
    $deals = json_decode($res, true); // возвращенный результат фукции преобразуется в ассоциативный массив
    $deals = $deals['_embedded']['items']; // выборка массива сделок
    foreach ($deals as $deal) {
        /* поиск сделок без созданных задач */
        if ($deal['closest_task_at'] == '0' ){
            addTask($deal['id']); // вызов фукции добавления задачи к сделки и передача id сделки
        }
    };
};

/* функция добавления сделки */
function addTask($id){
    if (!($id>0)) return false; // проверка на пустую строку
    /* массив описание информации о задаче */
    $tasks['add'] = array(
        #Привязываем к сделке
        array(
            'element_id' => $id, #ID сделки
            'element_type' => 2, #Показываем, что это - сделка, а не контакт
            'task_type' => 1, #Звонок
            'text' => 'c Боссом',
        ),
    );
    SetCurlParam(URLAPI[1], TRANSFER[1], $tasks); // вызов функции установки сеанса
};









