<?php

namespace App\Model;

use DateTimeImmutable;
use DateTimeZone;

class CalendarItem extends AbstractSharedModel
{
  // Properties to store parsed data
  private ?int $id = null;
  private ?string $link = null;

  public function __construct(array $data, private bool $reminderSent = false)
  {
    parent::__construct($data);
  }

  public function getId(): int
  {
    if ($this->id) {
      return $this->id;
    }

    parse_str(parse_url($this->getLink(), PHP_URL_QUERY), $queryString);

    return $this->id = $queryString['ID'];
  }

  public function getTitle(): string
  {
    return static::$accessor->getValue($this->data, '[title]');
  }

  public function getLink(): string
  {
    if ($this->link) {
      return $this->link;
    }

    $link = static::$accessor->getValue($this->data, '[link]');
    if ('http' === parse_url($link, PHP_URL_SCHEME)) {
      $link = preg_replace("/^http:/i", "https:", $link);
    }

    return $this->link = $link;
  }

  public function getAuthor(): string
  {
    return static::$accessor->getValue($this->data, '[author]');
  }

  public function getPubDate(): DateTimeImmutable
  {
    $dbValue = new DateTimeImmutable(static::$accessor->getValue($this->data, '[pubDate]'));

    // Retard use of timezones because the onderhoudskalendar exports everything in GMT, but it is actually local time
    return new DateTimeImmutable($dbValue->format('Y-m-d H:i:s'), new DateTimeZone('Europe/Amsterdam'));
  }

  public function getCategory(): string
  {
    return static::$accessor->getValue($this->data, '[category]');
  }

  public function getDescription(): string
  {
    return static::$accessor->getValue($this->data, '[description]');
  }

  public function isReminderSent(): bool
  {
    return $this->reminderSent;
  }
}