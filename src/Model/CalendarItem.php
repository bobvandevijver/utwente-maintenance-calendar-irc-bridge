<?php

namespace App\Model;

use DateTimeImmutable;

class CalendarItem extends AbstractSharedModel
{
  // Properties to store parsed data
  private ?int $id = null;
  private ?string $link = null;

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
    return new DateTimeImmutable(static::$accessor->getValue($this->data, '[pubDate]'));
  }

  public function getCategory(): string
  {
    return static::$accessor->getValue($this->data, '[category]');
  }

  public function getDescription(): string
  {
    return static::$accessor->getValue($this->data, '[description]');
  }
}