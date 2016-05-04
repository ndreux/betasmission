Feature: Download the subtitles for all the shows

  Scenario Outline: I run the download subtitle command
    When I create the following file
      | <fileName> | <withSubtitle> |
    And I run the command "betasmission:subtitle" with the parameters "/tmp/betasmission"
    Then I should have the following results
      | <fileName> | <shouldHaveSubtitle> |

    Examples:
      | fileName                                         | withSubtitle | shouldHaveSubtitle |
      | /tmp/betasmission/Test/test.s01e01.killers.mp4   | false        | false              |
      | /tmp/betasmission/Suits/Suits.s01e01.killers.mp4 | false        | true               |
      | /tmp/betasmission/Suits/Suits.s01e02.killers.mp4 | true         | true               |