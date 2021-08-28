<?php

namespace Junges\Kafka\Commit;

use Junges\Kafka\Commit\Contracts\Committer;
use RdKafka\KafkaConsumer;

class KafkaCommitter implements Committer
{
    private KafkaConsumer $consumer;

    public function __construct(KafkaConsumer $consumer)
    {
        $this->consumer = $consumer;
    }

    public function commitMessage(): void
    {
        $this->consumer->commit();
    }

    public function commitDlq(): void
    {
        $this->consumer->commit();
    }
}