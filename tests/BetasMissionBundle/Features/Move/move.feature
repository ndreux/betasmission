Feature: Organize the episodes

  Scenario Outline: I run the move command
    When I create the following file
      | <fileName> |
    And I run the command "betasmission:move" with the following parameters <from> <destination> and <default-destination>
    Then I should have the following results
      | <fileName> | <finalDestination> |

    Examples:
      | from                        | destination                     | default-destination            | fileName                                             | finalDestination                                               |
      | /tmp/betasmission/Downloads | /tmp/betasmission/Series/Active | /tmp/betasmission/Unrecognized | /tmp/betasmission/Downloads/test.mp4                 | /tmp/betasmission/Unrecognized/test.mp4                        |
      | /tmp/betasmission/Downloads | /tmp/betasmission/Series/Active | /tmp/betasmission/Unrecognized | /tmp/betasmission/Downloads/Suits.s01e01.killers.mp4 | /tmp/betasmission/Series/Active/Suits/Suits.s01e01.killers.mp4 |
      | /tmp/betasmission/Downloads | /tmp/betasmission/Series/Active | /tmp/betasmission/Unrecognized | /tmp/betasmission/Downloads/Suits.s01e02.killers.mp4 | /tmp/betasmission/Series/Active/Suits/Suits.s01e02.killers.mp4 |