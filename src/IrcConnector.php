<?php

namespace App;

use App\Model\CalendarItem;
use BobV\IrkerUtils\Colorize;
use BobV\IrkerUtils\Connector;
use DateTimeInterface;
use function Symfony\Component\String\u;

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

  public function remindCalendarItem(CalendarItem $item): void
  {
    $this->sendCalendarItem('reminder', $item);
  }

  private function sendCalendarItem(string $type, CalendarItem $item): void
  {
    $this->send(sprintf('%s %s: %s - %s',
        Colorize::colorize(sprintf('[%s - %s]', ucfirst($type), $item->getDepartment()), Colorize::COLOR_ORANGE),
        Colorize::colorize($this->formatDate($item), Colorize::COLOR_DARK_RED),
        $item->getTitle(),
        $item->getDescription()->truncate(100, '...'),
    ));
  }

  private function send(string $message): void
  {
    if (!$this->connector) {
      return;
    }

    $this->connector->send($_ENV['IRC_ENDPOINT'], $message);
  }

  private function formatDate(CalendarItem $item): string
  {
    $format = $item->allDay() ? 'Y-m-d' : 'Y-m-d H:i';

    $start = $item->getStart()->format($format);
    $end = $item->getEnd()->format($format);
    if ($start === $end) {
      return $start;
    }

    return "$start - $end";
  }
}
