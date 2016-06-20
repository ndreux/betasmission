Feature: Crawl the show rss link

  Scenario: I run the download subtitle command
    When I don't have any episode currently downloading
    And I load the xml file "46743.xml"
    And I run the command "show-rss:crawl" with the parameters "--since=2016-05-18-12:00:00"
    Then I should have "19" torrent added
