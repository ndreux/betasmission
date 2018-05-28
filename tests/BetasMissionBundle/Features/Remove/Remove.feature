Feature: Remove the watched episodes

#  Scenario Outline: I run the remove command
#    When I create the following file
#      | <fileName> | <withLockFile> |
#    And I run the command "betasmission:remove" with the parameters "/tmp/betasmission/Series/Active"
#    Then I should have the following results
#      | <fileName> | <hasBeenSeen> | <shouldExist> |
#
#    Examples:
#      | fileName                                                                | withLockFile | hasBeenSeen | shouldExist |
#      | /tmp/betasmission/Series/Active/Suits/Suits.s01e01.killers.mp4          | false        | true        | false       |
#      | /tmp/betasmission/Series/Active/Suits/Suits.s01e02.killers.mp4          | false        | true        | false       |
#      | /tmp/betasmission/Series/Active/Suits/Suits.s01e03.killers.mp4          | true         | true        | true        |
#      | /tmp/betasmission/Series/Active/test2/KLQSDKLQSDQSD.mp4                 | false        | false       | true        |
#      | /tmp/betasmission/Series/Active/test2/My.little.pony.S01E01.KILLERS.mp4 | false        | false       | true        |
#      | /tmp/betasmission/Series/Active/Sense8/Sense8.S01E01.KILLERS.mp4        | false        | false       | true        |
#      | /tmp/betasmission/Series/Active/Sense8/Sense8.S01E08.KILLERS.mp4        | false        | false       | true        |
#      | /tmp/betasmission/Series/Active/Sense8/Sense8.S01E09.KILLERS.mp4        | false        | false       | true        |
#      | /tmp/betasmission/Series/Active/Sense8/Sense8.S01E10.KILLERS.mp4        | false        | false       | true        |

  Scenario Outline: I run the remove command with an archived show
    When I have a file named "/tmp/betasmission/Series/Active/MyLittlePonney/MyLittlePonney.S01E01.KILLERS.mp4"
    And I have a file named "/tmp/betasmission/Series/Active/MyLittlePonney/MyLittlePonney.S01E08.KILLERS.mp4"
    And I have a file named "/tmp/betasmission/Series/Active/MyLittlePonney/MyLittlePonney.S01E09.KILLERS.mp4"
    And I have a file named "/tmp/betasmission/Series/Active/MyLittlePonney/MyLittlePonney.S01E10.KILLERS.mp4"
    And I have a file named "/tmp/betasmission/Series/Active/Suits/Suits.s01e01.killers.mp4"
    And I have a file named "/tmp/betasmission/Series/Active/Suits/Suits.s01e02.killers.mp4"
    And I run the command "betasmission:remove" with the parameters "/tmp/betasmission/Series/Active"
    Then I should have the following results
      | <fileName> | <hasBeenSeen> | <shouldExist> |

    Examples:
      | fileName                                                         | hasBeenSeen | shouldExist |
      | /tmp/betasmission/Series/Active/MyLittlePonney/MyLittlePonney.S01E01.KILLERS.mp4 | false       | true        |
      | /tmp/betasmission/Series/Active/MyLittlePonney/MyLittlePonney.S01E08.KILLERS.mp4 | false       | true        |
      | /tmp/betasmission/Series/Active/MyLittlePonney/MyLittlePonney.S01E09.KILLERS.mp4 | false       | true        |
      | /tmp/betasmission/Series/Active/MyLittlePonney/MyLittlePonney.S01E10.KILLERS.mp4 | false       | true        |
      | /tmp/betasmission/Series/Active/Suits/Suits.s01e01.killers.mp4   | true        | false       |
      | /tmp/betasmission/Series/Active/Suits/Suits.s01e02.killers.mp4   | true        | false       |
      | /tmp/betasmission/Series/Active/Suits/Suits.s01e03.killers.mp4   | true        | false        |