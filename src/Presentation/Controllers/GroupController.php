<?php

namespace Presentation\Controllers;

use Application\Commands\CreateGroupCommand;
use Application\Commands\JoinGroupCommand;
use Application\DTOs\GroupDTO;
use Application\Queries\GetAllGroupsQuery;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Presentation\Responses\ApiResponse;

class GroupController {
    private $createGroupCommand;
    private $joinGroupCommand;
    private $getAllGroupsQuery;
    private $logger;

    public function __construct(
        CreateGroupCommand $createGroupCommand,
        JoinGroupCommand $joinGroupCommand,
        GetAllGroupsQuery $getAllGroupsQuery,
        Logger $logger) {
        $this->createGroupCommand = $createGroupCommand;
        $this->joinGroupCommand = $joinGroupCommand;
        $this->getAllGroupsQuery = $getAllGroupsQuery;
        $this->logger = $logger;
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $data = json_decode($request->getBody()->getContents(), true);
        $groupDTO = new GroupDTO();
        $groupDTO->name = $data['name'];

        // Get the user ID from the request attributes
        $userId = $request->getAttribute('user')->sub;

        // Log the group creation attempt
        $this->logger->info('User attempting to create a group', [
            'user_id' => $userId,
            'group_name' => $groupDTO->name
        ]);

        try {
            $group = $this->createGroupCommand->execute($groupDTO, $userId);

            // Log successful group creation
            $this->logger->info('Group created successfully', [
                'group_id' => $group->getId(),
                'group_name' => $group->getName(),
                'user_id' => $userId
            ]);

            $apiResponse = new ApiResponse('success', ['id' => $group->getId(), 'name' => $group->getName()], 'Group created successfully');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            // Log error during group creation
            $this->logger->error('Failed to create group', [
                'user_id' => $userId,
                'group_name' => $groupDTO->name,
                'error' => $e->getMessage()
            ]);

            $apiResponse = new ApiResponse('error', null, $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function join(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $userId = $args['userId'];
        $groupId = $args['groupId'];

        // Log the attempt to join a group
        $this->logger->info('User attempting to join a group', [
            'user_id' => $userId,
            'group_id' => $groupId
        ]);

        try {
            $this->joinGroupCommand->execute($userId, $groupId);

            // Log successful group join
            $this->logger->info('Joined group successfully', [
                'user_id' => $userId,
                'group_id' => $groupId
            ]);

            $apiResponse = new ApiResponse('success', null, 'Joined group successfully');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            // Log error during joining a group
            $this->logger->error('Failed to join group', [
                'user_id' => $userId,
                'group_id' => $groupId,
                'error' => $e->getMessage()
            ]);

            $apiResponse = new ApiResponse('error', null, $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function getAll(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        try {
            // Log the attempt to fetch all groups
            $this->logger->info('User attempting to fetch all groups');

            // Use the command to get all groups
            $groups = $this->getAllGroupsQuery->execute();

            // Log successful fetch
            $this->logger->info('Successfully fetched all groups', ['count' => count($groups)]);

            $apiResponse = new ApiResponse('success', $groups, 'Groups fetched successfully');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            // Log the error
            $this->logger->error('Failed to fetch groups', ['error' => $e->getMessage()]);

            $apiResponse = new ApiResponse('error', null, $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}
