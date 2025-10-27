<?php

namespace App\Parser;

use App\Model\CalendarItem;
use DateTimeImmutable;

class CalendarItemParser extends AbstractSharedParser
{

  protected function getEndpoint(): string
  {
    return $_ENV['API_ENDPOINT'];
  }

  protected function parseObject(array $apiData): void
  {
    $now = new DateTimeImmutable();

    $apiItem = new CalendarItem($apiData);
    $dbItem = $this->db->getCalendarItem($apiItem->getId());

    if (!$dbItem) {
      $this->console->text(sprintf('New item! [%s] %s',
          $apiItem->getId(), $apiItem->getTitle()));

      $this->irc->newCalendarItem($apiItem);
      $this->db->storeCalendarItem($apiItem);

      if ($apiItem->getStart() > $now) {
        // Item was already started, mark reminder as already send
        $this->db->markCalendarItemReminderSent($apiItem, true);
      }
    } else {
      if ($apiItem->getData() === $dbItem->getData()) {
        // No update, but check if reminder is needed
        if ($dbItem->isReminderSent()) {
          // Reminder already sent
          return;
        }

        if ($apiItem->getStart() > $now) {
          // Reminder not yet needed
          return;
        }

        if ($apiItem->getEnd() <= $now) {
          // Too late for a reminder
          $this->console->text(sprintf('Calendar item reminder missed?! [%s] %s',
              $apiItem->getId(), $apiItem->getTitle()));
          $this->db->markCalendarItemReminderSent($apiItem, true);

          return;
        }

        // Create reminder
        $this->console->text(sprintf('Calendar item reminder! [%s] %s',
            $apiItem->getId(), $apiItem->getTitle()));
        $this->irc->remindCalendarItem($apiItem);
        $this->db->markCalendarItemReminderSent($apiItem, true);

        return;
      }

      $this->console->text(sprintf('Updated calendar item! [%s] %s',
          $apiItem->getId(), $apiItem->getTitle()));

      if ($apiItem->getStart() > $now) {
        // Item was already started, mark reminder as already send
        $this->db->markCalendarItemReminderSent($apiItem, true);
      }

      $this->irc->updateCalendarItem($apiItem);
      $this->db->updateCalendarItem($apiItem);
    }

    // If the item/update is for today, mark reminder as sent
    $this->db->markCalendarItemReminderSent(
        $apiItem,
        $apiItem->getStart()->format('Ymd') === $now->format('Ymd')
    );
  }
}