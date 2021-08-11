<?php

require_once 'rb.php';
require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

R::setup();

$connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
$channel = $connection->channel();

$queue = 'student_enrollment';
$channel->queue_declare($queue, auto_delete: false);
$channel->queue_bind($queue, 'client_enrolled');
$channel->basic_consume($queue, no_ack: true, callback: function (AMQPMessage $msg) {
    $properties = json_decode($msg->body, true);
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
