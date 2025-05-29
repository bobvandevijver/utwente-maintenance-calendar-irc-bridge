<?php

namespace App\Model;

use DateTimeImmutable;
use DateTimeZone;
use Symfony\Component\String\UnicodeString;
use function Symfony\Component\String\u;

class CalendarItem extends AbstractSharedModel
{
  public function __construct(array $data, private readonly bool $reminderSent = false)
  {
    parent::__construct($data);
  }

  public function getId(): int
  {
    return static::$accessor->getValue($this->data, '[id]');
  }

  public function getTitle(): string
  {
    return static::$accessor->getValue($this->data, '[title]');
  }

  public function getDepartment(): string
  {
    return static::$accessor->getValue($this->data, '[department]');
  }

  public function getStart(): DateTimeImmutable
  {
    return new DateTimeImmutable(static::$accessor->getValue($this->data, '[start]'));
  }

  public function getEnd(): DateTimeImmutable
  {
    return new DateTimeImmutable(static::$accessor->getValue($this->data, '[end]'));
  }

  public function allDay(): bool
  {
    return static::$accessor->getValue($this->data, '[allDay]');
  }

  public function getDescription(): UnicodeString
  {
    $description = static::$accessor->getValue($this->data, '[description]');
    $description = str_replace('<', ' <', $description);
    $description = strip_tags($description);

    return u($description)->collapseWhitespace();
  }

  public function isReminderSent(): bool
  {
    return $this->reminderSent;
  }
}