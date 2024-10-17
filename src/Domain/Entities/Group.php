<?php

namespace Domain\Entities;

class Group {
    private $id;
    private $name;

    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function toArray(): array {
        return [
            'id' => $this->getId(),
            'group_id' => $this->getName(),
        ];
    } 
}
