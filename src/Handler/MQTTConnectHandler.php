<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\MqttServer\Handler;

use Hyperf\Context\Context;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;
use Simps\MQTT\Protocol\ProtocolInterface;
use Simps\MQTT\Protocol\Types;
use Simps\MQTT\Protocol\V3;
use Simps\MQTT\Protocol\V5;

class MQTTConnectHandler implements HandlerInterface
{
    use ResponseRewritable;

    public function handle(ServerRequestInterface $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        if ($data['protocol_name'] != 'MQTT') {
            return $response->withAttribute('closed', true);
        }

        if (! $this->isRewritable($response)) {
            return $response;
        }
        $data = [
            'type' => Types::CONNACK,
            'code' => 0,
            'session_present' => 0,
        ];
        $protocolLevel = $response->getAttribute('MqttProtocolLevel');
        if ($protocolLevel != ProtocolInterface::MQTT_PROTOCOL_LEVEL_5_0) {
            $data = V3::pack($data);
        } else {
            $data = V5::pack($data);
        }
        return $response->withBody(new SwooleStream($data));
    }
}
