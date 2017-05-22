<?php

namespace App;

use \PhpAmqpLib\Connection\AMQPConnection;
use \PhpAmqpLib\Message\AMQPMessage;

class Queue {

    private $oConnection = null;
    private $oChannel = null;
    protected $oWorker = null;
    private $sQueueName = null;

    public function __construct($_sQueueName)
    {
        # - Запоминаем имя очереди в которой работаем
        $this->sQueueName = $_sQueueName;
        # - Устанавливаем соединение
        $this->oConnection = new AMQPConnection(
            '172.17.0.3',	#host
            5672,       	#port
            'test',    	#user
            'test'     	#password
        );
        # - Открываем канал
        $this->oChannel = $this->oConnection->channel();
        # - Инициализация очереди
        $this->oChannel->queue_declare(
            $this->sQueueName,	#имя очереди, такое же, как и у отправителя
            false,      	#пассивный
            false,      	#надёжный
            false,      	#эксклюзивный
            false       	#автоудаление
        );
    }

    public function __destruct()
    {
        # - Закрываем канал
        $this->oChannel->close();
        # - Разрываем соединение
        $this->oConnection->close();
    }

    public function addTask($_mMessage)
    {
        # - Создаем сериализованное сообщение
        $oMessage = new AMQPMessage(serialize($_mMessage));
        # - Отправляем его в очередь
        $this->oChannel->basic_publish(
            $oMessage,          #message
            '',         	    #exchange
            $this->sQueueName   #routing key
        );
    }

    public function listen($_oWorker)
    {
        # - Регистрируем воркер
        $this->oWorker = $_oWorker;
        # - Регистрируем обработчик
        $this->oChannel->basic_consume(
            $this->sQueueName,              #очередь
            '',                         	#тег получателя - Идентификатор получателя, валидный в пределах текущего канала. Просто строка
            false,                      	#не локальный - TRUE: сервер не будет отправлять сообщения соединениям, которые сам опубликовал
            true,                       	#без подтверждения - отправлять соответствующее подтверждение обработчику, как только задача будет выполнена
            false,                      	#эксклюзивная - к очереди можно получить доступ только в рамках текущего соединения
            false,                      	#не ждать - TRUE: сервер не будет отвечать методу. Клиент не должен ждать ответа
            [$this, 'execute']	            #функция обратного вызова - метод, который будет принимать сообщение
        );
        # - Слушаем событие
        while(count($this->oChannel->callbacks)) {
            $this->oChannel->wait();
        }
    }

    public function execute($_oMessage)
    {
        # - Обработка пришедшего события
        $this->oWorker->execute(unserialize($_oMessage->body));
    }
}
