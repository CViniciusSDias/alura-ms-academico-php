<?php

require_once 'rb.php';
require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Message\AMQPMessage;

R::setup(getenv('DB_DSN'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));

$connection = rabbitMqConnection();
$channel = $connection->channel();

$queue = 'student_enrollment';
$channel->exchange_declare('client_enrolled', 'fanout', durable: true, auto_delete: false);
$channel->queue_declare($queue, auto_delete: false);
$channel->queue_bind($queue, 'client_enrolled');
$channel->basic_consume($queue, no_ack: true, callback: function (AMQPMessage $msg) {
    $properties = json_decode($msg->body, true);
    $student = R::dispense('students');
    $student->name = $properties['name'];
    $student->email = $properties['email'];
    $student->password = password_hash('123456', PASSWORD_ARGON2ID);
    R::store($student);

    sendMailTo($student);
    echo 'E-mail enviado' . PHP_EOL;
});

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();
