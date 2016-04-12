Feature: Check orphan lock command

  Scenario: I run the check-orphan-command with no lock files
    When I run the command "<betasmission:check-orphan-lock>" with remaining lock files
    Then An email should be sent
