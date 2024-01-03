<?php

namespace App\Parser;

use App\Model\CalendarItem;

class CalendarItemParser extends AbstractSharedParser
{

  protected function getEndpoint(): string
  {
    return $_ENV['API_RSS_ENDPOINT'];
  }

  protected function parseObject(array $apiData): void
  {
    $apiItem = new CalendarItem($apiData);
    $dbItem = $this->db->getCalendarItem($apiItem->getId());

    if (!$dbItem) {
      $this->console->text(sprintf('New item! [%s] %s',
          $apiItem->getId(), $apiItem->getTitle()));

      $this->irc->newCalendarItem($apiItem);
      $this->db->storeCalendarItem($apiItem);
    } else {
      if ($apiItem->getData() === $dbItem->getData()) {
        // No update
        return;
      }

      $this->console->text(sprintf('Updated calendar item! [%s] %s',
          $apiItem->getId(), $apiItem->getTitle()));

      $this->irc->updateCalendarItem($apiItem);
      $this->db->updateCalendarItem($apiItem);
    }
    die;
  }
}