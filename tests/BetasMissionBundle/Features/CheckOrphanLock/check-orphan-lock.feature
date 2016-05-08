Feature: Check orphan lock command

  Scenario: I run the check-orphan-command with remaining lock files
    When I run the command "<betasmission:check-orphan-lock>" with remaining lock files
    Then An email should be sent

  Scenario: I run the check-orphan-command with no lock file
    When I run the command "<betasmission:check-orphan-lock>" without remaining lock files
    Then No email should be sent
