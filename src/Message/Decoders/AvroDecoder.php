<?php

namespace Junges\Kafka\Message\Decoders;

use FlixTech\AvroSerializer\Objects\RecordSerializer;
use Junges\Kafka\Contracts\AvroMessageDecoder;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Junges\Kafka\Contracts\AvroSchemaRegistry;
use Junges\Kafka\Message\ConsumedMessage;

class AvroDecoder implements AvroMessageDecoder
{
    public function __construct(
        private AvroSchemaRegistry $registry,
        private RecordSerializer   $recordSerializer
    )
    {
    }

    public function getRegistry(): AvroSchemaRegistry
    {
        return $this->registry;
    }

    public function decode(KafkaConsumerMessage $message): KafkaConsumerMessage
    {
        return new ConsumedMessage(
            topicName: $message->getTopicName(),
            partition: $message->getPartition(),
            headers: $message->getHeaders(),
            body: $this->decodeBody($message->getBody()),
            key: $this->decodeKey($message->getKey()),
            offset: $message->getOffset(),
            timestamp: $message->getTimestamp()
        );
    }

    private function decodeBody(KafkaConsumerMessage $message)
    {
        $body = $message->getBody();
        $topicName = $message->getTopicName();

        if (null === $body) {
            return null;
        }

        if (false === $this->registry->hasBodySchemaForTopic($topicName)) {
            return $body;
        }

        $avroSchema = $this->registry->getBodySchemaForTopic($topicName);
        $schemaDefinition = $avroSchema->getDefinition();

        return $this->recordSerializer->decodeMessage($body, $schemaDefinition);
    }

    private function decodeKey(KafkaConsumerMessage $message)
    {
        $key = $message->getKey();
        $topicName = $message->getTopicName();

        if (null === $key) {
            return null;
        }

        if (false === $this->registry->hasKeySchemaForTopic($topicName)) {
            return $key;
        }

        $avroSchema = $this->registry->getKeySchemaForTopic($topicName);
        $schemaDefinition = $avroSchema->getDefinition();

        return $this->recordSerializer->decodeMessage($key, $schemaDefinition);
    }
}