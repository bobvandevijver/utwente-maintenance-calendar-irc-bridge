<?php

namespace App\Parser;

use App\DbConnector;
use App\IrcConnector;
use SimpleXMLElement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractSharedParser
{
  public function __construct(
      protected SymfonyStyle $console,
      protected DbConnector $db,
      protected IrcConnector $irc,
      protected HttpClientInterface $httpClient,
      protected PropertyAccessorInterface $accessor)
  {
  }

  /**
   * @throws ClientExceptionInterface
   * @throws DecodingExceptionInterface
   * @throws RedirectionExceptionInterface
   * @throws ServerExceptionInterface
   * @throws TransportExceptionInterface
   */
  public function __invoke(): int
  {
    $response = $this->httpClient->request('GET', $_ENV['FRONTEND_HOST'] . $this->getEndpoint());
    if ($response->getStatusCode() !== 200) {
      $this->console->error([
          'HTTP request failed for:',
          $this->getEndpoint(),
          'The error message was:',
          json_encode($response->getInfo()),
      ]);

      return Command::FAILURE;
    }

    foreach ((new SimpleXMLElement($response->getContent()))->channel->item as $apiData) {
      $this->parseObject((array)$apiData);
    }

    return Command::SUCCESS;
  }

  protected abstract function getEndpoint(): string;

  protected abstract function parseObject(array $apiData): void;
}
