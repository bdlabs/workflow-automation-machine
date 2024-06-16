<?php

namespace DecisionMachine;

class ApiNode extends Node
{
    /**
     * @param string $Id
     * @param array $config
     * @param ApiNodeInterface|null $callBackNode
     */
    public function __construct(string $Id, private array $config, private readonly ?ApiNodeInterface $callBackNode = null)
    {
        parent::__construct($Id);
    }

    public function input(NodeSignal $signal)
    {
        if($this->callBackNode) {
            $this->config = $this->callBackNode->prepareConfig($this->config, $signal);
        }

        $response = $this->sendRequest($this->config);
        $newSignal = new NodeSignal(json_decode($response, true), new SignalType(''));

        if($this->callBackNode) {
            parent::input($this->callBackNode->end($newSignal));

            return;
        }

        parent::input($newSignal);

    }

    protected function sendRequest(array $config)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $config['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => strtoupper($config['method']),
            CURLOPT_POSTFIELDS => $config['body'],
            CURLOPT_HTTPHEADER => $config['headers'],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}