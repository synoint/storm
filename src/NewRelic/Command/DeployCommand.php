<?php

namespace NewRelic\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeployCommand extends Command
{
    const APP_NAME = 'Syno Survey';
    const API_HOST = 'api.newrelic.com';

    private string $apiKey;

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }


    protected function configure()
    {
        $this
            ->setName('nr:deploy')
            ->setDescription('Creates deployment in NewRelic');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = 0;
        if (empty($this->apiKey)) {
            $output->writeln("New Relic's API key is not set");
            return -1;
        }

        $response = $this->performRequest($this->apiKey, $this->createPayload());
        switch ($response['status']) {
            case 200:
            case 201:
                $output->writeln('NewRelic deployment created');
                break;
            case 403:
                $output->writeln("<error>Deployment not created: Invalid API key</error>");
                $result = -1;
                break;
            default:
                $output->writeln(
                    sprintf("<error>Deployment not created: Received HTTP status %d</error>", $response['status'])
                );
                $result = -2;
                break;
        }

        return $result;
    }

    public function performRequest(string $apiKey, string $payload): array
    {
        $headers = [
            \sprintf('x-api-key: %s', $apiKey),
            'Content-type: application/x-www-form-urlencoded',
        ];

        $context = [
            'http' => [
                'method'        => 'POST',
                'header'        => \implode("\r\n", $headers),
                'content'       => $payload,
                'ignore_errors' => true,
            ],
        ];

        $content = \file_get_contents(
            \sprintf('https://%s/deployments.xml', self::API_HOST),
            false,
            \stream_context_create($context)
        );

        if (false === $content) {
            $error = \error_get_last();
            throw new \RuntimeException($error['message']);
        }

        $response = [
            'status' => null,
            'error'  => null,
        ];

        if (isset($http_response_header[0])) {
            \preg_match('/^HTTP\/1.\d (\d+)/', $http_response_header[0], $matches);

            if (isset($matches[1])) {
                $status = $matches[1];

                $response['status'] = $status;

                \preg_match('/<error>(.*?)<\/error>/', $content, $matches);

                if (isset($matches[1])) {
                    $response['error'] = $matches[1];
                }
            }
        }

        return $response;
    }

    /**
     * @return string
     */
    private function createPayload(): string
    {
        return http_build_query(
            [
                'deployment[app_name]'  => self::APP_NAME,
                'deployment[user]'      => get_current_user(),
                'deployment[revision]'  => shell_exec('git log -1 --format=%h'),
                'deployment[changelog]' => shell_exec('git log -1 --format=%s')
            ]
        );
    }
}
