<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPIOException;
use RedBeanPHP\OODBBean;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

function rabbitMqConnection(): AMQPStreamConnection
{
    do {
        try {
            $connection = new AMQPStreamConnection(
                getenv('RABBITMQ_HOST'),
                getenv('RABBITMQ_PORT'),
                getenv('RABBITMQ_USERNAME'),
                getenv('RABBITMQ_PASSWORD')
            );
        } catch (AMQPIOException) {
            sleep(5);
            echo 'Retrying' . PHP_EOL;
        }
    } while(!isset($connection));

    return $connection;
}

function sendMailTo(OODBBean $student): void
{
    $mensagem = <<<FIM
    Olá, $student->name! Seu pagamento foi confirmado e sua matrícula foi criada com sucesso.
    Para acessar sua conta e começar a estudar conosco, acesse: http://localhost:4200/login.
    Seus dados de acesso são:
    E-mail: $student->email
    Senha: 123456
    
    Bons estudos!
    FIM;

    $usuario = getenv('GMAIL_USER');
    $email = (new Email())
        ->from($usuario)
        ->to($student->email)
        ->subject('Matrícula confirmada')
        ->text($mensagem);

    $senha = getenv('GMAIL_PASSWORD');
    $transport = Transport::fromDsn("gmail+smtp://$usuario:$senha@default");
    $mailer = new Mailer($transport);
    $mailer->send($email);
}
