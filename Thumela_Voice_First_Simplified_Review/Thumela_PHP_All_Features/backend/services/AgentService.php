<?php
/**
 * Integration boundary for the future AI agent.
 * Keep provider keys on the server, in environment variables, never in frontend JS.
 */
final class AgentService {
    public function plan(string $userInstruction, array $availableTools): array {
        // TODO: Call your selected LLM provider with structured tool definitions.
        // The model should return a proposed action, not a signed transaction.
        // Backend policy must independently validate every tool call.
        return [
            'status' => 'not_configured',
            'message' => 'Connect an LLM provider and MCP client before enabling agentic execution.',
            'instruction' => $userInstruction,
            'available_tools' => $availableTools
        ];
    }
}
