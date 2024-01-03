<?php

namespace App\Model;

use DateTimeImmutable;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class AbstractSharedModel
{
  protected static PropertyAccessor $accessor;

  public function __construct(protected readonly array $data)
  {
    static::$accessor ??= new PropertyAccessor();
  }

  public function getData(): array
  {
    return $this->data;
  }
}
