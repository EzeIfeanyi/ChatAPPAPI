<?php

namespace Application\Validators;

use Application\DTOs\GroupDTO;

class GroupValidator
{
    public static function validate(GroupDTO $groupDTO): void
    {
        if (empty($groupDTO->name)) {
            throw new \InvalidArgumentException("Group name is required.");
        }

        // Add any additional validation logic as needed, e.g., name length, uniqueness, etc.
    }
}
