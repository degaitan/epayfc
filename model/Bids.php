<?php

class Bids extends Model
{
    public function __construct(
        private int $owner,
        private int $item,
        private string $created_at,
        private float $amount
    )
    {
    }

    public function getOwner(): int
    {
        return $this->owner;
    }

    public function setOwner(int $owner): void
    {
        $this->owner = $owner;
    }

    public function getItem(): int
    {
        return $this->item;
    }

    public function setItem(int $item): void
    {
        $this->item = $item;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function is_highest_bidder(int $user_id): bool
    {
        $query = self::execute("SELECT owner FROM `bids` 
                            WHERE `item` = :item ORDER BY amount DESC
         LIMIT 1",
            ["item_id" => $this->id]);
        $highest_bid = $query->fetchColumn();
        return (int)$highest_bid === $user_id;
    }

    public function is_bidder(int $user_id): bool
    {
        $query = self::execute("SELECT COUNT(*) FROM `bids` WHERE `item` = :item_id and `owner` = :user_id",
            ["item_id" => $this->id, "user_id" => $user_id]);
        $result = $query->fetchColumn();
        return $result > 0;
    }

    public function get_user_bid(int $user_id): ?float
    {
        $query = self::execute(
            "SELECT MAX(amount)
         FROM bids
         WHERE item = :item_id
           AND owner = :user_id",
            [
                "item_id" => $this->id,
                "user_id" => $user_id
            ]
        );

        $amount = $query->fetchColumn();
        return $amount !== false ? (float)$amount : null;
    }
}