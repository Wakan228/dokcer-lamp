# LAMP stack built chek prise


## Installation

- Clone this repository on your local computer
- configure .env as needed
- Run the `docker-compose up -d`.

```shell
git clone https://github.com/Wakan228/dokcer-lamp.git
cd docker-compose-lamp/
cp sample.env .env
// modify sample.env as needed
docker-compose up -d
// visit localhost
```

import actual_price.sql to database 

## How it works

the user sends a json {"url":"","mail":""} to the page api.php

the application saves your email, parses the price of the product, adds an email subscription to this product and saves everything in the database and send approve mail to email

when the user clicks on the confirmation email - the script confirms the email in the database

the server through CRON regularly checks all the products that are in the database and if there is a difference in price, it makes a selection based on emails that are subscribed to this product and confirmed and sends a message to the email with the changed price and a link to the product

You can see the diagram along the way - data/API.pdf
