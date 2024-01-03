<?php

namespace App;

use App\Model\CalendarItem;
use BobV\IrkerUtils\Colorize;
use BobV\IrkerUtils\Connector;

readonly class IrcConnector
{
  private ?Connector $connector;

  public function __construct(bool $silent)
  {
    $this->connector = $silent
        ? null
        : new Connector($_ENV['IRKER_SERVER'], $_ENV['IRKER_PORT']);
  }

  public function newCalendarItem(CalendarItem $item): void
  {
    $this->sendCalendarItem('nieuw', $item);
  }

  public function updateCalendarItem(CalendarItem $item): void
  {
    $this->sendCalendarItem('update', $item);
  }

  private function sendCalendarItem(string $type, CalendarItem $item): void
  {
    $this->send(sprintf('%s %s: %s [ %s ]',
        Colorize::colorize(sprintf('[%s]', ucfirst($type)), Colorize::COLOR_ORANGE),
        Colorize::colorize($this->formatDate($item->getPubDate()), Colorize::COLOR_DARK_RED),
        $item->getTitle(),
        Colorize::colorize($item->getLink(), Colorize::COLOR_BLUE)
    ));
  }

  private function send(string $message): void
  {
    if (!$this->connector) {
      return;
    }

    $this->connector->send($_ENV['IRC_ENDPOINT'], $message);
  }

  private function formatDate(\DateTimeInterface $dateTime): string
  {
    if ((int)$dateTime->format('H') === 0
        && (int)$dateTime->format('i') === 0
        && (int)$dateTime->format('s') === 0) {
      // All zero time, assume full day event and exclude time
      return $dateTime->format('Y-m-d');
    }

    return $dateTime->format('Y-m-d H:i');
  }
}
