<?php

namespace App;

use App\Model\AbstractSharedModel;
use App\Model\CalendarItem;
use DateTimeInterface;
use PDO;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

readonly class DbConnector
{
  private PDO $db;

  public function __construct(string $dbDir, protected PropertyAccessorInterface $accessor)
  {
    $this->db = new PDO(sprintf('sqlite:%s%s%s', $dbDir, DIRECTORY_SEPARATOR, $_ENV['DB_FILE']));

    // Create tables if not yet created
    $this->db->query(<<<SQL
CREATE TABLE IF NOT EXISTS calendar_items (
      id INTEGER PRIMARY KEY NOT NULL,
      title TEXT,
      category TEXT,
      pub_date TEXT,
      data TEXT
) WITHOUT ROWID
SQL
    );
  }

  public function getCalendarItem(int $id): ?CalendarItem
  {
    $stmt = $this->db->prepare('SELECT data FROM calendar_items WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if (!$data = $stmt->fetch(PDO::FETCH_ASSOC)) {
      return NULL;
    }

    return new CalendarItem(json_decode($data['data'], true));
  }

  public function storeCalendarItem(CalendarItem $calendarItem): void
  {
    $stmt = $this->db->prepare(<<<SQL
INSERT INTO calendar_items (id,
                        title,
                        category,
                        pub_date,
                        data)
VALUES (:id,
        :title,
        :category,
        :pub_date,
        :data)
SQL
    );

    if (!$stmt->execute($this->calendarItemToParameters($calendarItem))) {
      throw new RuntimeException(json_encode($stmt->errorInfo()));
    }
  }

  public function updateCalendarItem(CalendarItem $component): void {
    $stmt = $this->db->prepare(<<<SQL
UPDATE calendar_items
SET title = :title,
    category = :category,
    pub_date = :pub_date,
    data = :data
WHERE id = :id
SQL
    );
    $stmt->execute($this->calendarItemToParameters($component));
  }

  private function calendarItemToParameters(CalendarItem $calendarItem): array
  {
    return $this->sharedModelToParameters($calendarItem, [
        'id',
        'title',
        'category',
        'pub_date'
    ]);
  }

  private function sharedModelToParameters(AbstractSharedModel $sharedModel, array $fields): array
  {
    $result = [];

    foreach ($fields as $field) {
      $result[":$field"] = $this->accessor->getValue($sharedModel, $field);

      if ($result[":$field"] instanceof DateTimeInterface) {
        $result[":$field"] = $result[":$field"]->format('c');
      }
    }

    $result[':data'] = json_encode($sharedModel->getData());

    return $result;
  }
}
