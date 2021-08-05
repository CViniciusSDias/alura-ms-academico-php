<?php

require_once 'rb.php';
require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

R::setup();

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('alura-academico', auto_delete: false);
$channel->basic_consume('alura-academico', no_ack: true, callback: function (AMQPMessage $msg) {
    $msgBody = json_decode($msg->body, true);
    if ($msgBody['msg'] !== 'client_enrolled') {
        return;
    }

    $properties = $msgBody['data'];
    $student = R::dispense('student');
    $student->name = $properties['name'];
    $student->email = $properties['email'];
    R::store($student);

    echo 'Enviando e-mail para ' . $student->email . PHP_EOL;
    echo "Olá, {$student->name}!\nPara acessar seus cursos, visite http://localhost:1000/cursos?email={$student->email}";
});

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();
