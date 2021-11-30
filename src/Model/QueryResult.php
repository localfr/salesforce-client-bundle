<?php

namespace Localfr\SalesforceClientBundle\Model;

use Doctrine\Common\Collections\{ArrayCollection, Collection};

class QueryResult
{
    /**
     * @var int
     */
    private $totalSize;

    /**
     * @var bool
     */
    private $done;

    /**
     * @var string
     */
    private $nextRecordsUrl;

    /**
     * @var Collection|SObject[]
     */
    private $records;

    /**
     * @param array|null $payload
     */
    public function __construct(?array $payload = [])
    {
        $this->totalSize = $payload['totalSize'] ?? 0;
        $this->done = $payload['done'] ?? false;
        $this->nextRecordsUrl = $payload['nextRecordsUrl'] ?? null;

        $this->records = null;
        if (array_key_exists('records', $payload) && is_array($payload['records'] && !empty($payload['records']))) {
            foreach ($payload['records'] as $record) {
                $this->addRecord($record);
            }
        }
    }

    /**
     * @return int|null
     */
    public function getTotalSize(): ?int
    {
        return $this->totalSize;
    }

    /**
     * @param int|null $totalSize
     * 
     * @return self
     */
    public function setTotalSize(?int $totalSize = null): self
    {
        $this->totalSize = $totalSize;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isDone(): ?bool
    {
        return $this->done;
    }

    /**
     * @param bool|null $done
     * 
     * @return self
     */
    public function setDone(?bool $done = null): self
    {
        $this->done = $done;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNextRecordsUrl(): ?string
    {
        return $this->nextRecordsUrl;
    }

    /**
     * @param string|null $nextRecordsUrl
     * 
     * @return self
     */
    public function setNextRecordsUrl(?string $nextRecordsUrl = null): self
    {
        $this->nextRecordsUrl = $nextRecordsUrl;
        return $this;
    }

    /**
     * @return Collection|SObject[]|null
     */
    public function getRecords(): ?Collection
    {
        return $this->records;
    }

    /**
     * @param SObject $record
     *
     * @return self
     */
    public function addRecord(SObject $record): self
    {
        if (null === $this->records) {
            $this->records = new ArrayCollection();
        }

        if (!$this->records->contains($record)) {
            $this->records[] = $record;
        }
        return $this;
    }

    /**
     * @param SObject $record
     *
     * @return self
     */
    public function removeRecord(SObject $record): self
    {
        if (null === $this->records) {
            return $this;
        }

        if ($this->records->contains($record)) {
            $this->records->removeElement($record);
        }
        return $this;
    }
}