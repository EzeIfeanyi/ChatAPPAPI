<?php

namespace Presentation\Controllers;

use Application\Commands\SendMessageCommand;
use Application\DTOs\MessageDTO;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Presentation\Responses\ApiResponse;

class MessageController {
    private $sendMessageCommand;
    private $logger;

    public function __construct(SendMessageCommand $sendMessageCommand, Logger $logger) {
        $this->sendMessageCommand = $sendMessageCommand;
        $this->logger = $logger;
    }

    public function send(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $data = json_decode($request->getBody()->getContents(), true);

        $messageDTO = new MessageDTO();
        $messageDTO->groupId = $data['group_id'];
        $messageDTO->userId = $data['user_id'];
        $messageDTO->content = $data['content'];

        // Log the message send attempt
        $this->logger->info('User attempting to send a message', [
            'user_id' => $messageDTO->userId,
            'group_id' => $messageDTO->groupId,
            'content' => $messageDTO->content
        ]);

        try {
            $message = $this->sendMessageCommand->execute($messageDTO);

            // Log successful message sending
            $this->logger->info('Message sent successfully', [
                'message_id' => $message->getId(),
                'group_id' => $messageDTO->groupId,
                'user_id' => $messageDTO->userId
            ]);

            $apiResponse = new ApiResponse('success', ['id' => $message->getId(), 'content' => $message->getContent()], 'Message sent successfully');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            // Log error while sending the message
            $this->logger->error('Failed to send message', [
                'user_id' => $messageDTO->userId,
                'group_id' => $messageDTO->groupId,
                'error' => $e->getMessage()
            ]);

            $apiResponse = new ApiResponse('error', null, $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function getMessages(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $groupId = $args['groupId'];
        $userId = $request->getAttribute('user')->sub;

        // Log the attempt to retrieve messages
        $this->logger->info('User attempting to retrieve messages', [
            'user_id' => $userId,
            'group_id' => $groupId
        ]);

        try {
            $messages = $this->sendMessageCommand->getMessagesByGroup($groupId, $userId);

            // Log successful retrieval of messages
            $this->logger->info('Messages retrieved successfully', [
                'user_id' => $userId,
                'group_id' => $groupId,
                'message_count' => count($messages)
            ]);

            $messagesArray = array_map(fn($message) => $message->toArray(), $messages);
            $apiResponse = new ApiResponse('success', $messagesArray, 'Messages retrieved successfully');
            $response->getBody()->write($apiResponse->toJson());
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            // Log error during message retrieval
            $this->logger->error('Failed to retrieve messages', [
                'user_id' => $userId,
                'group_id' => $groupId,
                'error' => $e->getMessage()
            ]);

            $apiResponse = new ApiResponse('error', null, $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
}
