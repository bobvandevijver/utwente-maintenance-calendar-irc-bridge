<?php

namespace App\Parser;

use App\DbConnector;
use App\IrcConnector;
use DateTimeImmutable;
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
    $now = new DateTimeImmutable();
    $response = $this->httpClient->request('POST', $_ENV['FRONTEND_HOST'] . $this->getEndpoint(), [
        'body' => [
            'start' => $now->modify('-1 week')->format('c'),
            'end' => $now->modify('+1 week')->format('c'),
        ]
    ]);
    if ($response->getStatusCode() !== 200) {
      $this->console->error([
          'HTTP request failed for:',
          $this->getEndpoint(),
          'The error message was:',
          json_encode($response->getInfo()),
      ]);

      return Command::FAILURE;
    }

    $items = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    foreach ($items as $item) {
      $this->parseObject($item);
    }

    return Command::SUCCESS;
  }

  protected abstract function getEndpoint(): string;

  protected abstract function parseObject(array $apiData): void;
}
