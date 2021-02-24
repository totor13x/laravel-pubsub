<?php

namespace LeroyMerlin\LaravelPubSub;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use LeroyMerlin\LaravelPubSub\Adapters\DevNullAdapter;
use LeroyMerlin\LaravelPubSub\Adapters\KafkaAdapter;
use LeroyMerlin\LaravelPubSub\Adapters\LocalAdapter;
use LeroyMerlin\LaravelPubSub\Adapters\GoogleCloudAdapter;
use LeroyMerlin\LaravelPubSub\Adapters\HTTPAdapter;
use LeroyMerlin\LaravelPubSub\Adapters\RedisAdapter;
use LeroyMerlin\LaravelPubSub\Contracts\AdapterInterface;

class PubSubConnectionFactory
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Factory a AdapterInterface.
     *
     * @param string $driver
     * @param array $config
     *
     * @return AdapterInterface
     */
    public function make($driver, array $config = [])
    {
        switch ($driver) {
            case '/dev/null':
                return new DevNullAdapter();
            case 'local':
                return new LocalAdapter();
            case 'redis':
                return $this->makeRedisAdapter($config);
            case 'kafka':
                return $this->makeKafkaAdapter($config);
            case 'gcloud':
                return $this->makeGoogleCloudAdapter($config);
            case 'http':
                return $this->makeHTTPAdapter($config);
        }

        throw new InvalidArgumentException(sprintf('The driver [%s] is not supported.', $driver));
    }

    /**
     * Factory a RedisAdapter.
     *
     * @param array $config
     *
     * @return RedisAdapter
     */
    protected function makeRedisAdapter(array $config)
    {
        if (!isset($config['read_write_timeout'])) {
            $config['read_write_timeout'] = 0;
        }

        $client = $this->container->makeWith('pubsub.redis.redis_client', ['config' => $config]);

        return new RedisAdapter($client);
    }

    /**
     * Factory a KafkaAdapter.
     *
     * @param array $config
     *
     * @return KafkaAdapter
     */
    protected function makeKafkaAdapter(array $config)
    {
        // create default topic
        $topicConf = $this->container->makeWith('pubsub.kafka.topic_conf');
        $topicConf->set('auto.offset.reset', 'smallest');

        // create config
        $conf = $this->container->makeWith('pubsub.kafka.conf');
        $conf->set('group.id', Arr::get($config, 'consumer_group_id', 'php-pubsub'));
        $conf->set('metadata.broker.list', $config['brokers']);
        $conf->set('enable.auto.commit', 'false');
        $conf->set('offset.store.method', 'broker');

        if (array_key_exists('security_protocol', $config)
            && $config['security_protocol'] != ''
        ) {
            switch ($config['security_protocol']) {
                case 'SASL_SSL':
                case 'SASL_PLAINTEXT':
                    $conf->set('security.protocol', Arr::get($config, 'security_protocol', 'SASL_SSL'));
                    $conf->set('sasl.username', Arr::get($config, 'sasl_username', 'sasl_username'));
                    $conf->set('sasl.password', Arr::get($config, 'sasl_password', 'sasl_password'));
                    $conf->set('sasl.mechanisms', Arr::get($config, 'sasl_mechanisms', 'PLAIN'));
                    break;

                default:
                    break;
            }
        }

        // create producer
        $producer = $this->container->makeWith('pubsub.kafka.producer', ['conf' => $conf]);
        $producer->addBrokers($config['brokers']);

        // create consumer
        $consumer = $this->container->makeWith('pubsub.kafka.consumer', ['conf' => $conf]);

        return new KafkaAdapter($producer, $consumer);
    }

    /**
     * Factory a GoogleCloudAdapter.
     *
     * @param array $config
     *
     * @return GoogleCloudAdapter
     */
    protected function makeGoogleCloudAdapter(array $config)
    {
        $clientConfig = [
            'projectId' => $config['project_id'],
            'keyFilePath' => $config['key_file'],
        ];

        if (isset($config['auth_cache'])) {
            $clientConfig['authCache'] = $this->container->make($config['auth_cache']);
        }

        $client = $this->container->makeWith('pubsub.gcloud.pub_sub_client', ['config' => $clientConfig]);

        $clientIdentifier = Arr::get($config, 'client_identifier');
        $autoCreateTopics = Arr::get($config, 'auto_create_topics', true);
        $autoCreateSubscriptions = Arr::get($config, 'auto_create_subscriptions', true);
        $backgroundBatching = Arr::get($config, 'background_batching', false);
        $backgroundDaemon = Arr::get($config, 'background_daemon', false);

        if ($backgroundDaemon) {
            putenv('IS_BATCH_DAEMON_RUNNING=true');
        }

        return new GoogleCloudAdapter(
            $client,
            $clientIdentifier,
            $autoCreateTopics,
            $autoCreateSubscriptions,
            $backgroundBatching
        );
    }

    /**
     * Factory a HTTPAdapter.
     *
     * @param array $config
     *
     * @return HTTPAdapter
     */
    protected function makeHTTPAdapter(array $config)
    {
        $client = $this->container->make('pubsub.http.client');
        $adapter = $this->make(
            $config['subscribe_connection_config']['driver'],
            $config['subscribe_connection_config']
        );
        return new HTTPAdapter($client, $config['uri'], $adapter);
    }
}
