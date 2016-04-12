Feature: Download the subtitles for all the shows

  Scenario Outline: I run the download subtitle command
    When I create the following file
      | name | <fileName> | <withSubtitle> |
    And I run the command "<betasmission:subtitle>"
    Then I should have the following results
      | name               | <fileName>           |
      | shouldHaveSubtitle | <shouldHaveSubtitle> |

    Examples:
      | fileName | withSubtitle | betasmission:subtitle | shouldHaveSubtitle |


  Scenario: I run the check-orphan-command with no lock files
    When I run the command "<betasmission:check-orphan-lock>" with remaining lock files
    Then An email should be sent