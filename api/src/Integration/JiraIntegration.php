<?php

declare(strict_types=1);

namespace FoodDelivery\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class JiraIntegration
{
    private Client $client;
    private Logger $logger;
    private string $baseUrl;
    private string $username;
    private string $apiToken;
    private string $projectKey;

    public function __construct()
    {
        $this->baseUrl = $_ENV['JIRA_BASE_URL'] ?? '';
        $this->username = $_ENV['JIRA_USERNAME'] ?? '';
        $this->apiToken = $_ENV['JIRA_API_TOKEN'] ?? '';
        $this->projectKey = $_ENV['JIRA_PROJECT_KEY'] ?? 'FOOD';

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'auth' => [$this->username, $this->apiToken],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);

        $this->logger = new Logger('jira-integration');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/jira.log', Logger::INFO));
    }

    public function createIssue(string $summary, string $description, string $issueType = 'Task', string $priority = 'Medium'): ?string
    {
        try {
            $data = [
                'fields' => [
                    'project' => ['key' => $this->projectKey],
                    'summary' => $summary,
                    'description' => $description,
                    'issuetype' => ['name' => $issueType],
                    'priority' => ['name' => $priority]
                ]
            ];

            $response = $this->client->post('/rest/api/3/issue', [
                'json' => $data
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            $issueKey = $result['key'] ?? null;

            $this->logger->info('JIRA issue created', [
                'issue_key' => $issueKey,
                'summary' => $summary,
                'issue_type' => $issueType
            ]);

            return $issueKey;

        } catch (GuzzleException $e) {
            $this->logger->error('Failed to create JIRA issue', [
                'error' => $e->getMessage(),
                'summary' => $summary
            ]);
            return null;
        }
    }

    public function updateIssue(string $issueKey, array $fields): bool
    {
        try {
            $data = ['fields' => $fields];

            $this->client->put("/rest/api/3/issue/{$issueKey}", [
                'json' => $data
            ]);

            $this->logger->info('JIRA issue updated', [
                'issue_key' => $issueKey,
                'fields' => $fields
            ]);

            return true;

        } catch (GuzzleException $e) {
            $this->logger->error('Failed to update JIRA issue', [
                'error' => $e->getMessage(),
                'issue_key' => $issueKey
            ]);
            return false;
        }
    }

    public function addComment(string $issueKey, string $comment): bool
    {
        try {
            $data = ['body' => $comment];

            $this->client->post("/rest/api/3/issue/{$issueKey}/comment", [
                'json' => $data
            ]);

            $this->logger->info('Comment added to JIRA issue', [
                'issue_key' => $issueKey,
                'comment' => $comment
            ]);

            return true;

        } catch (GuzzleException $e) {
            $this->logger->error('Failed to add comment to JIRA issue', [
                'error' => $e->getMessage(),
                'issue_key' => $issueKey
            ]);
            return false;
        }
    }

    public function transitionIssue(string $issueKey, string $transitionName): bool
    {
        try {
            // Get available transitions
            $transitionsResponse = $this->client->get("/rest/api/3/issue/{$issueKey}/transitions");
            $transitions = json_decode($transitionsResponse->getBody()->getContents(), true);

            $transitionId = null;
            foreach ($transitions['transitions'] as $transition) {
                if ($transition['name'] === $transitionName) {
                    $transitionId = $transition['id'];
                    break;
                }
            }

            if (!$transitionId) {
                $this->logger->warning('Transition not found', [
                    'issue_key' => $issueKey,
                    'transition_name' => $transitionName
                ]);
                return false;
            }

            $data = ['transition' => ['id' => $transitionId]];

            $this->client->post("/rest/api/3/issue/{$issueKey}/transitions", [
                'json' => $data
            ]);

            $this->logger->info('JIRA issue transitioned', [
                'issue_key' => $issueKey,
                'transition_name' => $transitionName
            ]);

            return true;

        } catch (GuzzleException $e) {
            $this->logger->error('Failed to transition JIRA issue', [
                'error' => $e->getMessage(),
                'issue_key' => $issueKey
            ]);
            return false;
        }
    }

    public function getIssue(string $issueKey): ?array
    {
        try {
            $response = $this->client->get("/rest/api/3/issue/{$issueKey}");
            return json_decode($response->getBody()->getContents(), true);

        } catch (GuzzleException $e) {
            $this->logger->error('Failed to get JIRA issue', [
                'error' => $e->getMessage(),
                'issue_key' => $issueKey
            ]);
            return null;
        }
    }

    public function searchIssues(string $jql, int $maxResults = 50): array
    {
        try {
            $response = $this->client->get('/rest/api/3/search', [
                'query' => [
                    'jql' => $jql,
                    'maxResults' => $maxResults
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result['issues'] ?? [];

        } catch (GuzzleException $e) {
            $this->logger->error('Failed to search JIRA issues', [
                'error' => $e->getMessage(),
                'jql' => $jql
            ]);
            return [];
        }
    }

    public function createBugReport(string $summary, string $description, string $environment = 'Production'): ?string
    {
        return $this->createIssue($summary, $description, 'Bug', 'High');
    }

    public function createFeatureRequest(string $summary, string $description): ?string
    {
        return $this->createIssue($summary, $description, 'Story', 'Medium');
    }

    public function createTask(string $summary, string $description): ?string
    {
        return $this->createIssue($summary, $description, 'Task', 'Medium');
    }
}
