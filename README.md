A Symfony project created on April 10, 2016, 6:33 pm.

![alt tag](https://codeship.com/projects/129646a0-6531-0133-40a9-4a7e5d8c8004/status?branch=master)
[![Code Climate](https://codeclimate.com/repos/5735a18678bbd61e220055d2/badges/17622a81b7c65e252668/gpa.svg)](https://codeclimate.com/repos/5735a18678bbd61e220055d2/feed)

To renew credentials

    curl -X POST https://api-v2launch.trakt.tv/oauth/token \
        -d client_id=CLIENT_ID \
        -d client_secret= CLIENT_SECRET \
        -d grant_type=authorization_code \
        -d code=CODE
        
Generate a new `code` from this [page](https://trakt.tv/oauth/applications/7382)
Replace the code in the command above.

Copy the new `access_token`, `refresh_token` and code and copy/past it in the `parameters.yml`
